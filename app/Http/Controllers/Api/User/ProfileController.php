<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  Ussr/Thông tin cá nhân
 */
class ProfileController extends Controller
{

    /**
     * Tạo Lấy thông tin profile
     */
    public function getProfile(Request $request)
    {
        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $request->user ?? $request->staff,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Cập nhật thông tin profile
     * @bodyParam name String   Họ và tên
     * @bodyParam date_of_birth Date   Ngày sinh
     * @bodyParam avatar_image String  Link ảnh avater
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nam - 2 Nữ
     */
    public function updateProfile(Request $request)
    {

        if ($request->staff != null) {
            $request->staff->update([
                "name" =>   $request->name,
                "date_of_birth" =>   $request->date_of_birth,
                "avatar_image" =>   $request->avatar_image,
                "sex" =>   $request->sex,
            ]);
        } else {
            $request->user->update([
                "name" =>   $request->name,
                "date_of_birth" =>   $request->date_of_birth,
                "avatar_image" =>   $request->avatar_image,
                "sex" =>   $request->sex,
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $request->user,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
