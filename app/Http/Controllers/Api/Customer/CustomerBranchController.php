<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\BranchUtils;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MsgCode;
use Illuminate\Http\Request;

class CustomerBranchController extends Controller
{
    /**
     * Danh sách chi nhánh
     * 
     * @urlParam  store_code required Store code
     * @queyParam get_all boolean (Lấy tất cả chi nhánh dùng cho trường hợp chuyển kho)
     */
    public function getAll(Request $request)
    {
        BranchUtils::getBranchDefault($request->store->id);
        $branchs = Branch::where('store_id', $request->store->id)->orderBy('created_at', 'ASC')->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $branchs,
        ], 200);
    }
}
