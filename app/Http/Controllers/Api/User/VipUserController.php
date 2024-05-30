<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\ConfigUserVip;
use App\Models\Discount;
use App\Models\MsgCode;
use App\Models\NotificationUser;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ProductReviews;
use App\Models\RoomChat;
use App\Models\Voucher;
use Illuminate\Support\Facades\Schema;

/**
 * @group  User/Vip user
 */
class VipUserController extends Controller
{
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
     * 
     */
    public function config_user_vip(Request $request)
    {

        $config = ConfigUserVip::where('user_id', $request->user->id)->first();
        if ($config  == null) {
            ConfigUserVip::create(
                [
                    'user_id' => $request->user->id,
                    'url_logo_image' => $request->url_logo_image,
                    'url_logo_small_image' => $request->url_logo_small_image,
                    'url_login_image' => $request->url_login_image,
                    'user_copyright' => $request->user_copyright,
                    'customer_copyright' => $request->customer_copyright,
                    'url_customer_copyright' => $request->url_customer_copyright,
                    'trader_mark_name' => $request->trader_mark_name,

                ]
            );
        } else {
            $config->update([
                'url_logo_image' => $request->url_logo_image,
                'url_logo_small_image' => $request->url_logo_small_image,
                'url_login_image' => $request->url_login_image,
                'user_copyright' => $request->user_copyright,
                'customer_copyright' => $request->customer_copyright,
                'url_customer_copyright' => $request->url_customer_copyright,
                'trader_mark_name' => $request->trader_mark_name,
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ConfigUserVip::where('user_id', $request->user->id)->first()
        ], 200);
    }

    /**
     * Lấy cấu hình hình
     */

    public function get_config_user_vip(Request $request)
    {

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ConfigUserVip::where('user_id', $request->user->id)->first()
        ], 200);
    }
}
