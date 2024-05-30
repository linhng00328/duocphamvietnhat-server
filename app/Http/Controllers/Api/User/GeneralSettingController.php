<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\MsgCode;
use Illuminate\Http\Request;


/**
 * @group User/Cấu hình chung
 * 
 */


class GeneralSettingController extends Controller
{

    static public function  defaultOfStoreID($store_id)
    {
        $configExists = GeneralSetting::where(
            'store_id',
            $store_id
        )->first();

        $defaultSetting = GeneralSetting::defaultSetting();
        $defaultSetting['store_id'] = $store_id;
        if ($configExists == null) {
            $configExists = GeneralSetting::create($defaultSetting);
        }

        return $configExists;
    }

    static public function  defaultOfStore($request)
    {
        $configExists = GeneralSetting::where(
            'store_id',
            $request->store->id
        )->first();

        $defaultSetting = GeneralSetting::defaultSetting();
        $defaultSetting['store_id'] = $request->store->id;
        if ($configExists == null) {
            $configExists = GeneralSetting::create($defaultSetting);
        }

        return $configExists;
    }
    /**
     * Lấy thông số cài đặt
     * @urlParam  store_code required Store code cần lấy
     * @bodyParam noti_near_out_stock boolean Gửi thông báo khi hết kho hàng
     * @bodyParam noti_stock_count_near int Số lượng sản phẩm còn lại báo gần hết hàng
     * @bodyParam  allow_semi_negative boolean Cho phép bán âm
     */
    public function getSetting(Request $request)
    {
        $configExists =  GeneralSettingController::defaultOfStore($request);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>    $configExists,
        ], 200);
    }



    /**
     * update Cấu hình
     * @urlParam  store_code required Store code cần update
     * @bodyParam noti_near_out_stock boolean Gửi thông báo khi hết kho hàng
     * @bodyParam noti_stock_count_near int Số lượng sản phẩm còn lại báo gần hết hàng
     * @bodyParam  allow_semi_negative boolean Cho phép bán âm
     * @bodyParam  email_send_to_customer string email gửi tới khách hàng
     * 
     */
    public function update(Request $request)
    {

        $configExists = GeneralSetting::where(
            'store_id',
            $request->store->id
        )->first();

        $defaultSetting = GeneralSetting::defaultSetting();
        $defaultSetting['store_id'] = $request->store->id;
        $defaultSetting['email_send_to_customer'] = $request->email_send_to_customer;

        if ($configExists == null) {
            $configExists = GeneralSetting::create($defaultSetting);
        } else {
            $configExists->update(Helper::sahaRemoveItemArrayIfNullValue(
                [
                    'noti_near_out_stock' =>  $request->noti_near_out_stock,
                    'noti_stock_count_near' =>  $request->noti_stock_count_near,
                    'allow_semi_negative' =>  $request->allow_semi_negative,
                    'enable_vat' =>  $request->enable_vat,
                    'percent_vat' =>  $request->percent_vat,
                    'allow_branch_payment_order' =>  $request->allow_branch_payment_order,
                    'auto_choose_default_branch_payment_order' =>  $request->auto_choose_default_branch_payment_order,
                    'required_agency_ctv_has_referral_code' =>  $request->required_agency_ctv_has_referral_code,
                    'is_default_terms_agency_collaborator' =>  $request->is_default_terms_agency_collaborator,
                    'terms_agency' =>  $request->terms_agency,
                    'terms_collaborator' =>  $request->terms_collaborator,
                ]
            ));

            $configExists->update(
                [
                    'email_send_to_customer' => $request->email_send_to_customer
                ]
            );
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>    $configExists,
        ], 200);
    }
}
