<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\RevenueExpenditureUtils;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\RevenueExpenditure;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;


/**
 * @group  User/Báo cáo tài chính
 */
class ReportFinanceController extends Controller
{
    /**
     * Danh sách nhà cung cấp đang nợi\\
     * 
     * @queryParam  date Date Ngày xem
     * 
     */
    public function report_supplier_debt(Request $request)
    {
        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';

        $arr_id_supplier = [];
        $arr_id_supplier_with_debt = [];
        $suppliers = Supplier::where('store_id', $request->store->id)->get();

        $total_debt = 0;
        foreach ($suppliers  as $supplier) {
            $revenueExpenditure = RevenueExpenditure::where('store_id', $request->store->id)
                ->where('recipient_references_id', $supplier->id)
                ->where('recipient_group', RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER)
                ->orderBy('updated_at', 'desc')
                ->when(request('date_from'), function ($query) use ($dateFrom) {
                    $query->where('updated_at', '>=', $dateFrom);
                })
                ->when(request('date_to'), function ($query) use ($dateTo) {
                    $query->where('updated_at', '<=', $dateTo);
                })
                ->first();


            $debt =   $revenueExpenditure == null ? 0 :    $revenueExpenditure->current_money;
            if ($debt > 0) {

                array_push($arr_id_supplier, $supplier->id);
                $arr_id_supplier_with_debt[$supplier->id] =  $debt;
                $total_debt += $debt;
            }
        }


        $suppliers = Supplier::where('store_id', $request->store->id)
            ->whereIn('id', $arr_id_supplier)
            ->orderBy('created_at', 'ASC')
            ->search(request('search'))->paginate(request('limit') == null ? 20 : request('limit'));

        foreach ($suppliers  as $supplier) {


            $supplier->debt =  $arr_id_supplier_with_debt[$supplier->id];
        }


        $custom = collect(
            [
                'debt' => $total_debt
            ]
        );

        $data = $custom->merge($suppliers);
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data,
        ], 200);
    }

    /**
     * Danh sách khách hàng đang nợ
     * 
     * @queryParam  date Date Ngày xem
     * 
     */
    public function report_customer_debt(Request $request)
    {
        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';

        $arr_id_customer = [];
        $arr_id_customer_with_debt = [];
        $customers = Customer::where('store_id', $request->store->id)->get();

        $total_debt = 0;
        foreach ($customers  as $customer) {
            $revenueExpenditure = RevenueExpenditure::where('store_id', $request->store->id)
                ->where('recipient_references_id', $customer->id)
                ->where('recipient_group', RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER)
                ->orderBy('id', 'desc')
                ->when(request('date_from'), function ($query) use ($dateFrom) {
                    $query->where('updated_at', '>=', $dateFrom);
                })
                ->when(request('date_to'), function ($query) use ($dateTo) {
                    $query->where('updated_at', '<=', $dateTo);
                })
                ->first();

            $debt =  $revenueExpenditure == null ? 0 : $revenueExpenditure->current_money;
            if ($debt > 0) {

                array_push($arr_id_customer, $customer->id);
                $arr_id_customer_with_debt[$customer->id] =  $debt;
                $total_debt += $debt;
            }
        }


        $customers = Customer::where('store_id', $request->store->id)
            ->whereIn('id', $arr_id_customer)
            ->orderBy('created_at', 'ASC')->search(request('search'))
            ->paginate(request('limit') == null ? 20 : request('limit'));

        foreach ($customers  as $customer) {

            $customer->debt =  $arr_id_customer_with_debt[$customer->id];
        }

        $custom = collect(
            [
                'debt' => $total_debt
            ]
        );

        $data = $custom->merge($customers);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data,
        ], 200);
    }


    /**
     * Báo cáo lãi lỗ
     * 
     * Profit and Loss
     * 
     * 
     *  sales_revenue Doanh thu bán hàng (1)
     * 
     *  real_money_for_sale Tiền hàng thực bán
     * 
     *  tax_vat = 10000 thuế vat
     * 
     *  customer_delivery_fee phí giao hàng thu của khách
     * 
     *  total_discount Giảm giá 
     * 
     *  product_discount giảm giá sản phẩm
     *  
     *  combo giảm giá combo
     * 
     *  voucher giảm giá voucher
     * 
     *  discount  chiết khấu
     * 
     *  selling_expenses Chi phí bán hàng (2)
     * 
     *  cost_of_sales giá vốn bán hàng
     * 
     *  pay_with_points thanh toán bằng điểm
     * 
     *  partner_delivery_fee phí giao hàng đối tác
     * 
     *  other_income  thu nhập khác (3)
     * 
     *  $revenue_auto_create thu tự tạo
     * 
     *  customer_return  khách trả hàng
     * 
     *  other_costs   Chi phí khác (4)
     * 
     *  profit  Lợi nhuận   (1-2+3-4)
     * 
     * 
     * 
     * 
     * @queryParam  date Date Ngày xem
     * 
     */
    public function profit_and_loss(Request $request)
    {
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';

        $branch = request('branch', $default = null);
        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $branch_ids_input = array();
        if ($branch != null) {
            $branch_ids_input = [$branch->id];
        } else if (count($branch_ids) > 0) {
            $branch_ids_input =  $branch_ids;
        }

        $orders = Order::where('store_id', $request->store->id)
            ->where('created_at', '>=',  $dateFrom)
            ->where('created_at', '<', $dateTo)
            // ->where('order_status', StatusDefineCode::COMPLETED)
            // ->where('payment_status', StatusDefineCode::PAID)
            ->whereIn('branch_id', $branch_ids_input)
            ->get();



        $sales_revenue =  0; //Doanh thu bán hàng (1)
        $real_money_for_sale =  0; //Tiền hàng thực bán

        $money_back = 0;
        $money_sales =   0;

        $tax_vat = 0; //thuế vat
        $customer_delivery_fee = 0; //phí giao hàng thu của khách

        $total_discount = 0; //tổng giảm giá
        $product_discount = 0; //giảm giá sản phẩm
        $voucher = 0; //giảm giá voucher
        $combo = 0; //giảm giá combo
        $discount = 0; //chiết khấu

        $selling_expenses = 0; //Chi phí bán hàng (2)
        $cost_of_sales = 0; //giá vốn bán hàng
        $pay_with_points = 0; // thanh toán bằng điểm
        $partner_delivery_fee = 0; //phí giao hàng đối tác

        $other_income = 0; //thu nhập khác (3) Phiếu thu(không tính cho đơn hàng)
        $revenue_auto_create = 0; //thu tự tạo
        $customer_return = 0; //khách trả hàng

        $other_costs = 0; // Chi phí khác (4) Phiếu chi(không tính cho đơn hàng)


        foreach ($orders  as $order) {
            if ($order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {
                //Tien hang ban ra
                $money_back += $order->total_before_discount;

                //giam gia
                $product_discount -=  $order->product_discount_amount;
                $voucher -=  $order->voucher_discount_amount;
                $combo -=  $order->combo_discount_amount;
                $discount -=  $order->discount;

                $customer_delivery_fee  -=  $order->total_shipping_fee;

                //gia von ban hang
                $cost_of_sales -=  $order->total_cost_of_capital;

                $pay_with_points -= $order->bonus_points_amount_used;

                //thuế vat
                $tax_vat -= $order->vat;
            } else if ($order->order_status == StatusDefineCode::COMPLETED) {
                //Tien hang ban ra
                $money_sales += $order->total_before_discount;

                //giam gia
                $product_discount +=  $order->product_discount_amount;
                $voucher +=  $order->voucher_discount_amount;
                $combo +=  $order->combo_discount_amount;
                $discount +=  $order->discount;

                $customer_delivery_fee  +=  $order->total_shipping_fee;

                //gia von ban hang
                $cost_of_sales +=  $order->total_cost_of_capital;

                $pay_with_points += $order->bonus_points_amount_used;

                //thuế vat
                $tax_vat += $order->vat;
            }
        }

        $revenue_expenditures = RevenueExpenditure::where('store_id', $request->store->id)
            ->where('created_at', '>=',  $dateFrom)
            ->where('created_at', '<', $dateTo)
            ->whereIn('branch_id', $branch_ids_input)
            ->get();

        foreach ($revenue_expenditures as $revenue_expenditure) {
            if ($revenue_expenditure->is_revenue) {
                if ($revenue_expenditure->type == RevenueExpenditureUtils::TYPE_OTHER_INCOME || $revenue_expenditure->type == RevenueExpenditureUtils::TYPE_BONUS || $revenue_expenditure->type == RevenueExpenditureUtils::TYPE_INDEMNIFICATION || $revenue_expenditure->type == RevenueExpenditureUtils::TYPE_RENTAL_PROPERTY || $revenue_expenditure->type == RevenueExpenditureUtils::TYPE_SALE_AND_LIQUIDATION_OF_ASSETS) {
                    //III. Thu nhập khác
                    $other_income += $revenue_expenditure->change_money;
                };
            }

            if ($revenue_expenditure->is_revenue == false) {
                if ($revenue_expenditure->type == RevenueExpenditureUtils::TYPE_OTHER_COSTS || $revenue_expenditure->type == RevenueExpenditureUtils::TYPE_PRODUCTION_COST || $revenue_expenditure->type == RevenueExpenditureUtils::TYPE_COST_OF_RAW_MATERIALS || $revenue_expenditure->type == RevenueExpenditureUtils::TYPE_COST_OF_LIVING || $revenue_expenditure->type == RevenueExpenditureUtils::TYPE_LABOR_COSTS || $revenue_expenditure->type == RevenueExpenditureUtils::TYPE_STORE_MANAGEMENT_COSTS) {

                    //IV. Chi phí khác
                    $other_costs += $revenue_expenditure->change_money;
                }
            }
        }

        $partner_delivery_fee = $customer_delivery_fee;

        //Tien hang thuc ban
        $real_money_for_sale +=  $money_sales - $money_back;

        $total_discount += ($product_discount + $voucher + $combo + $discount);

        //I. Doanh thu ban hang
        $sales_revenue += ($real_money_for_sale -  $total_discount +  $customer_delivery_fee + $tax_vat);

        //II. Chi phi ban hang
        $selling_expenses =  $cost_of_sales + $pay_with_points + $partner_delivery_fee;

        $profit = $sales_revenue - $selling_expenses + $other_income - $other_costs; //Lợi nhuận   (1-2+3-4)
        $data = [
            "sales_revenue" => $sales_revenue, //Doanh thu bán hàng (1)
            "real_money_for_sale" => $real_money_for_sale, //Tiền hàng thực bán

            "money_back" => $money_back, //Tiền hàng trả lại
            "money_sales" => $money_sales, // tiền hàng bán ra


            "tax_vat" => $tax_vat, //thuế vat
            "customer_delivery_fee" => $customer_delivery_fee, //phí giao hàng thu của khách
            "discount" => $discount, //chiết khấu


            "product_discount"  => $product_discount, //giảm giá sản phẩm
            "voucher"  => $voucher, //giảm giá voucher
            "combo"  => $combo,  //giảm giá combo
            "total_discount" => $total_discount, //tổng giảm giá

            "selling_expenses" => $selling_expenses, //Chi phí bán hàng (2)
            "cost_of_sales" => $cost_of_sales, //giá vốn bán hàng
            "pay_with_points" => $pay_with_points, // thanh toán bằng điểm
            "partner_delivery_fee" => $partner_delivery_fee, //phí giao hàng đối tác

            "other_income" => $other_income, //thu nhập khác (3)
            "revenue_auto_create" => $revenue_auto_create, //thu tự tạo
            "customer_return" => $customer_return, //khách trả hàng

            "other_costs" => $other_costs, // Chi phí khác (4)

            "profit" =>  $profit, //Lợi nhuận   (1-2+3-4)

        ];

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data,
        ], 200);
    }
}
