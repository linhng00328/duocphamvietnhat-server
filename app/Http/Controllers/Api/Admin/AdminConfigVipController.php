<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigUserVip;
use App\Models\Employee;
use App\Models\MsgCode;
use App\Models\User;
use App\Models\UserAdvice;
use Illuminate\Http\Request;

/**
 * @group  Admin/UserVip
 */
class AdminConfigVipController extends Controller
{

    /**
     * Bật tắt vip
     * 
     * @bodyParam is_vip boolean vip hay không
     */
    public function on_off_vip(Request $request)
    {

        $id = $request->route()->parameter('user_id');
        $userExists = User::where(
            'id',
            $id
        )->first();

        if ($userExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ], 400);
        }

        $userExists->update([
            'is_vip' => filter_var($request->is_vip, FILTER_VALIDATE_BOOLEAN)
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => User::where(
                'id',
                $id
            )->first()
        ], 200);
    }
    /**
     * Cập nhật cấu hình user vip
     * 
     * @urlParam user_id int user id
     * @bodyParam trader_mark_name string Tên nhãn hiệu
     * @bodyParam url_logo_image string Url logo
     * @bodyParam url_logo_small_image string url logo nhỏ khi thu nhỏ thanh công cụ
     * @bodyParam url_login_image string user login image
     * @bodyParam user_copyright string thương hiệu dưới trang user quản lý
     * @bodyParam customer_copyright string  thương hiệu dưới trang customer
     * @bodyParam url_customer_copyright string đường link trỏ đi của thương hiệu customer
     * @bodyParam list_json_id_theme_vip string list theme vip
     * 
     */
    public function config_user_vip(Request $request)
    {

        $id = $request->route()->parameter('user_id');
        $userExists = User::where(
            'id',
            $id
        )->first();

        if ($userExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ], 400);
        }

     

        $config = ConfigUserVip::where('user_id', $id)->first();
        if ($config  == null) {
            ConfigUserVip::create(
                [

                    'user_id' => $id,
                    'url_logo_image' => $request->url_logo_image,
                    'url_logo_small_image' => $request->url_logo_small_image,
                    'url_login_image' => $request->url_login_image,
                    'user_copyright' => $request->user_copyright,
                    'customer_copyright' => $request->customer_copyright,
                    'url_customer_copyright' => $request->url_customer_copyright,
                    'trader_mark_name' => $request->trader_mark_name,
                    
                    'list_json_id_theme_vip' =>is_array($request->list_id_theme_vip) ? json_encode($request->list_id_theme_vip) : [], // [1,2,3]

                ]
            );
        } else {
            $config->update([
                'user_id' => $id,
                'url_logo_image' => $request->url_logo_image,
                'url_logo_small_image' => $request->url_logo_small_image,
                'url_login_image' => $request->url_login_image,
                'user_copyright' => $request->user_copyright,
                'customer_copyright' => $request->customer_copyright,
                'url_customer_copyright' => $request->url_customer_copyright,
                'trader_mark_name' => $request->trader_mark_name,
                'list_json_id_theme_vip' =>is_array($request->list_id_theme_vip) ? json_encode($request->list_id_theme_vip) : [], // [1,2,3]
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ConfigUserVip::where('user_id', $id)->first()
        ], 200);
    }

    /**
     * Lấy cấu hình hình
     */

    public function get_config_user_vip(Request $request)
    {
        $id = $request->route()->parameter('user_id');
        $userExists = User::where(
            'id',
            $id
        )->first();

        if ($userExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ConfigUserVip::where('user_id', $id)->first()
        ], 200);
    }
}
