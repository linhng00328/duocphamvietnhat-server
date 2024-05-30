<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\PointCustomerUtils;
use App\Helper\StatusDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Models\Agency;
use App\Models\AgencyBonusStep;
use App\Models\AgencyConfig;
use App\Models\AgencyImportStep;
use App\Models\AgencyRegisterRequest;
use App\Models\AgencyType;
use App\Models\ChangeBalanceAgency;
use App\Models\Collaborator;
use App\Models\CollaboratorRegisterRequest;
use App\Models\Customer;
use App\Models\HistoryChangeLevelAgency;
use App\Models\MsgCode;
use App\Models\Product;
use App\Services\BalanceCustomerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


/**
 * @group User/Quản lý Đại lý
 * 
 * Đại lý
 */


class AgencyController extends Controller
{
    /**
     * Sắp xếp lại thứ tự agency type
     * @urlParam  store_code required Store code cần xóa.
     * 
     * @bodyParam  List<ids> required List id cate VD: [4,8,9]
     * @bodyParam  List<levels> required List vị trí theo danh sách id ở trên [1,2,3]
     */
    public function sortLevel(Request $request, $id)
    {
        $i = 0;
        if (is_array($request->ids) && is_array($request->levels)) {
            foreach ($request->ids as $id1) {
                $type = AgencyType::where('store_id', $request->store->id)->where('id', $id1)->first();
                if ($type != null) {


                    $type->update([
                        "level" =>  $request->levels[$i]
                    ]);
                }
                $i++;
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AgencyType::where('store_id', $request->store->id)->orderBy('level', 'asc')->get(),
        ], 200);
    }

