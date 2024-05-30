<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\ChangeBalanceAgency;
use App\Models\Agency;
use App\Models\MsgCode;

use Illuminate\Http\Request;

/**
 * @group  Customer/Lịch sử thay đổi số dư
 */
class CustomerChangeBalanceAgenciesController extends Controller
{

    /**
     * Lịch sử thay đổi số dư
     * @urlParam  store_code required Store code cần lấy.
     */
    public function getAll(Request $request)
    {

        $agency  = Agency::where('store_id', $request->store->id)->where('customer_id', $request->customer->id)->first();

        $histories = ChangeBalanceAgency::where('store_id', $request->store->id)
            ->where('agency_id',  $agency->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>    $histories,
        ], 200);
    }
}
