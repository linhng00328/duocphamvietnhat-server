<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\Helper;
use App\Helper\PhoneUtils;
use App\Helper\Place;
use App\Helper\PointCustomerUtils;
use App\Helper\SendToWebHookUtils;
use App\Helper\StringUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationStaffJob;
use App\Jobs\PushNotificationUserJob;
use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\PointSetting;
use App\Models\ReferralPhoneCustomer;
use App\Models\Staff;
use Exception;
use Illuminate\Http\Request;

/**
 * @group  Customer/Thông tin cá nhân
 */
class CustomerProfileController extends Controller
{

    /**
     * Tạo Lấy thông tin profile
     * @urlParam  store_code required Store code
     */
    public function getProfile(Request $request)
    {
        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $request->customer,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Cập nhật thông tin profile
     * @urlParam  store_code required Store code
     * @bodyParam name String   Họ và tên
     * @bodyParam avatar_image String  Link ảnh avater
     * @bodyParam date_of_birth Date   Ngày sinh
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nam - 2 Nữ
     * @bodyParam province int required id province
     * @bodyParam district int required id district
     * @bodyParam wards int required id wards
     * @bodyParam address_detail Địa chỉ chi tiết
     * 
     */
    public function updateProfile(Request $request)
    {
        $request->customer->update([
            "date_of_birth" =>   $request->date_of_birth,
            "avatar_image" =>   $request->avatar_image,
            "sex" =>   $request->sex,

            'name' => $request->name,
            'name_str_filter' => StringUtils::convert_name_lowcase($request->name),

            'address_detail' => $request->address_detail,
            'province' => $request->province,
            'district' => $request->district,
            'wards' => $request->wards,

            'email' => ($request->customer->email == null || $request->customer->email == "") ? $request->email :  $request->customer->email,

            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),
        ]);

        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_CUSTOMER,   $request->customer);
        
        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $request->customer,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
    /**
     * Cập nhật thông tin profile
     * 
     * @urlParam  store_code required Store code
     * 
     * @bodyParam referral_phone_number 
     * 
     */
    public function updateReferralPhoneNumber(Request $request)
    {
        $hasCus = Customer::where('store_id', $request->store->id)->where('phone_number', $request->referral_phone_number)->first();

        $referral_phone_number = PhoneUtils::convert($request->referral_phone_number);

        if ($referral_phone_number == $request->customer->phone_number) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => 'Không thể  nhập SĐT giới thiệu của bạn',
            ], 400);
        }
        // if ($request->customer->referral_phone_number !=  null) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::ERROR[0],
        //         'msg' => 'Không thể thay đổi số giới thiệu',
        //     ], 400);
        // }

        if ($hasCus  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 400);
        }

        $pointSetting = PointSetting::where(
            'store_id',
            $request->store->id
        )->first();

        if ($pointSetting != null &&  $request->customer->referral_phone_number == null) {
            //nếu có sdt giới thiệu
            if ($request->referral_phone_number != null) {

                $customerRefer = Customer::where('phone_number', $request->referral_phone_number)
                    ->where('store_id', $request->store->id)->first();

                if ($customerRefer  != null) {
                    try {
                        ReferralPhoneCustomer::create([
                            'store_id' => $request->store->id,
                            "customer_id" =>   $customerRefer->id,
                            "introduce_customer_id" =>   $request->customer->id,
                            "introduce_customer_phone" =>  $request->customer->phone_number,
                        ]);

                        if ($customerRefer->sale_staff_id && !$request->customer->sale_staff_id) {
                            $sale_exists = Staff::where('id', $customerRefer->sale_staff_id)
                                ->where('store_id', $request->store->id)
                                ->where('is_sale', true)
                                ->exists();

                            if ($sale_exists) {
                                $now = Helper::getTimeNowDateTime();
                                $request->customer->update(
                                    [
                                        'sale_staff_id' => $customerRefer->sale_staff_id,
                                        'time_sale_staff' => $now
                                    ]
                                );
                                PushNotificationStaffJob::dispatch(
                                    $request->store->id,
                                    'Khách hàng của sale',
                                    $request->customer->name . ' đã trở thành khách hàng của bạn(' . $customerRefer->name . ' giới thiệu)',
                                    TypeFCM::NEW_CUSTOMER_SALE,
                                    $request->customer->id,
                                    null,
                                    $customerRefer->sale_staff_id
                                );
                            }
                        }

                        //tính điểm cho customer
                        if ($pointSetting != null) {
                            if ($pointSetting->point_introduce_customer) {
                                PointCustomerUtils::add_sub_point(
                                    PointCustomerUtils::REFERRAL_CUSTOMER,
                                    $request->store->id,
                                    $customerRefer->id,
                                    $pointSetting->point_introduce_customer,
                                    $request->customer->id,
                                    $request->phone_number
                                );
                            }
                        }
                    } catch (Exception $e) {
                    }
                }
            }
        }

        $request->customer->update([
            "referral_phone_number" =>   $request->referral_phone_number,
        ]);

        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_CUSTOMER,   $request->customer);
        
        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $request->customer,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Lấy danh sách GT
     * 
     * @urlParam  store_code required Store code
     * 
     * @bodyParam referral_phone_number 
     * 
     */
    public function getAllReferralPhoneNumber(Request $request)
    {
        $cus = Customer::where('store_id', $request->store->id)
            ->where('referral_phone_number', $request->customer->phone_number)
            ->search(request('search'))
            ->paginate(20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' =>  $cus,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
