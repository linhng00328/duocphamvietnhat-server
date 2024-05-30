<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\BranchUtils;
use App\Helper\Place;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MsgCode;
use App\Models\Staff;
use Illuminate\Http\Request;

/**
 * @group  User/Chi nhánh
 */

class BranchController extends Controller
{

    /**
     * Danh sách chi nhánh
     * 
     * @urlParam  store_code required Store code
     * @queyParam get_all boolean (Lấy tất cả chi nhánh dùng cho trường hợp chuyển kho)
     */
    public function getAll(Request $request)
    {

        $get_all = filter_var(request('get_all'), FILTER_VALIDATE_BOOLEAN); //

        $branchs =  [];

        BranchUtils::getBranchDefault($request->store->id);

        if ($get_all  == true) {
            $branchs = Branch::where('store_id', $request->store->id)->orderBy('created_at', 'ASC')->get();
        } else {
            if ($request->staff != null) {
                $branchs = Branch::where('store_id', $request->store->id)
                    ->whereIn('id', [$request->staff->branch_id])
                    ->orderBy('created_at', 'ASC')->get();
            }

            if ($request->user != null) {
                $branchs = Branch::where('store_id', $request->store->id)->orderBy('created_at', 'ASC')->get();
            }
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $branchs,
        ], 200);
    }

    /**
     * Tạo chi nhánh mới
     * @urlParam  store_code required Store code
     * @bodyParam name string Tên chi nhánh
     * @bodyParam phone string Số điện thoại
     * @bodyParam email string Email chi nhánh
     * @bodyParam branch_code string Mã chi nhánh
     * @bodyParam province int required id province
     * @bodyParam district int required id district
     * @bodyParam wards int required id wards
     * @bodyParam address_detail Địa chỉ chi tiết
     * @bodyParam postcode string Mã bưu điện
     * @bodyParam is_default bool is_default
     * @bodyParam is_default_order_online bool Chi nhánh mặc định nhận đơn hàng online
     */
    public function create(Request $request)
    {

        if ($request->name == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $branchNameExists = Branch::where('name', $request->name)->where('store_id', $request->store->id)->first();

        if ($branchNameExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }

        if (
            filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN) == true
        ) {

            Branch::where('store_id', $request->store->id)
                ->update(['is_default' => 0]);
        }

        $is_default = false;

        $banches = Branch::where('store_id', $request->store->id)->get();

        if (count($banches) == 0) {
            $is_default = true;
        } else {
            $is_default =     filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN);
        }
        $is_default_order_online =     filter_var($request->is_default_order_online, FILTER_VALIDATE_BOOLEAN);

        if ($is_default_order_online  == true) {

            $banches = Branch::where('store_id', $request->store->id)->update(
                [
                    'is_default_order_online' => false
                ]
            );
        }

        $data = [
            'store_id' => $request->store->id,
            'name' => $request->name,

            'address_detail' => $request->address_detail,
            'branch_code' => $request->branch_code,
            'province' => $request->province,
            'district' => $request->district,
            'wards' => $request->wards,

            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),

            'postcode' => $request->postcode,
            'email' => $request->email,
            'phone' => $request->phone,

            'txt_code' => $request->txt_code,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'bank' => $request->bank,

            'is_default' =>  $is_default,
            'is_default_order_online' =>  $is_default_order_online,
        ];


        $branchCreated = Branch::create($data);

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Branch::where('id', $branchCreated->id)
                ->first()
        ], 201);
    }

    /**
     * Cập nhật chi nhánh
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required ID chi nhánh
     * @bodyParam name string Tên chi nhánh
     * @bodyParam phone string Số điện thoại
     * @bodyParam email string Email chi nhánh
     * @bodyParam branch_code string Mã chi nhánh
     * @bodyParam province int required id province
     * @bodyParam district int required id district
     * @bodyParam wards int required id wards
     * @bodyParam address_detail Địa chỉ chi tiết
     * @bodyParam postcode string Mã bưu điện
     * @bodyParam is_default bool is_default
     * @bodyParam is_default_order_online bool Chi nhánh mặc định nhận đơn hàng online
     */
    public function update(Request $request)
    {

        $branch_id = $request->route()->parameter('branch_id');

        $branchExists = Branch::where('store_id', $request->store->id)
            ->where('id', $branch_id)
            ->first();

        if ($branchExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_BRANCH_EXISTS[0],
                'msg' => MsgCode::NO_BRANCH_EXISTS[1],
            ], 400);
        }

        if ($request->name == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $branchNameExists = Branch::where('name', $request->name)->where('store_id', $request->store->id)->where('id', '!=', $branch_id)->first();

        if ($branchNameExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }

        if (
            filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN) == true
        ) {

            Branch::where('store_id', $request->store->id)
                ->update(['is_default' => 0]);
        }


        $banches = Branch::where('store_id', $request->store->id)->get();
        if (count($banches) == 1) {
            $is_default = true;
        } else {
            $is_default =     filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN);
        }

        $is_default_order_online =     filter_var($request->is_default_order_online, FILTER_VALIDATE_BOOLEAN);

        if ($is_default_order_online  == true) {

            $banches = Branch::where('store_id', $request->store->id)->where('id', '!=', $branch_id)->update(
                [
                    'is_default_order_online' => false
                ]
            );
        }

        $data = [
            'store_id' => $request->store->id,
            'name' => $request->name,

            'address_detail' => $request->address_detail,


            'branch_code' => $request->branch_code,
            'province' => $request->province,
            'district' => $request->district,
            'wards' => $request->wards,

            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),

            'postcode' => $request->postcode,
            'email' => $request->email,
            'phone' => $request->phone,

            'is_default' =>   $is_default,
            'is_default_order_online' =>  $is_default_order_online,

            'txt_code' => $request->txt_code,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'bank' => $request->bank,
        ];


        $branchExists->update($data);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Branch::where('id', $branchExists->id)
                ->first()
        ], 200);
    }


    /**
     * Xóa chi nhánh
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required ID chi nhánh
     */
    public function delete(Request $request)
    {

        $branch_id = $request->route()->parameter('branch_id');

        $branchExists = Branch::where('store_id', $request->store->id)
            ->where('id', $branch_id)
            ->first();

        if ($branchExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_BRANCH_EXISTS[0],
                'msg' => MsgCode::NO_BRANCH_EXISTS[1],
            ], 400);
        }

        if ($branchExists->is_default_order_online == true) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::BRANCHES_RECEIVING_ONLINE_ORDERS_CANNOT_BE_DELETED[0],
                'msg' => MsgCode::BRANCHES_RECEIVING_ONLINE_ORDERS_CANNOT_BE_DELETED[1],
            ], 400);
        }

        $idDeleted = $branchExists->id;

        Staff::where('branch_id', $branch_id)->delete();
        $branchExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }
}