    /**
     * DS tầng đại lý
     */
    public function getAgencyType(Request $request)
    {

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AgencyType::where('store_id', $request->store->id)->orderBy('level', 'asc')->get(),
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
     * @queryParam  report_type báo cáo theo xu, hay theo đơn hàng (order, point)
     * 
     */
    public function getAllAgencyTop(Request $request, $id)
    {


        $report_type = 'order';



        if (request('report_type') == 'point') {
            $report_type = 'point';
        }


        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        if ($report_type == 'point') {

            $datas = Agency::sortByRelevance(true)
                ->when(Agency::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                    $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
                })
                ->where(
                    'agencies.store_id',
                    $request->store->id
                )

                ->leftJoin('point_histories', 'agencies.customer_id', '=', 'point_histories.customer_id')
                ->selectRaw('agencies.*, count(point_histories.id) as points_count, sum(point_histories.point) as sum_point')
                ->groupBy('agencies.id')
                ->where('point_histories.created_at', '>=',  $dateFrom)
                ->where('point_histories.created_at', '<', $dateTo)
                ->where('point_histories.type', PointCustomerUtils::BONUS_POINT_AGENCY)
                ->when(request('sort_by') == "points_count", function ($query) use ($sortColumn) {
                    $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
                })
                ->when(request('agency_type_id') != null, function ($query) use ($sortColumn) {
                    $query->where('agency_type_id', request('agency_type_id'));
                })

                ->when(request('sort_by') == "sum_point", function ($query) use ($sortColumn) {
                    $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
                })

                ->orderBy('sum_point', 'desc')
                ->search(request('search'))
                ->paginate(
                    request('limit') == null ? 20 : request('limit')
                );
        } else {
            $datas = Agency::sortByRelevance(true)
                ->when(Agency::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                    $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
                })
                ->where(
                    'agencies.store_id',
                    $request->store->id
                )
                ->leftJoin('orders', 'agencies.customer_id', '=', 'orders.agency_by_customer_id')
                ->selectRaw('agencies.*, count(orders.id) as orders_count, sum(orders.total_final) as sum_total_final, sum(total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount) as total_after_discount_no_bonus')
                ->groupBy('agencies.id')
                // ->where('orders.created_at', '>=',  $dateFrom)
                // ->where('orders.created_at', '<', $dateTo)
                ->where('orders.completed_at', '>=',  $dateFrom)
                ->where('orders.completed_at', '<', $dateTo)
                ->where('orders.order_status', StatusDefineCode::COMPLETED)
                ->where('orders.payment_status', StatusDefineCode::PAID)
                ->when(request('sort_by') == "orders_count", function ($query) use ($sortColumn) {
                    $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
                })
                ->when(request('sort_by') == "sum_total_final", function ($query) use ($sortColumn) {
                    $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
                })
                ->when(request('agency_type_id') != null, function ($query) use ($sortColumn) {
                    $query->where('agency_type_id', request('agency_type_id'));
                })

                ->orderBy('sum_total_final', 'desc')
                ->search(request('search'))
                ->paginate(
                    request('limit') == null ? 20 : request('limit')
                );
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $datas,
        ], 200);
    }

    /**
     * Báo cáo Danh sách CTV theo top hoa hồng
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
    public function getAllAgencyTopShare(Request $request, $id)
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

        $posts = Agency::sortByRelevance(true)
            ->when(Agency::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->where(
                'agencies.store_id',
                $request->store->id
            )
            ->leftJoin('orders', 'agencies.customer_id', '=', 'orders.agency_ctv_by_customer_id')
            ->selectRaw('agencies.*, count(orders.id) as orders_count, sum(orders.total_final) as sum_total_final,sum(orders.total_before_discount) as sum_total_after_discount, sum(orders.share_agency) as sum_share_agency')
            ->groupBy('agencies.id')
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
            ->when(request('sort_by') == "sum_share_agency", function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->orderBy('sum_share_agency', 'desc')

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
     * Thêm tầng đại lý
     * @urlParam  store_code required Store code cần lấy
     * @bodyParam name Tên tầng đại lý
     * @bodyParam position Vị trí trên danh sách
     * @bodyParam auto_set_value_import double required Số tiền tối thiểu nhập hàng
     * @bodyParam auto_set_value_share double required Số tiền tối thiểu hoa hồng
     * 
     */
    public function createAgencyType(Request $request)
    {

        if ($request->name == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 404);
        }

        $gencyTypeExists = AgencyType::where(
            'name',
            $request->name
        )
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($gencyTypeExists != null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 404);
        }

        $create =  AgencyType::create([
            'name' => $request->name,
            'store_id' =>  $request->store->id,
            'position' => $request->position,
            'commission_percent' => $request->commission_percent,
            'auto_set_value_import' => $request->auto_set_value_import,
            'auto_set_value_share' => $request->auto_set_value_share,
        ]);

        $products = Product::where('store_id', $request->store->id)->get();
        foreach ($products as $product) {
            ProductAgencyController::updateAllPriceAgeny($request, $product->id, $create->id, $request->commission_percent ?? 0);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AgencyType::where('id', $create->id)->first(),
        ], 200);
    }


    /**
     * Sửa hoa hồng đại lý
     * 
     * @urlParam  store_code required Store code cần lấy
     * @bodyParam percent_agency hoa hồng
     * @bodyParam is_all toàn bộ khách
     * @bodyParam nếu is_all= false product_ids danh sách id sản phẩm
     * 
     * 
     */

    public function edit_percent_agency(Request $request)
    {
        $is_all = filter_var($request->is_all, FILTER_VALIDATE_BOOLEAN);
        $agency_type_id = request('agency_type_id');

        $agencyTypeByIdExists = AgencyType::where(
            'id',
            $agency_type_id
        )
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($agencyTypeByIdExists  == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
            ], 404);
        }


        $agencyTypeExists = AgencyType::where(
            'name',
            $request->name
        )->where('id', '!=', $agency_type_id)
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($request->percent_agency !== null && $request->percent_agency >= 0 && $request->percent_agency <= 100 && $request->percent_agency !==  $agencyTypeByIdExists->commission_percent) {
            if ($is_all  == true) {
                $products = DB::table('products')->where('store_id', $request->store->id)->get();
                foreach ($products as $product) {
                    ProductAgencyController::updateAllPercentAgeny($request, $product->id, $agencyTypeByIdExists->id, $request->percent_agency ?? 0);
                }
            } else {
                foreach ($request->product_ids as $productId) {
                    ProductAgencyController::updateAllPercentAgeny($request, $productId, $agencyTypeByIdExists->id, $request->percent_agency ?? 0);
                }
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AgencyType::where('id', $agencyTypeByIdExists->id)->first(),
        ], 200);
    }

    /**
     * Sửa giá đại lý
     * @urlParam  store_code required Store code cần lấy
     * @urlParam agency_type_id required agency_type_id
     * @bodyParam is_all toàn bộ khách
     * @bodyParam commission_percent (is_all=false) Hoa hồng bớt
     */
    public function override_price(Request $request)
    {
        $agency_type_id = request('agency_type_id');
        $gencyTypeByIdExists = AgencyType::where(
            'id',
            $agency_type_id
        )
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($gencyTypeByIdExists  == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
            ], 404);
        }

        $is_all = filter_var($request->is_all, FILTER_VALIDATE_BOOLEAN);
        if ($request->commission_percent !== null && $request->commission_percent >= 0 && $request->commission_percent <= 100 && $request->commission_percent !==  $gencyTypeByIdExists->commission_percent) {
            if ($is_all  == true) {
                $products = DB::table('products')->where('store_id', $request->store->id)->get();
                foreach ($products as $product) {
                    ProductAgencyController::updateAllPriceAgeny($request, $product->id, $gencyTypeByIdExists->id, $request->commission_percent ?? 0);
                }
            } else {
                foreach ($request->product_ids as $productId) {
                    ProductAgencyController::updateAllPriceAgeny($request, $productId, $gencyTypeByIdExists->id, $request->commission_percent ?? 0);
                }
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AgencyType::where('id', $gencyTypeByIdExists->id)->first(),
        ], 200);
    }

    /**
     * Cập nhật tầng đại lý
     * @urlParam  store_code required Store code cần lấy
     * @urlParam agency_type_id required agency_type_id
     * @bodyParam name Tên tầng đại lý
     * @bodyParam position Vị trí trên danh sách
     * @bodyParam auto_set_value_import double required Số tiền tối thiểu nhập hàng
     * @bodyParam auto_set_value_share double required Số tiền tối thiểu hoa hồng
     * 
     */
    public function updateAgencyType(Request $request)
    {

        $agency_type_id = request('agency_type_id');

        $gencyTypeByIdExists = AgencyType::where(
            'id',
            $agency_type_id
        )
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($gencyTypeByIdExists  == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
            ], 404);
        }


        if ($request->name == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 404);
        }

        $gencyTypeExists = AgencyType::where(
            'name',
            $request->name
        )->where('id', '!=', $agency_type_id)
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($gencyTypeExists != null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 404);
        }


        if ($request->commission_percent != null && $request->commission_percent >= 0 && $request->commission_percent <= 100 && $request->commission_percent !=  $gencyTypeByIdExists->commission_percent) {

            $products = Product::where('store_id', $request->store->id)->get();
            foreach ($products as $product) {
                ProductAgencyController::updateAllPriceAgeny($request, $product->id, $gencyTypeByIdExists->id, $request->commission_percent ?? 0);
            }
        }

        $gencyTypeByIdExists->update([
            'name' => $request->name,
            'store_id' =>  $request->store->id,
            'position' => $request->position,
            'commission_percent' => $request->commission_percent,
            'auto_set_value_import' => $request->auto_set_value_import,
            'auto_set_value_share' => $request->auto_set_value_share,
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AgencyType::where('id', $gencyTypeByIdExists->id)->first(),
        ], 200);
    }



    /**
     * Xóa 1 tầng đại lý
     * @urlParam  store_code required Store code cần lấy
     * @urlParam agency_type_id required agency_type_id
     */
    public function deleteAgencyType(Request $request)
    {

        $agency_type_id = request('agency_type_id');

        $gencyTypeByIdExists = AgencyType::where(
            'id',
            $agency_type_id
        )
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($gencyTypeByIdExists  == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
            ], 404);
        }


        $gencyTypeByIdExists->delete();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Auto set tầng đại lý theo thiết đặt
     * 
     * @bodyParam is_all phải tất cả đại lý không
     * @bodyParam agency_ids theo id đại lý
     * 
     */
    public function auto_set_level_agency_type(Request $request)
    {
        $allType = AgencyType::where('store_id', $request->store->id)->orderBy('auto_set_value_import', 'desc')->get();


        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $agencies = Agency::where(
            'agencies.store_id',
            $request->store->id
        )
            ->when(filter_var($request->is_all, FILTER_VALIDATE_BOOLEAN)  == false, function ($query) use ($request) {
                if (is_array($request->agency_ids)) {
                    $query->whereIn('agencies.id', $request->agency_ids);
                }
            })

            ->get();

        $agenciesImports = Agency::where(
            'agencies.store_id',
            $request->store->id
        )
            ->leftJoin('orders', 'agencies.customer_id', '=', 'orders.agency_by_customer_id')
            ->selectRaw('agencies.*, count(orders.id) as orders_count, sum(orders.total_final) as sum_total_final, sum(orders.share_agency) as sum_share_agency,  sum(orders.total_before_discount - orders.combo_discount_amount - orders.product_discount_amount - orders.voucher_discount_amount) as sum_total_after_discount_no_use_bonus')
            ->groupBy('agencies.id')
            ->where('orders.created_at', '>=',  $dateFrom)
            ->where('orders.created_at', '<', $dateTo)
            ->where('orders.order_status', StatusDefineCode::COMPLETED)
            ->where('orders.payment_status', StatusDefineCode::PAID)
            ->when(filter_var($request->is_all, FILTER_VALIDATE_BOOLEAN)  == false, function ($query) use ($request) {
                if (is_array($request->agency_ids)) {
                    $query->whereIn('agencies.id', $request->agency_ids);
                }
            })
            ->orderBy('sum_total_final', 'desc')
            ->get();

        $agenciesCTV = Agency::where(
            'agencies.store_id',
            $request->store->id
        )
            ->leftJoin('orders', 'agencies.customer_id', '=', 'orders.agency_ctv_by_customer_id')
            ->selectRaw('agencies.*, count(orders.id) as orders_count, sum(orders.total_final) as sum_total_final, sum(orders.share_agency) as sum_share_agency,  sum(orders.total_before_discount - orders.combo_discount_amount - orders.product_discount_amount - orders.voucher_discount_amount) as sum_total_after_discount_no_use_bonus')
            ->groupBy('agencies.id')
            ->where('orders.created_at', '>=',  $dateFrom)
            ->where('orders.created_at', '<', $dateTo)
            ->where('orders.order_status', StatusDefineCode::COMPLETED)
            ->where('orders.payment_status', StatusDefineCode::PAID)
            ->when(filter_var($request->is_all, FILTER_VALIDATE_BOOLEAN)  == false, function ($query) use ($request) {
                if (is_array($request->agency_ids)) {
                    $query->whereIn('agencies.id', $request->agency_ids);
                }
            })
            ->orderBy('sum_total_final', 'desc')
            ->get();

        $agencyDataCTV = null;
        foreach ($agenciesCTV  as $agency) {
            $agencyDataCTV[$agency->id] = $agency->sum_share_agency;
        }

        $agencyDataImport = null;
        foreach ($agenciesImports  as $agency) {
            $agencyDataImport[$agency->id] = $agency->sum_total_after_discount_no_use_bonus;
        }


        foreach ($agencies  as $agency) {

            $typeId = null;
            $sum_share_agency =  $agencyDataCTV[$agency->id] ?? 0;
            $sum_total_after_discount_no_use_bonus = $agencyDataImport[$agency->id] ?? 0;


            foreach ($allType as $type) {

                if (
                    $sum_share_agency >=  $type->auto_set_value_share
                    && $sum_total_after_discount_no_use_bonus  >=  $type->auto_set_value_import
                ) {
                    $typeId = $type->id;

                    if ($type->id != $agency->agency_type_id) {


                        HistoryChangeLevelAgency::create([
                            "store_id" =>  $request->store->id,
                            "agency_id" => $agency->id,
                            "last_agency_type_name" =>  $agency->agency_type == null ? null : $agency->agency_type->name,
                            "new_agency_type_name" => $type->name,


                            "last_agency_type_id" =>  $agency->agency_type == null ? null : $agency->agency_type->id,
                            "new_agency_type_id" => $type->id,

                            "auto_set_value_share" => $type == null ? null : $type->auto_set_value_share,
                            "auto_set_value_import" => $type == null ? null : $type->auto_set_value_import,

                            "current_share_agency"  =>  $sum_share_agency,
                            "current_total_after_discount_no_use_bonus" =>   $sum_total_after_discount_no_use_bonus,

                            "date_from"  =>   $dateFrom,
                            "date_to" =>    $dateTo,

                            "action_from" => 0
                        ]);

                        $agency->update([
                            "agency_type_id" =>  $type->id,
                        ]);
                    }
                    break;
                }
            }


            if ($typeId == null && $typeId  != $agency->agency_type_id) {

                HistoryChangeLevelAgency::create([
                    "store_id" =>  $request->store->id,
                    "agency_id" => $agency->id,
                    "last_agency_type_name" =>  $agency->agency_type == null ? null : $agency->agency_type->name,
                    "new_agency_type_name" => null,

                    "last_agency_type_id" =>  $agency->agency_type == null ? null : $agency->agency_type->id,
                    "new_agency_type_id" => null,


                    "auto_set_value_share" => $type == null ? null : $type->auto_set_value_share,
                    "auto_set_value_import" => $type == null ? null : $type->auto_set_value_import,

                    "date_from"  =>   $dateFrom,
                    "date_to" =>    $dateTo,

                    "current_share_agency"  => $sum_share_agency,
                    "current_total_after_discount_no_use_bonus" => $sum_total_after_discount_no_use_bonus,

                    "action_from" => 0
                ]);

                $agency->update([
                    "agency_type_id" => null,
                ]);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AgencyType::where('store_id', $request->store->id)->get(),
        ], 200);
    }

    /**
     * 
     * Danh sách lịch sử thay đổi cấp đại lý
     * 
     * 
     * 
     */
    public function getHistoryChangeLevelAgency(Request $request)
    {

        $agency_ids = request("agency_ids") == null ? [] : explode(',', request("agency_ids"));

        $posts = HistoryChangeLevelAgency::when(count($agency_ids) > 0, function ($query) use ($agency_ids) {
            $query->whereIn('agency_id', $agency_ids);
        })->where('store_id', $request->store->id)
            // ->search(request('search'))
            ->orderBy('id', 'desc')
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
     * Danh sách Đại lý
     * @urlParam  store_code required Store code
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: covid 19
     * @queryParam  sort_by Sắp xếp theo VD: time
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam agency_type_id  Id tầng đại lý
     */
    public function getAllAgency(Request $request, $id)
    {
        $search = request('search');
        $categoryIds = request("category_ids") == null ? [] : explode(',', request("category_ids"));

        $agency_type_id = request("agency_type_id");

        $agencies = Agency::sortByRelevance(true)
            ->when(Agency::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when($agency_type_id != null, function ($query) use ($agency_type_id) {
                $query->where('agency_type_id', '=',   $agency_type_id);
            })->where('agencies.store_id', $request->store->id)
            ->where('agencies.status', true)
            ->whereHas('customer', function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%');
            })
            ->whereHas('customer', function ($query) {
                $query->where('is_agency', true);
            })
            ->orderBy('id', 'desc')
            ->paginate(request('limit') == null ? 20 : request('limit'));


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $agencies,
        ], 200);
    }

    /**
     * Lấy thông số chia sẻ cho Đại lý
     * @urlParam  store_code required Store code cần lấy
     * @bodyParam allow_payment_request cho phép gửi yêu cầu thanh toán
     * @bodyParam type_rose 0 doanh số, 1 hoa hồng
     * @bodyParam payment_1_of_month Quyết toán ngày 1 hàng tháng ko
     * @bodyParam payment_16_of_month Quyết toán ngày 15 hàng tháng ko
     * @bodyParam payment_limit Số tiền hoa hồng được quyết toán
     */
    public function getConfig(Request $request)
    {
        $columns = Schema::getColumnListing('agency_configs');

        $configExists = AgencyConfig::where(
            'store_id',
            $request->store->id
        )->first();

        if ($configExists == null) {
            $configExists = AgencyConfig::create([
                'store_id' => $request->store->id,
                'allow_payment_request' => false,
                'payment_1_of_month' => false,
                'payment_16_of_month' => false,
                'payment_limit' => 0,
            ]);
        }

        $configResponse = new AgencyConfig();

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
     * Cập nhật 1 số thuộc tính cho agencys                                                                                                                         
     * @urlParam  store_code required Store code
     * @urlParam  agency_id required id trong danh sach cong tac vien
     * @bodyParam status int Trạng thái Đại lý 1 (Hoạt động)  0 đã hủy
     * @bodyParam agency_type_id  Id tầng đại lý
     */
    public function update_for_agency(Request $request)
    {


        $id = $request->route()->parameter('agency_id');

        $agencyExists = Agency::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        if ($agencyExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_AGENCY_EXISTS[0],
                'msg' => MsgCode::NO_AGENCY_EXISTS[1],
            ], 400);
        }

        $customerExist = Customer::where('id', $agencyExists->customer_id)->first();


        if ($request->status == 1) {

            $c = Collaborator::where('customer_id',  $agencyExists->customer_id)->first();

            if ($c != null && $c->status == 1) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Khách hàng đã là CTV không thể là Đại lý, hãy tắt quyền CTV trước",
                ], 400);
            }

            $rq = CollaboratorRegisterRequest::where('store_id', $request->store->id)
                ->where('customer_id', $agencyExists->customer_id)
                ->orderBy('id', 'desc')
                ->first();

            if ($rq != null && ($rq->status == 0 || $rq->status == 3)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Khách hàng đang gửi yêu cầu làm CTV không thể là Đại lý, hãy hủy yêu cầu CTV",
                ], 400);
            }
        }

        if ($agencyExists->status !=  $request->status && $request->status  == 1) {
            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $agencyExists->customer_id,
                "Chúc mừng",
                "Hồ sơ đại lý của bạn đã được duyệt",
                TypeFCM::GET_AGENCY,
                null
            );
        }


        if ($request->status  == 1) {
            $customerExist->update(
                [
                    'is_collaborator' => false,
                    'is_agency' => true,
                ]
            );
        }

        if ($agencyExists->status !=  $request->status && $request->status  === 0) {
            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $agencyExists->customer_id,
                "Chú ý",
                "Bạn đã bị hủy tư cách đại lý",
                TypeFCM::CANCEL_AGENCY,
                null
            );
        }

        $agencyExists->update(Helper::sahaRemoveItemArrayIfNullValue(
            [
                "status" => $request->status,
                "agency_type_id" => $request->agency_type_id
            ]
        ));

        if ($request->status  === 0) {
            $customerExist->update(
                [
                    'is_agency' => false,
                ]
            );
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  Agency::where('store_id',  $request->store->id)->where('id', $id)->first()
        ], 200);
    }

    /**
     * Cập nhật cấu hình cài đặt cho phần Đại lý
     * @urlParam  store_code required Store code
     * @bodyParam allow_payment_request cho phép gửi yêu cầu thanh toán
     * @bodyParam payment_1_of_month Quyết toán ngày 1 hàng tháng ko
     * @bodyParam payment_16_of_month Quyết toán ngày 15 hàng tháng ko
     * @bodyParam payment_limit Số tiền hoa hồng được quyết toán
     */
    public function update(Request $request)
    {

        $callaboratorExists = AgencyConfig::where(
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
            $callaboratorExists = AgencyConfig::create([
                'store_id' => $request->store->id,
                'allow_payment_request' => filter_var($request->allow_payment_request ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_1_of_month' => filter_var($request->payment_1_of_month ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_16_of_month' => filter_var($request->payment_16_of_month ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_limit' => $request->payment_limit ?? 0,
                'percent_agency_t1' => $request->percent_agency_t1 ?? 0,
                'allow_rose_referral_customer'  => filter_var($request->allow_rose_referral_customer ?? false, FILTER_VALIDATE_BOOLEAN),
                'bonus_type_for_ctv_t2' => $request->bonus_type_for_ctv_t2 ?? 0,

                "auto_set_level_agency" => $request->auto_set_level_agency,
                "auto_set_type_period" => $request->auto_set_type_period,
            ]);
        } else {
            $callaboratorExists->update([
                'allow_payment_request' => filter_var($request->allow_payment_request ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_1_of_month' => filter_var($request->payment_1_of_month ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_16_of_month' => filter_var($request->payment_16_of_month ?? false, FILTER_VALIDATE_BOOLEAN),
                'payment_limit' => $request->payment_limit ?? 0,
                'percent_agency_t1' => $request->percent_agency_t1 ?? 0,
                'allow_rose_referral_customer'  => filter_var($request->allow_rose_referral_customer ?? false, FILTER_VALIDATE_BOOLEAN),
                'bonus_type_for_ctv_t2' => $request->bonus_type_for_ctv_t2 ?? 0,

                "auto_set_level_agency" => $request->auto_set_level_agency,
                "auto_set_type_period" => $request->auto_set_type_period,
            ]);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  AgencyConfig::where('store_id',  $request->store->id)->first()
        ], 200);
    }



    /**
     * Cấu hình thưởng cho đại lý kỳ thưởng
     * @urlParam  store_code required Store code
     * 
     * @bodyParam type_bonus_period_import kỳ thưởng 0 theo tháng, 1 theo tuần, 2 theo quý, 3 theo năm
     * 
     */
    public function updateConfigTypeBonusPeriodImport(Request $request)
    {

        $callaboratorExists = AgencyConfig::where(
            'store_id',
            $request->store->id
        )->first();

        if ($callaboratorExists == null) {
            AgencyConfig::create([
                "type_bonus_period_import" => $request->type_bonus_period_import
            ]);
        } else {
            $callaboratorExists->update([
                "type_bonus_period_import" => $request->type_bonus_period_import
            ]);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  AgencyConfig::where('store_id',  $request->store->id)->first()
        ], 200);
    }

    /**
     * Thêm 1 bậc tiền thưởng 1 tháng
     * 
     * @urlParam  store_code required Store code
     * @bodyParam limit double required Giới hạn được thưởng
     * @bodyParam bonus double required Số tiền thưởng
     * 
     */
    public function create(Request $request)
    {

        $callaboratorExists = AgencyBonusStep::where(
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

        $callaboratorExists = AgencyBonusStep::create([
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

        $steps = AgencyBonusStep::where('store_id', $request->store->id)->orderBy('bonus', 'asc')->get();;

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
        $checkStepExists = AgencyBonusStep::where(
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
        $checkStepExists = AgencyBonusStep::where(
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
                'data' => AgencyBonusStep::where('id', $id)->first(),
            ], 200);
        }
    }


    /**
     * Cộng trừ tiền cho đại lý
     * 
     * @bodyParam is_sub bool cộng hay trừ
     * @bodyParam money số tiền ()
     * @bodyParam reason lý do
     * 
     * 
     */
    public function addSubBalanceAgency(Request $request)
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

        $id = $request->route()->parameter('agency_id');

        $agencyExists = Agency::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        if ($agencyExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_AGENCY_EXISTS[0],
                'msg' => MsgCode::NO_AGENCY_EXISTS[1],
            ], 400);
        }

        BalanceCustomerService::change_balance_agency(
            $request->store->id,
            $agencyExists->customer->id,
            $is_sub == true ?  BalanceCustomerService::SUB_BALANCE_CTV  :  BalanceCustomerService::ADD_BALANCE_CTV,
            $is_sub == true ? -$request->money : $request->money,
            Helper::getRandomOrderString(),
            null,
            $request->reason

        );

        $agencyExists = Agency::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $agencyExists,
        ], 200);
    }


    /**
     * Lịch sử thay đổi số dư đại lý
     * 
     * 
     */
    public function historyChangeBalance(Request $request)
    {

        $id = $request->route()->parameter('agency_id');

        $agencyExists = Agency::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        if ($agencyExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_AGENCY_EXISTS[0],
                'msg' => MsgCode::NO_AGENCY_EXISTS[1],
            ], 400);
        }

        $histories = ChangeBalanceAgency::where('store_id', $request->store->id)
            ->where('agency_id',  $id)
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
     * Danh sách yêu cầu làm đại lý
     * @urlParam  store_code required Store code
     * 
     * 
     * @queryParam  page Lấy danh sách yêu cầu đại lý ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: covid 19
     * @queryParam  status trạng thái 0 chờ xử lý, 1 đã hủy, 2 đồng ý, 3 yêu cầu lại

     */
    public function getAllAgencyRegisterRequest(Request $request, $id)
    {

        $listAgencyRegisterRequest = AgencyRegisterRequest::where(
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
            'data' => $listAgencyRegisterRequest,
        ], 200);
    }

    /**
     * Xử lý yêu cầu làm đại lý
     * @urlParam  store_code required Store code
     * 
     * 
     * @queryParam  page Lấy danh sách yêu cầu đại lý ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: covid 19
     * @queryParam  status trạng thái 0 chờ xử lý, 1 đã hủy, 2 đồng ý, 3 yêu cầu lại
     * @queryParam  note Ghi chú
     */

    public function handleAgencyRegisterRequest(Request $request, $id)
    {
        $agency_register_request = $request->agency_register_request_id;

        $requestAgency = AgencyRegisterRequest::where(
            'store_id',
            $request->store->id
        )
            ->where('id', $agency_register_request)->first();

        if ($requestAgency == null) {
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
        )->where('id', $requestAgency->customer_id)->first();

        $agency = Agency::where(
            'store_id',
            $request->store->id
        )->where('customer_id', $requestAgency->customer_id)->first();

        if ($request->status == 2) {

            $allTypeAgency = AgencyType::where('store_id', $request->store->id)->get();
            if (count($allTypeAgency) > 0 && $request->agency_type_id) {
                $agencyTypeByIdExists = AgencyType::where('id', $request->agency_type_id)
                    ->where('store_id', $request->store->id)
                    ->first();

                if ($agencyTypeByIdExists  == null) {
                    return response()->json([
                        'code' => 404,
                        'success' => false,
                        'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                        'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
                    ], 404);
                }
            }

            $requestAgency->update([
                'status' => 2
            ]);
            $customer->update([
                'is_agency' =>  true,
                'official' => true,
            ]);
            $agency->update([
                'status' =>  1,
                'agency_type_id' => $request->agency_type_id
            ]);

            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $customer->id,
                "Ghi chú",
                "Yêu cầu làm đại lý đã được duyệt",
                TypeFCM::GET_AGENCY,
                null
            );
        }

        if ($request->status == 1) {

            $requestAgency->update([
                'status' => 1
            ]);
            $customer->update([
                'is_agency' =>  false,
            ]);
            $agency->update([
                'status' =>  0,
            ]);

            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $customer->id,
                "Ghi chú",
                "Yêu cầu làm đại lý đã bị hủy",
                TypeFCM::CANCEL_AGENCY,
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


    /**
     * Thêm 1 bậc tiền thưởng 1 tháng
     * @urlParam  store_code required Store code
     * @bodyParam limit double required Giới hạn được thưởng
     * @bodyParam bonus double required Số tiền thưởng
     */
    public function createStepImport(Request $request)
    {

        $callaboratorExists = AgencyImportStep::where(
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

        $callaboratorExists = AgencyImportStep::create([
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
    public function getStepImportBonusAll(Request $request)
    {

        $steps = AgencyImportStep::where('store_id', $request->store->id)->orderBy('bonus', 'asc')->get();;

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
    public function deleteOneStepImport(Request $request, $id)
    {

        $id = $request->route()->parameter('step_id');
        $checkStepExists = AgencyImportStep::where(
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
    public function updateOneStepImport(Request $request)
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
        $checkStepExists = AgencyImportStep::where(
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
                'data' => AgencyImportStep::where('id', $id)->first(),
            ], 200);
        }
    }
}
