<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Place;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MsgCode;
use App\Models\StoreAddress;
use Illuminate\Http\Request;

/**
 * @group  User/Địa chỉ store
 */
class StoreAddressController extends Controller
{


    /**
     * Thêm địa chỉ cho store
     * @bodyParam name string required họ tên
     * @bodyParam address_detail string Địa chỉ chi tiết
     * @bodyParam country int required id country
     * @bodyParam province int required id province
     * @bodyParam district int required id district
     * @bodyParam village int required id village
     * @bodyParam wards int required id wards
     * @bodyParam postcode string required postcode
     * @bodyParam email string required email
     * @bodyParam phone string required phone
     * @bodyParam is_default_pickup string required Địa chỉ nhận hàng mặc định hay không
     * @bodyParam is_default_return string required Địa chỉ trả hàng
     * @bodyParam branch_id int required branch id
     */
    public function create(Request $request, $id)
    {


        if (Place::getNameProvince($request->province) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PROVINCE[0],
                'msg' => MsgCode::INVALID_PROVINCE[1],
            ], 400);
        }

        if (Place::getNameDistrict($request->district) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DISTRICT[0],
                'msg' => MsgCode::INVALID_DISTRICT[1],
            ], 400);
        }

        if (Place::getNameWards($request->wards) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_WARDS[0],
                'msg' => MsgCode::INVALID_WARDS[1],
            ], 400);
        }


        if (
            filter_var($request->is_default_pickup, FILTER_VALIDATE_BOOLEAN) == true &&
            filter_var($request->is_default_return, FILTER_VALIDATE_BOOLEAN) == true
        ) {

            StoreAddress::where('store_id', $request->store->id)
                ->update(['is_default_pickup' => 0, 'is_default_return' => 0]);
        } else if (filter_var($request->is_default_pickup, FILTER_VALIDATE_BOOLEAN) == true) {

            StoreAddress::where('store_id', $request->store->id)
                ->update(['is_default_pickup' => 0]);
        } else if (filter_var($request->is_default_return, FILTER_VALIDATE_BOOLEAN) == true) {

            StoreAddress::where('store_id', $request->store->id)
                ->update(['is_default_return' => 0]);
        }


        $address = StoreAddress::create(
            [
                'name' => $request->name,
                'store_id' => $request->store->id,
                'address_detail' => $request->address_detail,
                'country' => $request->country,
                'province' => $request->province,
                'district' => $request->district,
                'wards' => $request->wards,
                'village' => $request->village,
                'postcode' => $request->postcode,
                'email' => $request->email,
                'phone' => $request->phone,
                'branch_id' => $request->branch_id,
                'is_default_pickup' =>  filter_var($request->is_default_pickup, FILTER_VALIDATE_BOOLEAN) == true ? 1 : 0,
                'is_default_return' =>  filter_var($request->is_default_return, FILTER_VALIDATE_BOOLEAN) == true ? 1 : 0,
            ]
        );


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => StoreAddress::where('id', $address->id)->first()
        ], 201);
    }


    /**
     * Cập nhật địa chỉ cho store
     * @urlParam  store_address_id required id địa chỉ cần sửa
     * @bodyParam name string required họ tên
     * @bodyParam address_detail string Địa chỉ chi tiết
     * @bodyParam country int required id country
     * @bodyParam province int required id province
     * @bodyParam district int required id district
     * @bodyParam village int required id village
     * @bodyParam wards int required id wards
     * @bodyParam postcode string required postcode
     * @bodyParam email string required email
     * @bodyParam phone string required phone
     * @bodyParam is_default_pickup string required Địa chỉ nhận hàng mặc định hay không
     * @bodyParam is_default_return string required Địa chỉ trả hàng
     * @bodyParam branch_id int required branch id
     */
    public function update(Request $request, $id)
    {
        $id = $request->route()->parameter('store_address_id');

        $addressExists = StoreAddress::where(
            'store_id',
            $request->store->id
        )->where('id', $id)->first();

        if (empty($addressExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_ADDRESS_EXISTS[0],
                'msg' => MsgCode::NO_STORE_ADDRESS_EXISTS[1],
            ], 404);
        }

        $is_default_pickup = filter_var($request->is_default_pickup, FILTER_VALIDATE_BOOLEAN);
        $is_default_return = filter_var($request->is_default_return, FILTER_VALIDATE_BOOLEAN);


        if (Place::getNameProvince($request->province) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PROVINCE[0],
                'msg' => MsgCode::INVALID_PROVINCE[1],
            ], 400);
        }

        if (Place::getNameDistrict($request->district) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DISTRICT[0],
                'msg' => MsgCode::INVALID_DISTRICT[1],
            ], 400);
        }

        if (Place::getNameWards($request->wards) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_WARDS[0],
                'msg' => MsgCode::INVALID_WARDS[1],
            ], 400);
        }

        $addressExists->update(
            [
                'name' => $request->name,
                'store_id' => $request->store->id,
                'address_detail' => $request->address_detail,
                'country' => $request->country,
                'province' => $request->province,
                'district' => $request->district,
                'wards' => $request->wards,
                'village' => $request->village,
                'postcode' => $request->postcode,
                'email' => $request->email,
                'phone' => $request->phone,
                'branch_id' => $request->branch_id,
                'is_default_pickup' => $is_default_pickup == true ? 1 : 0,
                'is_default_return' => $is_default_return == true ? 1 : 0,
            ]
        );


        if (
            filter_var($addressExists->is_default_pickup, FILTER_VALIDATE_BOOLEAN) == true &&
            filter_var($addressExists->is_default_return, FILTER_VALIDATE_BOOLEAN) == true
        ) {

            StoreAddress::where('store_id', $request->store->id)->where('id', '!=', $addressExists->id)
            ->when($request->branch_id != null, function ($query)  use ($request) {
                $branchDefault = Branch::where('store_id', $request->store->id)->where('id', $request->branch_id)->first();
                if ($branchDefault->is_default_order_online == true) {
                    $query->where(function ($query) use ($request) {
                        $query->where('branch_id', '=', null)
                            ->orWhere('branch_id', '=',  $request->branch_id);
                    });
                } else {
                    $query->where('branch_id', $request->branch_id);
                }
            })
                ->update(['is_default_pickup' => 0, 'is_default_return' => 0]);
        } else if (filter_var($addressExists->is_default_pickup, FILTER_VALIDATE_BOOLEAN) == true) {

            StoreAddress::where('store_id', $request->store->id)->where('id', '!=', $addressExists->id)
            ->when($request->branch_id != null, function ($query)  use ($request) {
                $branchDefault = Branch::where('store_id', $request->store->id)->where('id', $request->branch_id)->first();
                if ($branchDefault->is_default_order_online == true) {
                    $query->where(function ($query) use ($request) {
                        $query->where('branch_id', '=', null)
                            ->orWhere('branch_id', '=',  $request->branch_id);
                    });
                } else {
                    $query->where('branch_id', $request->branch_id);
                }
            })
                ->update(['is_default_pickup' => 0]);
        } else if (filter_var($addressExists->is_default_return, FILTER_VALIDATE_BOOLEAN) == true) {

            StoreAddress::where('store_id', $request->store->id)->where('id', '!=', $addressExists->id)
            ->when($request->branch_id != null, function ($query)  use ($request) {
                $branchDefault = Branch::where('store_id', $request->store->id)->where('id', $request->branch_id)->first();
                if ($branchDefault->is_default_order_online == true) {
                    $query->where(function ($query) use ($request) {
                        $query->where('branch_id', '=', null)
                            ->orWhere('branch_id', '=',  $request->branch_id);
                    });
                } else {
                    $query->where('branch_id', $request->branch_id);
                }
            })
                ->update(['is_default_return' => 0]);
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => StoreAddress::where(
                'store_id',
                $request->store->id
            )->where('id', $addressExists->id)->first()
        ], 200);
    }


    /**
     * Xem tất cả store address
     */
    public function getAll(Request $request, $id)
    {

        $address = StoreAddress::where('store_id', $request->store->id)
            ->when(request('branch_id') != null, function ($query)  use ($request) {
              
                $branchDefault = Branch::where('store_id', $request->store->id)->where('id', request('branch_id'))->first();
          
                if ($branchDefault->is_default_order_online == true) {
                    $query->where(function ($query) {
                        $query->where('branch_id', '=', null)
                            ->orWhere('branch_id', '=',  request('branch_id'));
                    });
                } else {
                    $query->where('branch_id', request('branch_id'));
                }
            })

            ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $address,
        ], 200);
    }


    /**
     * xóa một địa chỉ
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID địa chỉ cần xóa
     */
    public function deleteOneStoreAddress(Request $request)
    {
        $id = $request->route()->parameter('store_address_id');
        $addressExists = StoreAddress::where(
            'store_id',
            $request->store->id
        )->where('id', $id)->first();

        if (empty($addressExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_ADDRESS_EXISTS[0],
                'msg' => MsgCode::NO_STORE_ADDRESS_EXISTS[1],
            ], 404);
        }


        $addressExists->delete();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $addressExists->id],
        ], 200);
    }
}
