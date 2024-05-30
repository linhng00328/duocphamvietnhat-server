<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\PhoneUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Danh cách nhân viên
     */
    public function getAll(Request $request)
    {
        $branch_id = request("branch_id");
        $is_sale = request("is_sale");

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => Staff::where('store_id', $request->store->id)
                ->when($branch_id != null, function ($query) use ($branch_id) {
                    $query->where('branch_id', $branch_id);
                })
                ->when($is_sale !== null, function ($query) use ($is_sale) {
                    $query->where('is_sale', filter_var($is_sale, FILTER_VALIDATE_BOOLEAN));
                })
                ->orderBy('created_at', 'desc')
                ->get(),
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
     * @bodyParam salary_one_hour string Lương theo giờ
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
     * @bodyParam id_decentralization int id phân quyền (ủy quyền cho nhân viên)  ( 0 là admin, 1 là sale , 2 là marketing)
     * @bodyParam branch_id int id chi nhánh
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

        // if ($request->branch_id == null) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::NO_BRANCH_EXISTS[0],
        //         'msg' => MsgCode::NO_BRANCH_EXISTS[1],
        //     ], 400);
        // }

        if ($request->password == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PASSWORD_IS_REQUIRED[0],
                'msg' => MsgCode::PASSWORD_IS_REQUIRED[1],
            ], 400);
        }

        $start_with = $request->store->store_code . "_";
        $length_start = strlen($start_with);


        if (substr($request->username, 0, $length_start) !=    $start_with) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::START_WITH[0],
                'msg' => MsgCode::START_WITH[1] . " " . $start_with,
            ], 400);
        }

        if (strlen($request->username) ==   $length_start) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_USERNAME[0],
                'msg' => MsgCode::INVALID_USERNAME[1],
            ], 400);
        }

        if (Staff::where('username', $request->username)->exists()) {
            return response()->json([
                'code' => 409,
                'success' => false,
                'msg_code' => MsgCode::USERNAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::USERNAME_ALREADY_EXISTS[1],
            ], 409);
        }

        $phone = PhoneUtils::convert($request->phone_number);
        $validPhone = PhoneUtils::check_valid($phone);

        if ($validPhone == false) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ], 400);
        }

        $staffExists = Staff::where('store_id', $request->store->id)
            ->where('phone_number', $phone)->first();

        if ($staffExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 400);
        }

        $staff_created = Staff::create([
            'area_code' => '+84',
            'username' => $request->username,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'name' => $request->name,
            'store_id' => $request->store->id,
            'salary' => $request->salary,
            'address' => $request->address,
            'sex' => $request->sex,
            'id_decentralization' => $request->id_decentralization,
            'branch_id' => $request->branch_id,
            'salary_one_hour' => $request->salary_one_hour
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Staff::where('id', '=',   $staff_created->id)->first(),
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
     * @bodyParam salary_one_hour string Lương theo giờ
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
     * @bodyParam id_decentralization int id phân quyền (ủy quyền cho nhân viên)
     * @bodyParam branch_id int id chi nhánh
     */
    public function update(Request $request)
    {

        $staff_id = request("staff_id");
        $staffExists = Staff::where(
            'id',
            $staff_id
        )
            ->where(
                'store_id',
                $request->store->id
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


        $start_with = $request->store->store_code . "_";
        $length_start = strlen($start_with);


        if (substr($request->username, 0, $length_start) !=    $start_with) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::START_WITH[0],
                'msg' => MsgCode::START_WITH[1] . " " . $start_with,
            ], 400);
        }

        if (strlen($request->username) ==   $length_start) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_USERNAME[0],
                'msg' => MsgCode::INVALID_USERNAME[1],
            ], 400);
        }

        if (Staff::where('username', $request->username)
            ->where('id', '<>', $staff_id)->exists()
        ) {
            return response()->json([
                'code' => 409,
                'success' => false,
                'msg_code' => MsgCode::USERNAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::USERNAME_ALREADY_EXISTS[1],
            ], 409);
        }

        $phone = PhoneUtils::convert($request->phone_number);
        $validPhone = PhoneUtils::check_valid($phone);

        if ($validPhone == false) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ], 400);
        }

        $checkStaffExists = Staff::where('store_id', $request->store->id)
            ->where('id', '!=',  $staff_id)
            ->where('phone_number', $phone)
            ->first();

        if ($checkStaffExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 400);
        }


        $staffExists->update(Helper::sahaRemoveItemArrayIfNullValue(
            [
                'area_code' => '+84',
                'username' => $request->username,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => $request->password == null ? null : bcrypt($request->password),
                'name' => $request->name,
                'store_id' => $request->store->id,
                'salary' => $request->salary,
                'address' => $request->address,
                'sex' => $request->sex,
                'id_decentralization' => $request->id_decentralization,
                'branch_id' => $request->branch_id,
                'salary_one_hour' => $request->salary_one_hour
            ]
        ));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Staff::where('id', '=',   $staffExists->id)->first(),
        ], 200);
    }

    /**
     * Cấp quyền sale
     * 
     * @bodyParam is_sale boolean phải sale không
     */
    public function updateSale(Request $request)
    {

        $staff_id = request("staff_id");
        $staffExists = Staff::where(
            'id',
            $staff_id
        )
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($staffExists == null) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 400);
        }


        $staffExists->update(Helper::sahaRemoveItemArrayIfNullValue(
            [
                'is_sale' =>    filter_var($request->is_sale, FILTER_VALIDATE_BOOLEAN),
            ]
        ));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Staff::where('id', '=',   $staffExists->id)->first(),
        ], 200);
    }

    /**
     * Xóa 1 nhân viên
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function delete(Request $request)
    {

        $staff_id = request("staff_id");
        $staffExists = Staff::where(
            'id',
            $staff_id
        )
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($staffExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 404);
        }

        $idDeleted = $staffExists->id;
        $staffExists->delete();



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }
}
