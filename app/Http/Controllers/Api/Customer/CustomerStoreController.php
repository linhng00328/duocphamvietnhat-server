<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  Customer/Thông tin store
 */
class CustomerStoreController extends Controller
{

    /**
	* Lấy thông tin store
    * @urlParam  store_code required Store code cần lấy.
	*/
    public function getOneStore(Request $request, $id)
    {
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $request->store,
        ], 200);

    }
}
