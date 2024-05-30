<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\User\ConfigShipController;
use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\MsgCode;
use App\Models\Shipment;
use App\Models\StoreAddress;
use App\Services\ShipperService;
use Illuminate\Http\Request;

/**
 * @group  Customer/Vận chuyển
 */
class CustomerShipperController extends Controller
{


    /**
     * Danh sách nhà vận chuyển
     * @bodyParam id_address_customer integer required Id địa chỉ giao hàng
     */
    public function list_shipper(Request $request)
    {

        $config = ConfigShipController::defaultDataConfigShip($request->store->id);
        if (is_array($config)) {
            $config = json_decode(json_encode($config), false);
        }
        if ($config == null || $config->is_calculate_ship == false) {
            $data = [
                'info' => "Không tính phí ship",
                'data' => array()
            ];

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $data,
            ], 200);
        } else {

            if ($config->use_fee_from_partnership == false &&  $config->use_fee_from_default == false) {

                $data = [
                    'info' => "Không tính phí ship",
                    'data' => [
                        [
                            "partner_id" =>  -1,
                            "name" => "Phí giao hàng mặc định",
                        ]
                    ]
                ];

                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                    'data' => $data,
                ], 200);
            } else {


                $addressPickupExists = StoreAddress::where(
                    'store_id',
                    $request->store->id
                )->where('is_default_pickup', true)->first();

                $list_shipper = array();

                if (empty($addressPickupExists)) {
                    $data = [
                        'info' => "Shop chưa cài đặt địa chỉ nhận hàng",
                        'data' =>   $list_shipper
                    ];
                } else {


                    if ($config->use_fee_from_default == true) {
                        array_push($list_shipper,  [
                            "partner_id" =>  -1,
                            "name" => "Phí giao hàng mặc định",
                        ]);
                    }


                    if ($config->use_fee_from_partnership == true) {


                        $datas = config('saha.shipper.list_shipper');
                        $listShip = [];

                        foreach ($datas as $shiperInFile) {
                            $partnerExists = Shipment::where('store_id', $request->store->id)
                                ->where('partner_id', $shiperInFile['id'])
                                ->where('use', true)
                                ->whereNotNull('token')
                                ->first();

                            if ($partnerExists != null) {

                                array_push($list_shipper, [
                                    'partner_id' => $shiperInFile['id'],
                                    'logo' => $shiperInFile['image_url'],
                                    'name' => $shiperInFile['name'],
                                ]);
                            }
                        }
                    }
                }


                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'data' => $list_shipper,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                ], 200);
            }
        }
    }
    /**
     * Tính phí vận chuyển
     * @bodyParam id_address_customer integer required Id địa chỉ giao hàng
     */
    public function caculate_fee(Request $request)
    {
        $addressCustomerExists = CustomerAddress::where(
            'store_id',
            $request->store->id
        )
            ->where(
                'customer_id',
                $request->customer->id
            )
            ->where('id',  $request->id_address_customer)->first();

        if (empty($addressCustomerExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ADDRESS_EXISTS[0],
                'msg' => MsgCode::NO_ADDRESS_EXISTS[1],
            ], 404);
        }

        // * @bodyParam length integer required Chiều dài của gói hàng, đơn vị sử dụng cm
        // * @bodyParam width integer required Chiều rộng của gói hàng, đơn vị sử dụng cm
        // * @bodyParam height integer required Chiều cao của gói hàng, đơn vị sử dụng cm


        $config = ConfigShipController::defaultDataConfigShip($request->store->id);


        if ($config == null) {

            $data = [
                'info' => "Không tính phí ship",
                'data' => []
            ];

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $data,
            ], 200);
        }

        if (is_array($config)) {
            $config = json_decode(json_encode($config), false);
        }

        if ($config == null || $config->is_calculate_ship == false) {


            $data = [
                'info' => "Không tính phí ship",
                'data' => []
            ];

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $data,
            ], 200);
        } else {

            if ($config->use_fee_from_partnership == false) {
                $data = [
                    'info' => "Không tính phí ship",
                    'data' => [
                        [
                            "partner_id" =>  -1,
                            "fee" => in_array(
                                $addressCustomerExists->province,
                                $config->urban_list_id_province
                            ) ? $config->fee_urban :   $config->fee_suburban,
                            "name" => "Phí giao hàng mặc định",
                            "ship_type" => 0
                        ]


                    ]
                ];

                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                    'data' => $data,
                ], 200);
            } else {


                $addressPickupExists = StoreAddress::where(
                    'store_id',
                    $request->store->id
                )->where('is_default_pickup', true)->first();

                if (empty($addressPickupExists)) {
                    return response()->json([
                        'code' => 200,
                        'success' => true,
                        'data' => [],
                        'msg_code' => MsgCode::STORE_HAS_NOT_SET_PICKUP_ADDRESS[0],
                        'msg' => MsgCode::STORE_HAS_NOT_SET_PICKUP_ADDRESS[1],
                    ], 200);
                }



                $shipperArr = array(
                    "store_id" => $request->store->id,
                    "from_province_id" => $addressPickupExists->province,
                    "from_district_id" => $addressPickupExists->district,
                    "from_wards_id" => $addressPickupExists->wards,
                    "to_province_id" => $addressCustomerExists->province,
                    "to_district_id" => $addressCustomerExists->district,
                    "service_type" => $request->service_type,
                    "weight" => 100,
                    "length" => $request->length,
                    "width" => $request->width,
                    "height" => $request->height,
                );


                $data = ShipperService::caculate_monney_all($shipperArr);
                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'data' => $data,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                ], 200);
            }
        }
    }
}
