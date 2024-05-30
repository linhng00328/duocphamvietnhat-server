<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Place;
use App\Http\Controllers\Controller;
use App\Models\ConfigShip;
use App\Models\MsgCode;
use Illuminate\Http\Request;


/**
 * @group User/Cấu hình ship
 * 
 * APIs AppTheme
 */
class ConfigShipController extends Controller
{

    static function defaultDataConfigShip($store_id)
    {
        $configShip = ConfigShip::where('store_id', $store_id)
            ->first();

        if ($configShip  == null) {
            $data = [
                "is_calculate_ship" =>   ConfigShip::is_calculate_ship,
                "use_fee_from_partnership"  =>    ConfigShip::use_fee_from_partnership,
                "use_fee_from_default"  =>    ConfigShip::use_fee_from_default,
                "fee_urban"  =>    ConfigShip::fee_urban,
                "fee_suburban" =>    ConfigShip::fee_suburban,
                "urban_list_id_province" => [],
                "urban_list_name_province" => []
            ];
            $data = json_decode(json_encode($data), false);
        } else {
            $data = $configShip;
        }

        return $data;
    }

    /**
     * Lấy cấu hình ship
     * @urlParam  store_code required Store code
     */
    public function configShip(Request $request)
    {
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ConfigShipController::defaultDataConfigShip($request->store->id),
        ], 200);
    }
    /**
     * Cập nhật cấu hình ship (shipper_id == -1)
     * @urlParam  store_code required Store code
     * @bodyParam is_calculate_ship boolean Cho phép tính phí ship khi customer mua hàng 
     * @bodyParam use_fee_from_partnership boolean Sử dụng phí vận chuyển từ nhà vận chuyển hay không
     * @bodyParam fee_urban double Phí nội thành (khi use_fee_from_partnership = false)
     * @bodyParam fee_suburban double phí ngoại thành (khi use_fee_from_partnership = false)
     * @bodyParam urban_list_id_province List id tỉnh nội thành
     */
    public function updateConfigShip(Request $request)
    {
        $configShip = ConfigShip::where('store_id', $request->store->id)
            ->first();

        $urban = [];
        $urban_name = [];

        if ($request->urban_list_id_province != null && is_array($request->urban_list_id_province)) {
            foreach ($request->urban_list_id_province  as $id) {
                $name =  Place::getNameProvince($id);

                if ($name != null) {
                    array_push($urban, $id);
                    array_push($urban_name, $name);
                }
            }
        }

        $data = [
            "store_id" =>  $request->store->id,
            "is_calculate_ship" =>   $request->is_calculate_ship,
            "use_fee_from_partnership"  =>    $request->use_fee_from_partnership,
            "use_fee_from_default"  =>    $request->use_fee_from_default,
            "fee_urban"  =>   $request->fee_urban,
            "fee_suburban" =>    $request->fee_suburban,
            "fee_default_description" =>    $request->fee_default_description,
            "urban_list_id_province_json" => json_encode($urban),
            "urban_list_name_province_json" => json_encode($urban_name),

        ];

        if ($configShip != null) {
            $configShip->update($data);
        } else {
            ConfigShip::create($data);
        }

        return  $this->configShip($request);
    }
}
