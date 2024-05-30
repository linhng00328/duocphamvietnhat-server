<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\Place;
use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  Customer/Địa chỉ khách hàng
 */
class CustomerAddressController extends Controller
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
     * @bodyParam is_default bool required Địa chỉ mặc định hay không
     */
    public function create(Request $request, $id)
    {
        $is_default = filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN);

        if (
            filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN) == true
        ) {

            CustomerAddress::where('store_id', $request->store->id)
                ->where('customer_id', $request->customer->id)
                ->update(['is_default' => 0]);
        }

        if (
            $is_default == true
        ) {

            CustomerAddress::where('store_id', $request->store->id)
                ->where(
                    'customer_id',
                    $request->customer->id
                )
                ->update(['is_default' => 0]);
        }

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

        $address = CustomerAddress::create(
            [
                'name' => $request->name,
                'store_id' => $request->store->id,
                'customer_id' => $request->customer->id,
                'address_detail' => $request->address_detail,
                'country' => $request->country,
                'province' => $request->province,
                'district' => $request->district,
                'wards' => $request->wards,
                'village' => $request->village,
                'postcode' => $request->postcode,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_default' =>  filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN) == true ? 1 : 0,
            ]
        );


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CustomerAddress::where('id', $address->id)->first()
        ], 201);
    }


    /**
     * Cập nhật địa chỉ 
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
     * @bodyParam is_default_pickup boolean required Địa chỉ mặc định hay không
     */
    public function update(Request $request, $id)
    {
        $id = $request->route()->parameter('customer_address_id');

        $addressExists = CustomerAddress::where(
            'store_id',
            $request->store->id
        )
            ->where(
                'customer_id',
                $request->customer->id
            )
            ->where('id', $id)->first();

        if (empty($addressExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ADDRESS_EXISTS[0],
                'msg' => MsgCode::NO_ADDRESS_EXISTS[1],
            ], 404);
        }

        $is_default = $request->is_default;

        if ($is_default != null) {
            if (
                $is_default == true
            ) {

                CustomerAddress::where('store_id', $request->store->id)
                    ->where('id', '!=', $addressExists->id)
                    ->where(
                        'customer_id',
                        $request->customer->id
                    )
                    ->update(['is_default' => 0]);
            }
        }



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
                'customer_id' => $request->customer->id,
                'address_detail' => $request->address_detail,
                'country' => $request->country,
                'province' => $request->province,
                'district' => $request->district,
                'wards' => $request->wards,
                'village' => $request->village,
                'postcode' => $request->postcode,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_default' => $is_default == null ? $addressExists->is_default : filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN),
            ]
        );

        $hasDefault = CustomerAddress::where('store_id', $request->store->id)->where('customer_id', $request->customer->id)
            ->where('is_default', true)->first();
        if ($hasDefault  == null) {
            $hasDefault = CustomerAddress::where('store_id', $request->store->id)->where('customer_id', $request->customer->id)->first();
            $hasDefault->update([
                'is_default' => true
            ]);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CustomerAddress::where(
                'store_id',
                $request->store->id
            )->where('id', $addressExists->id)->first()
        ], 200);
    }


    /**
     * Xem tất cả address
     */
    public function getAll(Request $request, $id)
    {
        $address = CustomerAddress::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)
            ->orderBy('created_at', 'desc')->get();

        if (count($address) === 0) {

            $customer = $request->customer;

            if (Place::getNameProvince($customer->province) != null && Place::getNameDistrict($customer->district) != null && Place::getNameWards($customer->wards) != null) {

                $newAddress =  CustomerAddress::create(
                    [
                        'name' => $customer->name,
                        'store_id' => $request->store->id,
                        'customer_id' => $customer->id,
                        'address_detail' => $customer->address_detail,
                        'country' => 1,
                        'province' => $customer->province,
                        'district' => $customer->district,
                        'wards' => $customer->wards,
                        'village' => null,
                        'postcode' => null,
                        'email' => $customer->email,
                        'phone' => $customer->phone_number,
                        'is_default' =>  true,
                    ]
                );

                $address->prepend($newAddress);
            }
        }

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
        $id = $request->route()->parameter('customer_address_id');
        $addressExists = CustomerAddress::where(
            'store_id',
            $request->store->id
        )
            ->where(
                'customer_id',
                $request->customer->id
            )
            ->where('id', $id)->first();

        if (empty($addressExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ADDRESS_EXISTS[0],
                'msg' => MsgCode::NO_ADDRESS_EXISTS[1],
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
