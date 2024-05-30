<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\MsgCode;
use Illuminate\Http\Request;


/**
 * @group  Admin/Nhân viên 
 */
class AdminEmployeeController extends Controller
{
    /**
     * Danh cách nhân viên
     */
    public function getAll(Request $request)
    {

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => Employee::get(),
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Thêm 1 nhân viên
     * @bodyParam username string Tên tài khoản
     * @bodyParam phone_number string Số điện thoại
     * @bodyParam email string Email
     * @bodyParam name string Tên đầy đủ
     * @bodyParam salary string Lương
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
     * @bodyParam id_decentralization int 1 NV Sale,  0 QL SALE
     */
    public function create(Request $request)
    {

        if ($request->username == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::USERNAME_IS_REQUIRED[0],
                'msg' => MsgCode::USERNAME_IS_REQUIRED[1],
            ], 400);
        }

        if ($request->password == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PASSWORD_IS_REQUIRED[0],
                'msg' => MsgCode::PASSWORD_IS_REQUIRED[1],
            ], 400);
        }


        if (Employee::where('username', $request->username)->exists()) {
            return response()->json([
                'code' => 409,
                'success' => false,
                'msg_code' => MsgCode::USERNAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::USERNAME_ALREADY_EXISTS[1],
            ], 409);
        }


        $employee_created = Employee::create([
            'area_code' => '+84',
            'username' => $request->username,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'avatar_image' => $request->avatar_image,
            'password' => bcrypt($request->password),
            'name' => $request->name,
            'salary' => $request->salary,
            'address' => $request->address,
            'sex' => $request->sex,
            'id_decentralization' => $request->id_decentralization
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Employee::where('id', '=',   $employee_created->id)->first(),
        ], 200);
    }


    /**
     * Cập nhật thông tin nhân viên
     * 
     * @bodyParam username string Tên tài khoản
     * @bodyParam phone_number string Số điện thoại
     * @bodyParam email string Email
     * @bodyParam name string Tên đầy đủ
     * @bodyParam salary string Lương
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
     * @bodyParam id_decentralization int 1 NV Sale,  0 QL SALE
     */
    public function update(Request $request)
    {

        $employee_id = request("employee_id");
        $employeeExists = Employee::where(
            'id',
            $employee_id
        )
            ->first();

        if ($request->username == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::USERNAME_IS_REQUIRED[0],
                'msg' => MsgCode::USERNAME_IS_REQUIRED[1],
            ], 400);
        }



        if (Employee::where('username', $request->username)
            ->where('id', '<>', $employee_id)->exists()
        ) {
            return response()->json([
                'code' => 409,
                'success' => false,
                'msg_code' => MsgCode::USERNAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::USERNAME_ALREADY_EXISTS[1],
            ], 409);
        }


        $employeeExists->update(Helper::sahaRemoveItemArrayIfNullValue(
            [
                'area_code' => '+84',
                'username' => $request->username,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' =>$request->password == null ? null : bcrypt($request->password),
                'name' => $request->name,
                'avatar_image' => $request->avatar_image,
                'salary' => $request->salary,
                'address' => $request->address,
                'sex' => $request->sex,
                'id_decentralization' => $request->id_decentralization
            ]
        ));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Employee::where('id', '=',   $employeeExists->id)->first(),
        ], 200);
    }

    /**
     * Xóa 1 nhân viên
     */
    public function delete(Request $request)
    {

        $employee_id = request("employee_id");
        $employeeExists = Employee::where(
            'id',
            $employee_id
        )
            ->first();

        if ($employeeExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 404);
        }

        $idDeleted = $employeeExists->id;
        $employeeExists->delete();



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }
}
