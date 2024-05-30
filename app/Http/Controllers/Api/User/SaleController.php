<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\SaleBonusStep;
use App\Models\SaleConfig;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @group  User/Sale
 */
class SaleController extends Controller
{

    /**
     * Lấy thông số chia sẻ cho cộng tác viên
     * 
     * @bodyParam allow_sale boolean cho phép sale làm việc
     * @bodyParam type_bonus_period kỳ thưởng 0 theo tháng, 1 theo tuần, 2 theo quý, 3 theo năm
     * 
     */
    public function getConfig(Request $request)
    {
        $columns = Schema::getColumnListing('sale_configs');

        $configExists = SaleConfig::where(
            'store_id',
            $request->store->id
        )->first();

        if ($configExists == null) {
            $configExists = SaleConfig::create([
                'store_id' => $request->store->id,
                'allow_sale' =>  filter_var($request->allow_sale ?? false, FILTER_VALIDATE_BOOLEAN),
                'type_bonus_period' =>  $request->type_bonus_period ?? 0,
            ]);
        }

        $configResponse = new SaleConfig();

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
     * Cập nhật cấu hình cài đặt cho Sale
     * 
     * @bodyParam allow_sale boolean cho phép sale làm việc
     * @bodyParam type_bonus_period kỳ thưởng 0 theo tháng, 1 theo tuần, 2 theo quý, 3 theo năm
     * 
     * 
     */
    public function updateConfig(Request $request)
    {

        $saleCExists = SaleConfig::where(
            'store_id',
            $request->store->id
        )->first();

        if ($saleCExists == null) {
            $saleCExists = SaleConfig::create([
                'store_id' => $request->store->id,
                'allow_sale' =>  filter_var($request->allow_sale ?? false, FILTER_VALIDATE_BOOLEAN),
                'type_bonus_period' =>  $request->type_bonus_period ?? 0,
            ]);
        } else {
            $saleCExists->update([
                'allow_sale' =>  filter_var($request->allow_sale ?? false, FILTER_VALIDATE_BOOLEAN),
                'type_bonus_period' =>  $request->type_bonus_period ?? 0,
            ]);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  SaleConfig::where('store_id',  $request->store->id)->first()
        ], 200);
    }

    /**
     * Thêm khách hàng vào cho sale
     *
     * 
     * @bodyParam list_customer_id danh sách id customer cần thêm
     * @bodyParam staff_id Id nhân viên
     * 
     *  
     */
    public function add_customers_to_sale(Request  $request)
    {

        $list_customer_id = $request->list_customer_id;
        $staff_id = $request->staff_id;

        $staff = Staff::where('id', $staff_id)->where('store_id', $request->store->id)
            ->first();

        if ($staff_id  == null || $staff ==  null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 400);
        }

