<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\AgencyUtils;
use App\Helper\CollaboratorUtils;
use App\Helper\GroupCustomerUtils;
use App\Helper\Helper;
use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\CustomerVoucher;
use App\Models\MsgCode;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group  Customer/Voucher
 */
class CustomerVoucherController extends Controller
{

    /**
     * Lấy danh sách voucher đang phát hành
     * @urlParam  store_code required Store code cần lấy.
     */
    public function getAllAvailable(Request $request, $id)
    {
        // dd(1);
        $now = Helper::getTimeNowString();
        $vouchers = Voucher::where('store_id', $request->store->id)
            ->where('is_end', false)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('is_public', true)
            ->where('is_use_once_code_multiple_time', true)
            ->whereRaw('vouchers.is_show_voucher = true AND (vouchers.amount - vouchers.used > 0 OR vouchers.set_limit_amount = false)')
            ->get();

        $request = request();
        $customer = request('customer', $default = null);
        $vouchersRes = [];

        if ($request->customer_id) {
            $cusArray = [
                'id' => intval($request->customer_id),
                'store_id' => $request->store->id
            ];

            $customer = (object)$cusArray;
        }

        foreach ($vouchers  as  $voucherItem) {

            if ($voucherItem->is_use_once == true) {
                $customerVoucher = CustomerVoucher::where('store_id', $request->store->id)
                    ->where('customer_id', $customer ? $customer->id : null)
                    ->where('voucher_id', $voucherItem->id)
                    ->first();

                if ($customerVoucher) continue;
            }

            $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                $request,
                $voucherItem->group_customer,
                $voucherItem->agency_type_id,
                $voucherItem->group_type_id,
                $customer,
                $request->store->id,
                $voucherItem->group_customers,
                $voucherItem->agency_types,
                $voucherItem->group_types
            );

            if ($ok_customer) {
                $voucherItem->products =  $voucherItem->products()->take(10)->get();
                array_push($vouchersRes, $voucherItem);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $vouchersRes,
        ], 200);
    }

    /**
     * Lấy danh sách sản phẩm trong voucher đang phát hành
     * @urlParam  store_code required Store code cần lấy.
     */
    public function getProductVoucherAvailable(Request $request)
    {
        $id = $request->route()->parameter('voucher_id');
        $search = StringUtils::convert_name_lowcase(request('search'));
        $now = Helper::getTimeNowString();
        $voucher = Voucher::where('store_id', $request->store->id)
            ->where('id', $id)
            ->where('is_end', false)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('is_public', true)
            ->where('is_use_once_code_multiple_time', true)
            ->whereRaw('vouchers.is_show_voucher = true AND (vouchers.amount - vouchers.used > 0 OR vouchers.set_limit_amount = false)')
            ->first();

        if ($voucher == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_VOUCHER_EXISTS[0],
                'msg' => MsgCode::NO_VOUCHER_EXISTS[1],
            ], 400);
        }

        $products = $voucher->products()
            ->when($search, function ($query) use ($search) {
                $query->where('name_str_filter', 'LIKE', "%{$search}%");
            })
            ->paginate($request->limit ?: 20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $products,
        ], 200);
    }
}
