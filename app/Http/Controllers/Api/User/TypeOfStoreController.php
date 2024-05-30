<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  Kiểu cửa hàng
 */

class TypeOfStoreController extends Controller
{
  
    /**
	* Danh sách kiểu cửa hàng
	*/
    public function getAll() {
        $types = config('saha.type_store.type_store');
        
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' =>MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $types,
        ],200);
    }}

