<?php

namespace App\Http\Controllers\Api\User\Shipment;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Shipment;
use App\Services\ShipperService;
use Exception;
use Illuminate\Http\Request;

/**
 * @group  User/Đơn vị vận chuyển
 */
class ShipmentController extends Controller
{
    /**
     * Tính phí ship
     * @urlParam  partner_id required id cần sửa
     * money_collection double tiền thu COD
     * length double dài
     * width double rộng 
     * height double cao
     * weight double cân nặng tính bằng gam
     * product_quantity int số lượng sản phẩm
     * receiver_province_id int id tỉnh người nhận
     * receiver_district_id int id quận huyện người nhận
     * receiver_wards_id int id phường xã người nhận
     * receiver_address string địa chỉ chi tiết người nhận
     * sender_province_id int id tỉnh người gửi
     * sender_district_id int id quận huyện người gửi
     * sender_wards_id int id phường xã người gửi
     * sender_address string địa chỉ chi tiết người gửi
     * 
     */
    public function calculate(Request $request)
    {
        $partner_id = $request->route()->parameter('partner_id');

        $datas = config('saha.shipper.list_shipper');

        //Check tồn tại ID
        $listIDShip = [];
        foreach ($datas as $shiper) {
            array_push($listIDShip, $shiper['id']);
        }
        if ($partner_id == null || !in_array($partner_id, $listIDShip)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PARTNER[0],
                'msg' => MsgCode::INVALID_PARTNER[1],
            ], 400);
        }


        $ship_data =  $datas[$partner_id];

        $shipperArr = array(
            'money_collection' => $request->money_collection,
            "store_id" => $request->store->id,
            "from_province_id" =>  $request->sender_province_id,
            "from_district_id" => $request->sender_district_id,
            "from_wards_id" =>  $request->sender_wards_id,
            "to_province_id" => $request->receiver_province_id,
            "to_district_id" => $request->receiver_district_id,
            "to_wards_id" => $request->receiver_wards_id,
            "service_type" => $request->service_type,
            "customer_name" => $request->customer_name,
            "to_address_detail" => $request->to_address_detail,
            "weight" =>  $request->weight,
            "length" => $request->length,
            "width" => $request->width,
            "height" => $request->height,
        );

        $partnerExists = Shipment::where('store_id', $request->store->id)
            ->where('partner_id',  $partner_id)
            ->where('use', true)
            ->whereNotNull('token')
            ->first();

        if ($partnerExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_SHIPPER_SET_YET[0],
                'msg' => MsgCode::NO_SHIPPER_SET_YET[1],
            ], 400);
        }

        $ship_speed = $ship_data['ship_speed'];

        $res  =   ShipperService::caculate_monney_one_partner(
            $shipperArr,
            $partner_id,
            0,
            $partnerExists->token
        );

        $data = [];

        if ($res instanceof Exception) {
            $info = $res->getMessage();

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => $info,
            ], 400);
        } else {
            array_push(
                $data,
                array(
                    "partner_id" => $partner_id,
                    "fee" => $res,
                    "name" => "Tiêu chuẩn",
                    "description" => "Giao thường",
                    "shipper_name" => $ship_data['name'],
                    "image_url" => $ship_data['image_url'],
                    "ship_type" => 0,
                )
            );
        }


        //Thêm giao nhanh
        if ($ship_speed == true) {

            $res  =   ShipperService::caculate_monney_one_partner(
                $shipperArr,
                $partner_id,
                1,
                $partnerExists->token
            );

            if ($res instanceof Exception) {

                $info = $res->getMessage();
            } else {
                array_push(
                    $data,
                    array(
                        "partner_id" => $partner_id,
                        "fee" => $res,
                        "name" => "Siêu tốc",
                        "description" => "Giao nhanh chống",
                        "shipper_name" => $ship_data['name'],
                        "image_url" => $ship_data['image_url'],
                        "ship_type" => 1,
                    )
                );
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data
        ], 200);
    }

    /**
     * Danh cách tất cả đơn vị vận chuyển
     */
    public function getAll(Request $request)
    {
        $datas = config('saha.shipper.list_shipper');
        $listShip = [];

        foreach ($datas as $shiper) {
            $partnerExists = Shipment::where('store_id', $request->store->id)->where('partner_id', $shiper['id'])->first();

            array_push($listShip, [
                'id' => $shiper['id'],
                'name' => $shiper['name'],
                'image_url' => $shiper['image_url'],
                'shipper_config' => $partnerExists
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $listShip,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Cập nhật cấu thông số cho 1 đơn vị vận chuyển
     * @urlParam  partner_id required id cần sửa
     * @bodyParam token string token được cung cấp
     * @bodyParam use boolean Sử dụng hay không
     * @bodyParam cod boolean COD hay không
     */
    public function updateOne(Request $request)
    {
        $partner_id = $request->route()->parameter('partner_id');


        $datas = config('saha.shipper.list_shipper');
        $listIDShip = [];

        foreach ($datas as $shiper) {
            array_push($listIDShip, $shiper['id']);
        }

        if ($partner_id == null || !in_array($partner_id, $listIDShip)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PARTNER[0],
                'msg' => MsgCode::INVALID_PARTNER[1],
            ], 400);
        }

        if ($request->token == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TOKEN_IS_REQUIRED[0],
                'msg' => MsgCode::TOKEN_IS_REQUIRED[1],
            ], 400);
        }

        $resToken = null;
        if (filter_var($request->use, FILTER_VALIDATE_BOOLEAN) == true) {
            $resToken = ShipperService::check_token_partner_id($partner_id, $request->token);
        }


        if ($resToken  instanceof Exception) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TOKEN[0],
                'msg' => MsgCode::INVALID_TOKEN[1],
            ], 400);
        }


        $partnerExists = Shipment::where('store_id', $request->store->id)->where('partner_id', $partner_id)->first();

        if ($partnerExists == null) {

            Shipment::create(
                [
                    'store_id' => $request->store->id,
                    'partner_id' => $partner_id,
                    'token' => $request->token,
                    'use' => filter_var($request->use, FILTER_VALIDATE_BOOLEAN),
                    'cod' => filter_var($request->cod, FILTER_VALIDATE_BOOLEAN),
                ]
            );

            return response()->json([
                'code' => 201,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
            ], 201);
        } else {

            $partnerExists->update(
                [
                    'store_id' => $request->store->id,
                    'partner_id' => $request->partner_id,
                    'token' => $request->token,
                    'cod' => filter_var($request->cod, FILTER_VALIDATE_BOOLEAN),
                    'use' => $request->use !== null ? filter_var($request->use, FILTER_VALIDATE_BOOLEAN) : $partnerExists->use,
                ]
            );

            //Delete invalid partner
            // Shipment::whereNotIn("partner_id", $listIDShip)->delete();

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
            ], 200);
        }
    }
}
