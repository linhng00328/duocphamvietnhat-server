<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\AttributeSearch;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  Customer/AppWebTheme
 */
class CustomerAttributeSearchController extends Controller
{

    /**
     * Danh sách thuộc tính tìm kiếm
     * @urlParam  store_code required Store code
     */
    public function getAll(Request $request)
    {

        $categories = AttributeSearch::where('store_id', $request->store->id)
            ->orderBy('position', 'ASC')->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categories,
        ], 200);
    }
}
