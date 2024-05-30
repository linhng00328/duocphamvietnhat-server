<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  Admin/Thông tin cá nhân
 */
class AdminProfileController extends Controller
{

    /**
     * Tạo Lấy thông tin profile
     */
    public function getProfile(Request $request)
    {
        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $request->admin ?? $request->employee,
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

        if ($request->employee != null) {
            $request->employee->update([
                "name" =>   $request->name,
                "date_of_birth" =>   $request->date_of_birth,
                "avatar_image" =>   $request->avatar_image,
                "sex" =>   $request->sex,
            ]);
        } else {
            $request->admin->update([
                "name" =>   $request->name,
                "date_of_birth" =>   $request->date_of_birth,
                "avatar_image" =>   $request->avatar_image,
                "sex" =>   $request->sex,
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $request->admin ?? $request->employee,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