        if ($list_customer_id != null && is_array($list_customer_id)) {
            $now = Helper::getTimeNowDateTime();
            foreach ($list_customer_id as $customer_id) {
                $customer = Customer::where('id', $customer_id)->where('store_id', $request->store->id)
                    ->first();
                if ($customer != null) {
                    $customer->update(
                        [
                            'sale_staff_id' => $staff_id,
                            'time_sale_staff' => $now
                        ]
                    );
                }
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
     * Tổng quan thông tin sale
     * 
     */
    public function getOverview(Request $request, $id)
    {

        $staff_id  = null;
        if (str_contains($request->url(), '/overview_one_sale') == true) {
            $staff_id  =   $request->staff->id;
        } else {
            $staff_id  = request('staff_id');
        }
        if (Staff::where('id', $staff_id)->first() == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 404);
        }

        $carbon = Carbon::now('Asia/Ho_Chi_Minh');

        $dateFromDay = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 00:00:00';
        $dateToDay = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 23:59:59';

        $dateFromMonth = $carbon->year . '-' . $carbon->month . '-' . "01" . ' 00:00:00';
        $dateToMonth = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 23:59:59';

        $dateFromYear = $carbon->year . '-' . "01" . '-' . "01" . ' 00:00:00';
        $dateToYear = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 23:59:59';

        $date = new \Carbon\Carbon();
        $firstOfQuarter = $date->firstOfQuarter();
        $lastOfQuarter = $date->lastOfQuarter();

        $curMonth = date("m", time());
        $curQuarter = (int)ceil($curMonth / 3);

        $dateFromQuarter = $firstOfQuarter->year . '-' .  $curQuarter . '-' . "01" . ' 00:00:00';
        $dateToQuarter =  $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 23:59:59';

        $day = date('w');
        $week_start = date('Y-m-d', strtotime('-' . $day . ' days'));
        $week_end = date('Y-m-d', strtotime('+' . (6 - $day) . ' days'));

        $dateFromWeek = $week_start  . ' 00:00:00';
        $dateToWeek = $week_end . ' 23:59:59';

        $total_order = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->count();
        $total_final = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->sum('total_final');

        //  whereIn('orders.phone_number', [0977711111,0955566666,0987654321,0988776611,0977744444])
        //
        $count_in_day = Order::where('store_id', $request->store->id)
            ->when($staff_id != null, function ($query) use ($staff_id) {
                $query->where('orders.sale_by_staff_id',  $staff_id);
            })
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('orders.created_at', '>=', $dateFromDay)
            ->where('orders.created_at', '<=', $dateToDay)
            ->count();

        $total_final_in_day = Order::where('store_id', $request->store->id)
            ->when($staff_id != null, function ($query) use ($staff_id) {
                $query->where('orders.sale_by_staff_id',  $staff_id);
            })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromDay)
            ->where('orders.created_at', '<=', $dateToDay)
            ->sum('total_final');
        //

        $count_in_month = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromMonth)
            ->where('orders.created_at', '<=', $dateToMonth)
            ->count();

