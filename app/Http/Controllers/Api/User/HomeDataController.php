<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\AdminBanner;
use App\Models\MsgCode;
use Illuminate\Http\Request;


/**
 * @group  User/HomeApp
 */
class HomeDataController extends Controller
{
    /**
     * Lấy giao diện home
     */
    public function getHomeApp(Request $request)
    {

     
        $homeData = [
            "banner" => AdminBanner::orderBy('created_at', 'desc')->get()
        ];

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $homeData,
        ], 200);
    }
}
