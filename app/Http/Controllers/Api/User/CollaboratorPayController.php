<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Models\Collaborator;
use App\Models\CollaboratorsConfig;
use App\Models\MsgCode;
use App\Models\PayCollaborator;
use App\Services\BalanceCustomerService;
use App\Services\SettlementCollaboratorService;
use Illuminate\Http\Request;

/**
 * @group  User/Yêu cầu thanh toán
 */
class CollaboratorPayController extends Controller
{
    /**
     * Danh sách yêu cầu thanh toán
     * status":  //0 chờ xử lý - 1 huy yeu cau - 2 đã thanh toán
     *  from":  // //0 yêu cầu từ CTV - 1 Do user lên danh sách
     */
    public function all_request_payment(Request $request)
    {

        $res = PayCollaborator::where('store_id', $request->store->id)
            ->where('status', 0)
            ->orderBy('id', 'desc')->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $res
        ], 200);
    }

    /**
     * Lịch sử yêu cầu thanh toán
     * 
     * 
     * 
     * status 0 chờ xử lý - 1 hoàn lại - 2 đã thanh toán
     * 
     * @urlParam  store_code required Store code
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     */
    public function history_request_payment(Request $request)
    {
        $filter_by = request('filter_by');
        $filter_by_value = request('filter_by_value');
        $sortColumn = request('sort_by');

        $res = PayCollaborator::where('store_id', $request->store->id)
            ->when($filter_by != null, function ($query) use ($filter_by, $filter_by_value) {
                $query->where($filter_by, $filter_by_value);
            })->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $res
        ], 200);
    }

    /**
     * Thay đổi trạng thái chờ xỷ lý sang đã thanh toán hoặc hoàn
     * @urlParam  store_code required Store code
     * @bodyParam  status 0 chờ xử lý - 1 hoàn lại - 2 đã thanh toán
     * @bodyParam list_id id xử lý
     */

    public function change_status(Request $request)
    {


        $list_id =  $request->list_id;


        if (!is_array($request->list_id)) {
            $list_id =  json_decode($request->list_id);
        }


        if (!is_array($list_id) || count($list_id) == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST[0],
                'msg' => MsgCode::INVALID_LIST[1],
            ], 400);
        }

        if ($request->status == 2) {
            $res = PayCollaborator::where('store_id', $request->store->id)
                ->where('status', 0)
                ->whereIn('id', $list_id)
                ->orderBy('id', 'desc')->get();
            foreach ($res as $pay) {
                $collaborator = Collaborator::where('store_id', $request->store->id)
                    ->where('id', $pay->collaborator_id)
                    ->first();
                $pay->update([
                    'status' => 2
                ]);
                BalanceCustomerService::change_balance_collaborator(
                    $request->store->id,
                    $collaborator->customer_id,
                    BalanceCustomerService::PAYMENT_REQUEST,
                    -$pay->money,
                    $pay->id,
                    -$pay->money
                );
            }
        }

        if ($request->status == 1) {

            $res = PayCollaborator::where('store_id', $request->store->id)
                ->where('status', 0)
                ->whereIn('id', $list_id)
                ->orderBy('id', 'desc')->get();
            foreach ($res as $pay) {

                $collaborator = Collaborator::where('store_id', $request->store->id)
                    ->where('id', $pay->collaborator_id)
                    ->first();

                PushNotificationCustomerJob::dispatch(
                    $request->store->id,
                    $collaborator->customer_id,
                    "Chú ý",
                    "Yêu cầu thanh toán đã bị hủy",
                    TypeFCM::CANCEL_CTV_PAY,
                    null
                );

                $pay->update([
                    'status' => 1
                ]);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],

        ], 200);
    }


    /**
     * Quyết toán toàn bộ CTV
     * @urlParam  store_code required Store code
     */

    public function settlement(Request $request)
    {
        SettlementCollaboratorService::settlement($request->store->id);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
