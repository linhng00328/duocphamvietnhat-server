<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Place;
use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MsgCode;
use App\Models\Supplier;
use Illuminate\Http\Request;

/**
 * @group  User/Nhà cung cấp
 */

class SupplierController extends Controller
{

    /**
     * Danh sách nhà cung cấp
     * @urlParam  store_code required Store code
     * @queryParam name string Tên nhà cung cấp
     * @queryParam is_debt Có nợ không
     * @queyParam search string chuoi can tim
     * 
     */
    public function getAll(Request $request)
    {
        $search = StringUtils::convert_name_lowcase(request('search'));

        $suppliers = Supplier::where('store_id', $request->store->id)
            ->when(empty($search), function ($query) {
                $query->orderBy('created_at', 'DESC');
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name_str_filter', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                })->orderBy('name_str_filter', 'ASC');
            })

            ->paginate(
                request('limit') == null ? 20 : request('limit')
            );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $suppliers,
        ], 200);
    }

    /**
     * Tạo  nhà cung cấp mới
     * @urlParam  store_code required Store code
     * @bodyParam name string Tên nhà cung cấp
     * @bodyParam phone string Số điện thoại
     * @bodyParam email string Email nhà cung cấp
     * @bodyParam branch_code string Mã nhà cung cấp
     * @bodyParam province int required id province
     * @bodyParam district int required id district
     * @bodyParam wards int required id wards
     * @bodyParam address_detail Địa chỉ chi tiết
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

        $supplierNameExists = Supplier::where('name', $request->name)->where('store_id', $request->store->id)->first();

        if ($supplierNameExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }



        $supplierPhoneExists = Supplier::where('store_id', $request->store->id)
            ->where('phone', $request->phone)->first();

        if ($supplierPhoneExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 400);
        }


        $data = [
            'store_id' => $request->store->id,
            'name' => $request->name,
            'name_str_filter' => StringUtils::convert_name_lowcase($request->name),

            'address_detail' => $request->address_detail,
            'province' => $request->province,
            'district' => $request->district,
            'wards' => $request->wards,

            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),
            'email' => $request->email,
            'phone' => $request->phone,
        ];


        $supplierCreated = Supplier::create($data);

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Supplier::where('id', $supplierCreated->id)
                ->first()
        ], 201);
    }

    /**
     * Cập nhật nhà cung cấp
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required ID nhà cung cấp
     * @bodyParam name string Tên nhà cung cấp
     * @bodyParam phone string Số điện thoại
     * @bodyParam email string Email nhà cung cấp
     * @bodyParam province int required id province
     * @bodyParam district int required id district
     * @bodyParam wards int required id wards
     * @bodyParam address_detail Địa chỉ chi tiết
     */
    public function update(Request $request)
    {

        $supplier_id = $request->route()->parameter('supplier_id');

        $supplierExists = Supplier::where('store_id', $request->store->id)
            ->where('id', $supplier_id)
            ->first();

        if ($supplierExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_SUPPLIER_EXISTS[0],
                'msg' => MsgCode::NO_SUPPLIER_EXISTS[1],
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

        $supplierNameExists = Supplier::where('name', $request->name)->where('store_id', $request->store->id)->where('id', '!=', $supplier_id)->first();

        if ($supplierNameExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }


        $supplierPhoneExists = Supplier::where('store_id', $request->store->id)
            ->where('id', '!=',  $supplier_id)
            ->where('phone', $request->phone)->first();

        if ($supplierPhoneExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 400);
        }

        $data = [
            'store_id' => $request->store->id,
            'name' => $request->name,
            'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
            'address_detail' => $request->address_detail,

            'province' => $request->province,
            'district' => $request->district,
            'wards' => $request->wards,

            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),

            'email' => $request->email,
            'phone' => $request->phone,
        ];


        $supplierExists->update($data);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Supplier::where('id', $supplierExists->id)
                ->first()
        ], 200);
    }


    /**
     * Xóa nhà cung cấp
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required ID nhà cung cấp
     */
    public function delete(Request $request)
    {

        $supplier_id = $request->route()->parameter('supplier_id');

        $supplierExists = Supplier::where('store_id', $request->store->id)
            ->where('id', $supplier_id)
            ->first();

        if ($supplierExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_SUPPLIER_EXISTS[0],
                'msg' => MsgCode::NO_SUPPLIER_EXISTS[1],
            ], 400);
        }

        $idDeleted = $supplierExists->id;

        $supplierExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }

    /**
     * Xem 1 nhà cung cấp
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required ID nhà cung cấp
     */
    public function getOne(Request $request)
    {

        $supplier_id = $request->route()->parameter('supplier_id');

        $supplierExists = Supplier::where('store_id', $request->store->id)
            ->where('id', $supplier_id)
            ->first();

        if ($supplierExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_SUPPLIER_EXISTS[0],
                'msg' => MsgCode::NO_SUPPLIER_EXISTS[1],
            ], 400);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $supplierExists,
        ], 200);
    }
}
