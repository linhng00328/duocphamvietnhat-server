<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\ChangeBalanceSale;
use App\Models\SaleCustomer;
use App\Models\MsgCode;

use Illuminate\Http\Request;

/**
 * @group  Customer/Lịch sử thay đổi số dư
 */
class CustomerChangeBalanceSaleController extends Controller
{

    /**
     * Lịch sử thay đổi số dư
     * @urlParam  store_code required Store code cần lấy.
     */
    public function getAll(Request $request)
    {

        $sale_customer  = SaleCustomer::where('store_id',$request->store->id)->where('customer_id', $request->customer->id)->first();
    
        $histories = ChangeBalanceSale::
        where('store_id', $request->store->id)
        ->where('sale_customer_id',  $sale_customer->id)
        ->orderBy('created_at', 'desc')
        ->paginate(20);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>    $histories ,
        ], 200);
    }
}