        $total_final_in_month = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromMonth)
            ->where('orders.created_at', '<=', $dateToMonth)
            ->sum('total_final');
        //
        $count_in_week = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromWeek)
            ->where('orders.created_at', '<=', $dateToWeek)
            ->count();

        $total_final_in_week = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromWeek)
            ->where('orders.created_at', '<=', $dateToWeek)
            ->sum('total_final');
        //
        $count_in_year = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromYear)
            ->where('orders.created_at', '<=', $dateToYear)
            ->count();

        $total_final_in_year = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromYear)
            ->where('orders.created_at', '<=', $dateToYear)
            ->sum('total_final');
        //

        $count_in_quarter = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromQuarter)
            ->where('orders.created_at', '<=', $dateToQuarter)
            ->count();

        $total_final_in_quarter = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromQuarter)
            ->where('orders.created_at', '<=', $dateToQuarter)
            ->sum('total_final');


        //////------/--/-//-/-/-/-/-/-//-/

        $total_after_discount_no_bonus = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));


        $total_after_discount_no_bonus_in_day = Order::where('store_id', $request->store->id)
            ->when($staff_id != null, function ($query) use ($staff_id) {
                $query->where('orders.sale_by_staff_id',  $staff_id);
            })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromDay)
            ->where('orders.created_at', '<=', $dateToDay)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));

        $total_after_discount_no_bonus_in_month = Order::where('store_id', $request->store->id)
            ->when($staff_id != null, function ($query) use ($staff_id) {
                $query->where('orders.sale_by_staff_id',  $staff_id);
            })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromMonth)
            ->where('orders.created_at', '<=', $dateToMonth)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));

        $total_after_discount_no_bonus_in_week = Order::where('store_id', $request->store->id)->when($staff_id != null, function ($query) use ($staff_id) {
            $query->where('orders.sale_by_staff_id',  $staff_id);
        })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromWeek)
            ->where('orders.created_at', '<=', $dateToWeek)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));


        $total_after_discount_no_bonus_in_year = Order::where('store_id', $request->store->id)
            ->when($staff_id != null, function ($query) use ($staff_id) {
                $query->where('orders.sale_by_staff_id',  $staff_id);
            })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromYear)
            ->where('orders.created_at', '<=', $dateToYear)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));

        $total_after_discount_no_bonus_in_quarter = Order::where('store_id', $request->store->id)
            ->when($staff_id != null, function ($query) use ($staff_id) {
                $query->where('orders.sale_by_staff_id',  $staff_id);
            })
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromQuarter)
            ->where('orders.created_at', '<=', $dateToQuarter)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));


        $saleCExists = SaleConfig::where(
            'store_id',
            $request->store->id
        )->first();

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => [
                'total_order' =>  $total_order,
                'total_final' =>  $total_final,

                'count_in_day' =>  $count_in_day,
                'total_final_in_day' =>  $total_final_in_day,

                'count_in_month' =>  $count_in_month,
                'total_final_in_month' =>  $total_final_in_month,

                'count_in_week' =>  $count_in_week,
                'total_final_in_week' =>  $total_final_in_week,

                'count_in_year' =>  $count_in_year,
                'total_final_in_year' =>  $total_final_in_year,

                'count_in_quarter' =>  $count_in_quarter,
                'total_final_in_quarter' =>  $total_final_in_quarter,

                'total_after_discount_no_bonus' =>  $total_after_discount_no_bonus,
                'total_after_discount_no_bonus_in_day' =>  $total_after_discount_no_bonus_in_day,
                'total_after_discount_no_bonus_in_month' =>  $total_after_discount_no_bonus_in_month,
                'total_after_discount_no_bonus_in_week' =>  $total_after_discount_no_bonus_in_week,
                'total_after_discount_no_bonus_in_year' =>  $total_after_discount_no_bonus_in_year,
                'total_after_discount_no_bonus_in_quarter' =>  $total_after_discount_no_bonus_in_quarter,

                'steps_bonus' => SaleBonusStep::where('store_id', $request->store->id)->orderBy('bonus', 'asc')->get(),
                'sale_config' =>   $saleCExists
            ],
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
    public function createStep(Request $request)
    {

        $stepExists = SaleBonusStep::where(
            'store_id',
            $request->store->id
        )->where(
            'limit',
            $request->limit
        )->first();

        if ($stepExists != null) {
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

        $stepExists = SaleBonusStep::create([
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

        $steps = SaleBonusStep::where('store_id', $request->store->id)->orderBy('bonus', 'asc')->get();;

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
        $checkStepExists = SaleBonusStep::where(
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
        $checkStepExists = SaleBonusStep::where(
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
                'data' => SaleBonusStep::where('id', $id)->first(),
            ], 200);
        }
    }


    function getModelFilterIds(Request $request)
    {
        $customer_type = request('customer_type') === null ? null : (int)(request('customer_type'));

        $province_ids = request("province_ids") == null ? [] : explode(',', request("province_ids"));
        $staff_ids = request("staff_ids") == null ? [] : explode(',', request("staff_ids"));

        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);


        $datas = Staff::when(request('sort_by') != null, function ($query) {
            $query->orderBy(request('sort_by'), filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
        })
            ->where(
                'staff.store_id',
                $request->store->id
            )
            ->where(
                'staff.is_sale',
                true
            )
            ->join('customers', function ($join) {
                $join->on('staff.id', 'customers.sale_staff_id');
            })
            ->leftJoin('orders', function ($join) {
                $join->on('customers.id', 'orders.customer_id');
            })
            ->whereIn('staff.id', function ($query) {
                $query->select('id')
                    ->from('staff')
                    ->whereColumn('staff.id', 'orders.sale_by_staff_id');
            })
            ->selectRaw(
                'staff.*, customers.province, 
        sum(orders.total_final) as sum_total_final,
        count(DISTINCT orders.id) as orders_count, 
        count(DISTINCT customers.id) as total_customer_in_filer,
        sum(orders.total_before_discount - orders.combo_discount_amount - orders.product_discount_amount - orders.voucher_discount_amount) as sum_total_after_discount_no_use_bonus',
            )
            ->where('orders.created_at', '>=',  $dateFrom)
            ->where('orders.created_at', '<=', $dateTo)
            ->when(count($staff_ids) > 0, function ($query) use ($staff_ids) {
                $query->whereIn('staff.id', $staff_ids);
            })
            ->when($customer_type === 1, function ($query) use ($customer_type) {

                $query->where('customers.is_collaborator', true);
            })
            ->when($customer_type  === 2, function ($query) use ($customer_type) {
                $query->where('customers.is_agency', true);
            })
            ->when($customer_type  === 0, function ($query) use ($customer_type) {
                $query->where('customers.is_agency', false)->where('customers.is_collaborator', false);
            })
            ->when($province_ids != null, function ($query) use ($province_ids) {
                $query->whereIn('customers.province', $province_ids);
            })
            ->where(
                'orders.store_id',
                $request->store->id
            )
            ->where('orders.order_status', StatusDefineCode::COMPLETED)
            ->where('orders.payment_status', StatusDefineCode::PAID)
            ->orderBy('sum_total_final', 'desc');;

        return  $datas;
    }

    /**
     * Báo cáo staff sale theo top 
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
     * @queryParam  customer_type
     * @queryParam  province_id
     * 
     */
    public function getAllStaffSaleTopShare(Request $request)
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

        $datas = $this->getModelFilterIds($request)
            ->groupBy('staff.id')
            // ->search(request('search'))
            ->paginate(request('limit') == null ? 20 : request('limit'));
        $sum_total_after_discount_no_use_bonus = 0;
        foreach ($datas as $d) {
            $sum_total_after_discount_no_use_bonus +=  $d->sum_total_after_discount_no_use_bonus;
        }

        $custom = collect(
            [
                'sum_total_after_discount_no_use_bonus' =>  $sum_total_after_discount_no_use_bonus
            ]
        );

        $datas = $custom->merge($datas);
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $datas,
        ], 200);
    }

    /**
     * Báo cáo staff sale theo top 
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
     * @queryParam  customer_type
     * @queryParam  province_id
     * @queryParam  staff_id
     * 
     */
    public function getIdsCustomerInSaleTopShare(Request $request, $id)
    {
        $customer_type = request('customer_type') === null ? null : (int)(request('customer_type'));

        $province_ids = request("province_ids") == null ? [] : explode(',', request("province_ids"));
        $staff_ids = request("staff_ids") == null ? [] : explode(',', request("staff_ids"));

        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);


        $staff = Staff::where('id', request('staff_id'))->where('store_id', $request->store->id)->first();

        if ($staff == null) {
            return response()->json([
                'code' => 404,
                'success' => true,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 404);
        }


        $datas = Customer::where(
            'staff.store_id',
            $request->store->id
        )
            ->where(
                'staff.is_sale',
                true
            )
            ->where(
                'staff.id',
                $staff->id
            )
            ->join('staff', function ($join) {
                $join->on('staff.id', '=', 'customers.sale_staff_id');
            })
            ->leftJoin('orders', function ($join) {
                $join->on('customers.id', '=', 'orders.customer_id');
            })

            ->selectRaw('customers.*')
            ->where('orders.created_at', '>=',  $dateFrom)
            ->where('orders.created_at', '<', $dateTo)

            ->when($customer_type === 1, function ($query) use ($customer_type) {

                $query->where('customers.is_collaborator', true);
            })
            ->when($customer_type  === 2, function ($query) use ($customer_type) {
                $query->where('customers.is_agency', true);
            })
            ->when($customer_type  === 0, function ($query) use ($customer_type) {
                $query->where('customers.is_agency', false)->where('customers.is_collaborator', false);
            })
            ->when($province_ids != null, function ($query) use ($province_ids) {
                $query->whereIn('customers.province', $province_ids);
            })
            ->where(
                'orders.store_id',
                $request->store->id
            )
            ->groupBy('customers.id')
            ->where('orders.order_status', StatusDefineCode::COMPLETED)
            ->where('orders.payment_status', StatusDefineCode::PAID)
            ->pluck('customers.id')->toArray();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $datas,
        ], 200);
    }
}
