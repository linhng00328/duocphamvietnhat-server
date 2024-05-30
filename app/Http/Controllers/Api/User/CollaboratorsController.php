<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\AgencyUtils;
use App\Helper\Helper;
use App\Helper\StatusDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Models\Agency;
use App\Models\AgencyRegisterRequest;
use App\Models\ChangeBalanceCollaborator;
use App\Models\Collaborator;
use App\Models\CollaboratorBonusStep;
use App\Models\CollaboratorRegisterRequest;
use App\Models\CollaboratorsConfig;
use App\Models\Customer;
use App\Models\MsgCode;
use App\Services\BalanceCustomerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;


/**
 * @group User/Quản lý Cộng tác viên
 * 
 * Cộng tác viên
 */


class CollaboratorsController extends Controller
{

    /**
     * Danh sách CTV
     * @urlParam  store_code required Store code
     * 
     * response có thêm số lượng order,và tổng total final
     * 
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: covid 19
     * @queryParam  sort_by Sắp xếp theo VD: time
     * @queryParam  descending Giảm dần không VD: false 

     */
    public function getAllCollaborator(Request $request, $id)
    {

        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $posts = Collaborator::select('collaborators.*')->join('customers as cuss', 'cuss.id', '=', 'collaborators.customer_id')
            ->when(request('sort_by') != null, function ($query) {
                $query->orderBy("collaborators." . request('sort_by'), filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->where(
                'collaborators.store_id',
                $request->store->id
            )
            ->where(
                'cuss.is_collaborator',
                true
            )
            ->when(request('search') == null, function ($query) {
                $query->orderBy('collaborators.created_at', 'desc');
            })
            ->search(request('search'))
            ->paginate(request('limit') == null ? 20 : request('limit'));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $posts,
        ], 200);
    }


    /**
     * Báo cáo Danh sách CTV theo top
     * @urlParam  store_code required Store code
     * 
     * response có thêm số lượng order,và tổng total final
     * 
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: covid 19
     * @queryParam  sort_by Sắp xếp theo VD: time
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  date_from
     * @queryParam  date_to
     */
    public function getAllCollaboratorTop(Request $request, $id)
    {

        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $posts = Collaborator::sortByRelevance(true)
            ->when(Collaborator::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->whereHas('customer', function ($query) {
                $query->where('is_collaborator', true);
            })
            ->where('collaborators.store_id', $request->store->id)
            ->leftJoin('orders', 'collaborators.customer_id', '=', 'orders.collaborator_by_customer_id')
            ->selectRaw('collaborators.*, count(orders.id) as orders_count, sum(orders.total_final) as sum_total_final,sum(orders.total_before_discount) as sum_total_after_discount, sum(orders.share_collaborator) as sum_share_collaborator')
            ->groupBy('collaborators.id')
            // ->where('orders.created_at', '>=',  $dateFrom)
            // ->where('orders.created_at', '<', $dateTo)
            ->where('orders.completed_at', '>=',  $dateFrom)
            ->where('orders.completed_at', '<', $dateTo)
            ->where(
                'orders.store_id',
                $request->store->id
            )
            ->where('orders.order_status', StatusDefineCode::COMPLETED)
            ->where('orders.payment_status', StatusDefineCode::PAID)
            ->when(request('sort_by') == "orders_count", function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when(request('sort_by') == "sum_total_final", function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->orderBy('sum_total_final', 'desc')

            ->search(request('search'))

            ->paginate(20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $posts,
        ], 200);
    }

    /**
     * Lấy thông số chia sẻ cho cộng tác viên
     * @urlParam  store_code required Store code cần lấy
     * @bodyParam allow_payment_request cho phép gửi yêu cầu thanh toán
     * @bodyParam type_rose 0 doanh số, 1 hoa hồng
     * @bodyParam payment_1_of_month Quyết toán ngày 1 hàng tháng ko
     * @bodyParam payment_16_of_month Quyết toán ngày 15 hàng tháng ko
     * @bodyParam payment_limit Số tiền hoa hồng được quyết toán
     * 
     */
    public function getConfig(Request $request)
    {
        $columns = Schema::getColumnListing('collaborators_configs');

        $configExists = CollaboratorsConfig::where(
            'store_id',
            $request->store->id
        )->first();

        if ($configExists == null) {
            $configExists = CollaboratorsConfig::create([
                'store_id' => $request->store->id,
                'allow_payment_request' => false,
                'type_rose' => 0,
                'payment_1_of_month' => false,
                'payment_16_of_month' => false,
                'payment_limit' => 0,
            ]);
        }

        $configResponse = new CollaboratorsConfig();

        foreach ($columns as $column) {

            if ($configExists != null && array_key_exists($column, $configExists->toArray())) {
                $configResponse->$column =  $configExists->$column;
            } else {
                $configResponse->$column = null;
            }
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $configResponse,
        ], 200);
    }


    /**
     * Cập nhật 1 số thuộc tính cho collaborators
     * @urlParam  store_code required Store code
     * @urlParam  collaborator_id required id trong danh sach cong tac vien
     * @bodyParam status int Trạng thái cộng tác viên 1 (Hoạt động)  0 đã hủy
     */
    public function update_for_collaborator(Request $request)
    {

        $id = $request->route()->parameter('collaborator_id');

        $collaboratorExists = Collaborator::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        if ($collaboratorExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COLLABORATOR_EXISTS[0],
                'msg' => MsgCode::NO_COLLABORATOR_EXISTS[1],
            ], 400);
        }

        $customerExist = Customer::where('id', $collaboratorExists->customer_id)->first();

        if ($request->status == 1) {

            $a = Agency::where('customer_id',  $collaboratorExists->customer_id)->first();

            if ($a != null && $a->status == 1) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Khách hàng đã là đại lý không thể là CTV, hãy tắt quyền đại lý trước",
                ], 400);
            }

            $rq = AgencyRegisterRequest::where('store_id', $request->store->id)
                ->where('customer_id', $collaboratorExists->customer_id)
                ->orderBy('id', 'desc')
                ->first();

            if ($rq != null && ($rq->status == 0 || $rq->status == 3)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Khách hàng đang gửi yêu cầu làm đại lý không thể là CTV, hãy hủy yêu cầu đại lý",
                ], 400);
            }
        }

        if ($collaboratorExists->status !=  $request->status && $request->status  == 1) {

            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $collaboratorExists->customer_id,
                "Chúc mừng",
                "Hồ sơ cộng tác viên của bạn đã được duyệt",
                TypeFCM::GET_CTV,
                null
            );
        }

        if ($request->status  == 1) {
            $customerExist->update(
                [
                    'is_collaborator' => true,
                    'is_agency' => false,
                ]
            );
        }

        if ($collaboratorExists->status !=  $request->status && $request->status  == 0) {
            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $collaboratorExists->customer_id,
                "Chú ý",
                "Bạn đã bị hủy tư cách CTV",
                TypeFCM::CANCEL_CTV,
                null
            );
        }

        $collaboratorExists->update(Helper::sahaRemoveItemArrayIfNullValue(
            [
                "status" => $request->status
            ]
        ));





        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  Collaborator::where('store_id',  $request->store->id)->where('id', $id)->first()
        ], 200);
    }

    /**
     * Cập nhật cấu hình cài đặt cho phần CTV
     * @urlParam  store_code required Store code
     * @bodyParam type_rose int 0 (Theo doanh số)  1 Theo hoa hồng giới thiệu
     * @bodyParam allow_payment_request cho phép gửi yêu cầu thanh toán
     * @bodyParam payment_1_of_month Quyết toán ngày 1 hàng tháng ko
     * @bodyParam payment_16_of_month Quyết toán ngày 15 hàng tháng ko
     * @bodyParam payment_limit Số tiền hoa hồng được quyết toán
     * @bodyParam percent_collaborator_t1 double Phăm trăm chia sẻ cho công tác viên T1
     * @bodyParam allow_rose_referral_customer cho phép cộng tiền hoa hồng từ khách hàng của CTV giới thiệu
     * 
     * 
     */
    public function update(Request $request)
    {

        $callaboratorExists = CollaboratorsConfig::where(
            'store_id',
            $request->store->id
        )->first();

        if ($request->type_rose > 100 || $request->type_rose < 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PERCENT[0],
                'msg' => MsgCode::INVALID_PERCENT[1],
            ], 400);
        }

        if ($callaboratorExists == null) {
            $callaboratorExists = CollaboratorsConfig::create([
                'store_id' => $request->store->id,
                'type_rose' => $request->type_rose ?? 0,
                'allow_payment_request' => filter_var($request->allow_payment_request ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_1_of_month' => filter_var($request->payment_1_of_month ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_16_of_month' => filter_var($request->payment_16_of_month ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_limit' => $request->payment_limit ?? 0,
                'percent_collaborator_t1' => $request->percent_collaborator_t1 ?? 0,
                'allow_rose_referral_customer'  => filter_var($request->allow_rose_referral_customer ?? false, FILTER_VALIDATE_BOOLEAN),
                'bonus_type_for_ctv_t2' => $request->bonus_type_for_ctv_t2 ?? 0,
            ]);
        } else {
            $callaboratorExists->update([
                'type_rose' => $request->type_rose ?? 0,
                'allow_payment_request' => filter_var($request->allow_payment_request ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_1_of_month' => filter_var($request->payment_1_of_month ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_16_of_month' => filter_var($request->payment_16_of_month ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_limit' => $request->payment_limit ?? 0,
                'percent_collaborator_t1' => $request->percent_collaborator_t1 ?? 0,
                'allow_rose_referral_customer'  => filter_var($request->allow_rose_referral_customer ?? false, FILTER_VALIDATE_BOOLEAN),
                'bonus_type_for_ctv_t2' => $request->bonus_type_for_ctv_t2 ?? $callaboratorExists->bonus_type_for_ctv_t2 ?? 0,
            ]);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  CollaboratorsConfig::where('store_id',  $request->store->id)->first()
        ], 200);
    }


    /**
     * Thêm 1 bậc tiền thưởng 1 tháng
     * @urlParam  store_code required Store code
     * @bodyParam limit double required Giới hạn được thưởng
     * @bodyParam bonus double required Số tiền thưởng
     */
    public function create(Request $request)
    {

        $callaboratorExists = CollaboratorBonusStep::where(
            'store_id',
            $request->store->id
        )->where(
            'limit',
            $request->limit
        )->first();

        if ($callaboratorExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::BONUS_EXISTS[0],
                'msg' => MsgCode::BONUS_EXISTS[1],
            ], 400);
        }

        if ($request->bonus < 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_VALUE[0],
                'msg' => MsgCode::INVALID_VALUE[1],
            ], 400);
        }

        $callaboratorExists = CollaboratorBonusStep::create([
            'store_id' => $request->store->id,
            'limit' => $request->limit,
            'bonus' => $request->bonus,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Danh sách bậc thang thưởng
     * @urlParam  store_code required Store code
     */
    public function getStepBonusAll(Request $request)
    {

        $steps = CollaboratorBonusStep::where('store_id', $request->store->id)->orderBy('bonus', 'asc')->get();;

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $steps,
        ], 200);
    }

    /**
     * xóa một bac thang
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  step_id required ID Step cần xóa thông tin.
     */
    public function deleteOneStep(Request $request, $id)
    {

        $id = $request->route()->parameter('step_id');
        $checkStepExists = CollaboratorBonusStep::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkStepExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST[0],
                'msg' => MsgCode::DOES_NOT_EXIST[1],
            ], 404);
        } else {
            $idDeleted = $checkStepExists->id;
            $checkStepExists->delete();
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => ['idDeleted' => $idDeleted],
            ], 200);
        }
    }


    /**
     * update một Step
     * @urlParam  store_code required Store code cần update
     * @urlParam  step_id required Step_id cần update
     * @bodyParam limit double required Giới hạn đc thưởng
     * @bodyParam bonus double required Số tiền thưởng
     */
    public function updateOneStep(Request $request)
    {

        if ($request->bonus < 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_VALUE[0],
                'msg' => MsgCode::INVALID_VALUE[1],
            ], 400);
        }

        $id = $request->route()->parameter('step_id');
        $checkStepExists = CollaboratorBonusStep::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkStepExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST[0],
                'msg' => MsgCode::DOES_NOT_EXIST[1],
            ], 404);
        } else {
            $checkStepExists->update([
                'limit' => $request->limit,
                'bonus' => $request->bonus,
            ]);

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => CollaboratorBonusStep::where('id', $id)->first(),
            ], 200);
        }
    }

    /**
     * Cộng trừ tiền cho cộng tác viên
     * 
     * @bodyParam is_sub bool cộng hay trừ
     * @bodyParam money số tiền ()
     * @bodyParam reason lý do
     * 
     * 
     */
    public function addSubBalanceCTV(Request $request)
    {
        $is_sub = filter_var($request->is_sub, FILTER_VALIDATE_BOOLEAN);

        if ($request->money <= 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => 'Số tiền không hợp lệ',
            ], 400);
        }

        $id = $request->route()->parameter('collaborator_id');

        $callaboratorExists = Collaborator::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        if ($callaboratorExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COLLABORATOR_EXISTS[0],
                'msg' => MsgCode::NO_COLLABORATOR_EXISTS[1],
            ], 400);
        }

        BalanceCustomerService::change_balance_collaborator(
            $request->store->id,
            $callaboratorExists->customer->id,
            $is_sub == true ?  BalanceCustomerService::SUB_BALANCE_CTV  :  BalanceCustomerService::ADD_BALANCE_CTV,
            $is_sub == true ? -$request->money : $request->money,
            Helper::getRandomOrderString(),
            null,
            $request->reason

        );

        $callaboratorExists = Collaborator::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $callaboratorExists,
        ], 200);
    }


    /**
     * Lịch sử thay đổi số dư cộng tác viên
     * 
     * 
     */
    public function historyChangeBalance(Request $request)
    {

        $id = $request->route()->parameter('collaborator_id');

        $callaboratorExists = Collaborator::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        $histories = ChangeBalanceCollaborator::where('store_id', $request->store->id)
            ->where('collaborator_id',  $id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $histories
        ], 200);
    }

    /**
     * Danh sách CTV yêu cầu làm ctv
     * @urlParam  store_code required Store code
     * 
     * 
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: covid 19
     * @queryParam  status trạng thái 0 chờ xử lý, 1 đã hủy, 2 đồng ý, 3 yêu cầu lại

     */
    public function getAllCollaboratorRegisterRequest(Request $request, $id)
    {

        $posts = CollaboratorRegisterRequest::where(
            'store_id',
            $request->store->id
        )
            ->when(request('search') == null, function ($query) {
                $query->orderBy('id', 'desc');
            })
            ->when(request('status') != null, function ($query) {
                $query->where('status', request('status'));
            })
            ->search(request('search'))
            ->paginate(request('limit') == null ? 20 : request('limit'));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $posts,
        ], 200);
    }

    /**
     * Xử lý yêu cầu làm ctv
     * @urlParam  store_code required Store code
     * 
     * 
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: covid 19
     * @queryParam  status trạng thái 0 chờ xử lý, 1 đã hủy, 2 đồng ý, 3 yêu cầu lại
     * @queryParam  note Ghi chú
     */
    public function handleCollaboratorRegisterRequest(Request $request, $id)
    {

        $collaborator_register_request = $request->collaborator_register_request_id;

        $rq = CollaboratorRegisterRequest::where(
            'store_id',
            $request->store->id
        )
            ->where('id', $collaborator_register_request)->first();

        if ($rq ==  null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => MsgCode::NO_REQUEST_EXISTS[1],
            ], 400);
        }

        $customer = Customer::where(
            'store_id',
            $request->store->id
        )->where('id', $rq->customer_id)->first();

        $collaborator = Collaborator::where(
            'store_id',
            $request->store->id
        )->where('customer_id', $rq->customer_id)->first();

        if ($request->status == 2) {
            $rq->update([
                'status' => 2
            ]);
            $customer->update([
                'is_collaborator' =>  true,
                'official' => true,
            ]);
            $collaborator->update([
                'status' =>  1,
            ]);

            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $customer->id,
                "Ghi chú",
                "Yêu cầu làm CTV đã được duyệt",
                TypeFCM::GET_CTV,
                null
            );
        }

        if ($request->status == 1) {
            $rq->update([
                'status' => 1
            ]);
            $customer->update([
                'is_collaborator' =>  false,
            ]);
            $collaborator->update([
                'status' =>  0,
            ]);

            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $customer->id,
                "Ghi chú",
                "Yêu cầu làm CTV đã bị hủy",
                TypeFCM::CANCEL_CTV,
                null
            );
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    public function updateBankInfoCollaborator(Request $request) {

        $id = $request->route()->parameter('collaborator_id');

        $collaboratorExists = Collaborator::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        if ($collaboratorExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COLLABORATOR_EXISTS[0],
                'msg' => MsgCode::NO_COLLABORATOR_EXISTS[1],
            ], 400);
        }

       $collaboratorExists->update([
            "bank" => $request->input("bankName"),
            "account_number" => $request->input("bankNumber"),
            "account_name" => $request->input("bankOwner"),
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  Collaborator::where('store_id',  $request->store->id)->where('id', $id)->first()
        ], 200);
    }
}
