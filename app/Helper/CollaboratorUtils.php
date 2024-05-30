<?php

namespace App\Helper;

use App\Jobs\PushNotificationCustomerJob;
use App\Models\Agency;
use App\Models\AgencyConfig;
use App\Models\Collaborator;
use App\Models\CollaboratorsConfig;
use App\Models\Customer;
use App\Models\Order;
use App\Services\BalanceCustomerService;
use Illuminate\Support\Facades\Cache;

class CollaboratorUtils
{

    static function isCollaborator($customer_id, $store_id)
    {

        return Cache::remember(json_encode(["isCollaboratorCac", $customer_id, $store_id]), 6, function () use ($customer_id, $store_id) {
            $customer = Customer::where('id', $customer_id)->where('store_id', $store_id)->first();

            if ($customer != null) {
                if ($customer->is_collaborator == false) return false;

                $collaborator = Collaborator::where('customer_id',    $customer->id)->where('store_id', $store_id)->first();


                if ($collaborator == null) return false;

                if ($collaborator->status != 1) {
                    return false;
                }
                return true;
            }
            return false;
        });
    }


    static function handelBalanceAgencyAndCollaborator($request, $orderExists)
    {
        $customer_agency_exists = Customer::where('id', $orderExists->customer_id)
            ->where('store_id', $request->store->id)
            ->where('is_agency', true)
            ->first();

        if (!$orderExists->logged && $customer_agency_exists != null && AgencyUtils::isAgencyByCustomerId($orderExists->customer_id) == true &&  $orderExists->order_from != Order::ORDER_FROM_POS_IN_STORE && $orderExists->order_from != Order::ORDER_FROM_POS_DELIVERY  && $orderExists->order_from != Order::ORDER_FROM_POS_SHIPPER) {
            CollaboratorUtils::handleBalanceAgencyNotLoggedCompleteOrder($request, $orderExists);
        } else if (!$orderExists->is_order_for_customer && AgencyUtils::isAgencyByCustomerId($orderExists->agency_by_customer_id)) {
            //Nếu là đại lý đặt hàng mà không phải đặt hộ thì không có gt trực tiếp, gián tiếp
            $orderExists->update([
                'share_collaborator' => 0,
                'share_collaborator_referen' => 0,
                'collaborator_by_customer_id' => null,
                'collaborator_by_customer_referral_id' => null,
                'share_agency' => 0,
                'share_agency_referen' => 0,
                'agency_ctv_by_customer_id' => null,
                'agency_ctv_by_customer_referral_id' => null,
            ]);
        } else {
            CollaboratorUtils::handleBalanceAgencyCompleteOrder($request, $orderExists);
            CollaboratorUtils::handleBalanceCTVCompleteOrder($request, $orderExists);
        }
    }

    static function handleBalanceCTVCompleteOrder($request, $orderExists)
    {
        if ($orderExists != null && $orderExists->order_status ==  StatusDefineCode::COMPLETED && $orderExists->payment_status  ==  StatusDefineCode::PAID) {
            $configExists = CollaboratorsConfig::where(
                'store_id',
                $request->store->id
            )->first();

            if (Cache::lock("handleBalanceCTVCompleteOrder" . $orderExists->order_code, 3)->get()) {
                //tiếp tục handle
            } else {
                //đã handle rồi
                return;
            }


            if ($orderExists->is_handled_balance_collaborator == false) {
                //Xem đã tính toán cho CTV chưa  
                if ($configExists != null) {


                    //***Tính hoa hồng trực tiếp

                    //TH1 CTV đặt hộ hoặc Dropship
                    //trước tiên kiểm tra đơn hàng có xuất phát từ id customer đã làm ctv ko (phải thì sét lại id ctv cho đơn)
                    if (CollaboratorUtils::isCollaborator($orderExists->customer_id, $request->store->id) == true) {
                        $orderExists->update([
                            'collaborator_by_customer_id' => $orderExists->customer_id
                        ]);
                    } else {
                        //TH2 tìm người giới thiệu qua khách mua qua sdt (ưu tiên qua sdt)
                        $customer_mh = Customer::where('id', $orderExists->customer_id)->where(
                            'store_id',
                            $request->store->id
                        )->first();


                        if ($customer_mh != null) {
                            //Tìm ctv t2 (là CTV giới thiệu CTV chính của đơn)
                            $customer_gt = Customer::where('phone_number', $customer_mh->referral_phone_number)
                                ->where(
                                    'store_id',
                                    $request->store->id
                                )->where('is_collaborator', true)->first();


                            if ($customer_gt != null) {
                                $orderExists->update([
                                    'collaborator_by_customer_id' => $customer_gt->id
                                ]);
                            }
                        }
                    }

                    if ($orderExists->collaborator_by_customer_id == null) {
                        $orderExists->update([
                            'share_collaborator' => 0
                        ]);
                    }


                    //Cộng tiền chia sẻ cho ctv chính
                    if ($orderExists->collaborator_by_customer_id != null) {
                        BalanceCustomerService::change_balance_collaborator(
                            $request->store->id,
                            $orderExists->collaborator_by_customer_id,
                            BalanceCustomerService::ORDER_COMPLETED,
                            $orderExists->share_collaborator,
                            $orderExists->id,
                            $orderExists->order_code,
                        );

                        if ($orderExists->share_collaborator > 0) {
                            PushNotificationCustomerJob::dispatch(
                                $request->store->id,
                                $orderExists->collaborator_by_customer_id,
                                "Chúc mừng",
                                "Bạn nhận được " . $orderExists->share_collaborator . 'đ hoa hồng từ đơn ' . $orderExists->order_code,
                                TypeFCM::GET_COMMISSION,
                                null
                            );
                        }
                    }

                    //***Tính hoa hồng gián tiếp cho CTV gt CTV

                    //Cộng tiền chia sẻ cho ctv t1 (là CTV giới thiệu CTV chính của đơn)
                    if ($configExists->percent_collaborator_t1 > 0) {

                        if ($orderExists->collaborator_by_customer_id != null) {
                            //Tìm CTV F1 đã giới thiệu F2 đi gt hoặc mua hàng
                            $customer_f1 = null;
                            $customer_f2 = Customer::where('id', $orderExists->collaborator_by_customer_id)->where(
                                'store_id',
                                $request->store->id
                            )->where('is_collaborator', true)->first();

                            //Tìm CTV F1 là CTV giới thiệu gián tiếp cho vào collaborator_by_customer_referral_id
                            if ($customer_f2 != null) {
                                $customer_f1 = Customer::where('phone_number', $customer_f2->referral_phone_number)
                                    ->where(
                                        'store_id',
                                        $request->store->id
                                    )->where('is_collaborator', true)->first();
                            }


                            if ($customer_f1  == null ||  $customer_f2  == null) {
                                $orderExists->update([
                                    'collaborator_by_customer_referral_id' => null
                                ]);
                            } else {
                                if ($customer_f1 != null) {
                                    $orderExists->update([
                                        'share_collaborator_referen' => 0,
                                        'collaborator_by_customer_referral_id' => $customer_f1->id
                                    ]);
                                }
                            }

                            if ($orderExists->collaborator_by_customer_referral_id  != null) {


                                //Cộng tiền chia sẻ cho ctv t2
                                if ($configExists->bonus_type_for_ctv_t2 == 1) {

                                    $share_collaborator = $orderExists->share_collaborator * ($configExists->percent_collaborator_t1 / 100);

                                    if ($customer_f1->id == $orderExists->collaborator_by_customer_id) {

                                        $orderExists->update([
                                            'share_collaborator_referen' => $share_collaborator
                                        ]);
                                    } else {

                                        $orderExists->update([
                                            'share_collaborator_referen' => $share_collaborator
                                        ]);

                                        BalanceCustomerService::change_balance_collaborator(
                                            $request->store->id,
                                            $customer_f1->id,
                                            BalanceCustomerService::ORDER_COMPLETED_T1,
                                            $share_collaborator,
                                            $orderExists->id,
                                            $orderExists->order_code,
                                        );

                                        if ($share_collaborator > 0) {
                                            PushNotificationCustomerJob::dispatch(
                                                $request->store->id,
                                                $customer_f1->id,
                                                "Chúc mừng",
                                                "Bạn nhận được " . $share_collaborator . 'đ hoa hồng. Nội dung: Thưởng giới thiệu đơn ' . $orderExists->order_code,
                                                TypeFCM::GET_COMMISSION,
                                                null
                                            );
                                        }
                                    }
                                } else {
                                    $share_collaborator_referen = ($orderExists->total_after_discount + $orderExists->bonus_points_amount_used + $orderExists->balance_collaborator_used) * ($configExists->percent_collaborator_t1 / 100);
                                    $orderExists->update([
                                        'share_collaborator_referen' =>  $share_collaborator_referen,
                                    ]);

                                    BalanceCustomerService::change_balance_collaborator(
                                        $request->store->id,
                                        $customer_f1->id,
                                        BalanceCustomerService::ORDER_COMPLETED_T1,
                                        $share_collaborator_referen,
                                        $orderExists->id,
                                        $orderExists->order_code,
                                    );

                                    if ($share_collaborator_referen > 0) {
                                        PushNotificationCustomerJob::dispatch(
                                            $request->store->id,
                                            $customer_f1->id,
                                            "Chúc mừng",
                                            "Bạn nhận được " . $share_collaborator_referen . 'đ hoa hồng. Nội dung: Thưởng giới thiệu đơn ' . $orderExists->order_code,
                                            TypeFCM::GET_COMMISSION,
                                            null
                                        );
                                    }
                                }
                            }
                        }
                    }


                    $configAgencyExists = AgencyConfig::where(
                        'store_id',
                        $request->store->id
                    )->first();
                    //***Tính hoa hồng gián tiếp cho Đại lý gt CTV
                    if ($configAgencyExists != null && $configAgencyExists->percent_agency_t1 > 0) {

                        //Tìm ctv f2 đã mua hàng
                        $customer_ctv_f2 = Customer::where('id', $orderExists->collaborator_by_customer_id)->where(
                            'store_id',
                            $request->store->id
                        )->first();

                        //xác nhận người giới thiệu lại đại lý và giới thiệu cho ctv mua hàng
                        if ($customer_ctv_f2  != null &&  $customer_ctv_f2->referral_phone_number && CollaboratorUtils::isCollaborator($customer_ctv_f2->id, $request->store->id)) {
                            //Tìm đại lý t1 (là Đại lý giới thiệu CTV mua đơn này)
                            $customer_agancy_f1 = Customer::where('phone_number',   $customer_ctv_f2->referral_phone_number)
                                ->where(
                                    'store_id',
                                    $request->store->id
                                )->where('is_agency', true)->first();


                            if ($orderExists->agency_ctv_by_customer_referral_id == $orderExists->agency_ctv_by_customer_id) {
                                $orderExists->update([
                                    'share_agency_referen' => 0,
                                    'agency_ctv_by_customer_referral_id' => null
                                ]);
                            }


                            if ($customer_agancy_f1 != null) {

                                $orderExists->update([
                                    'agency_ctv_by_customer_referral_id' => $customer_agancy_f1->id
                                ]);

                                if (AgencyUtils::getAgencyByCustomerId($orderExists->agency_ctv_by_customer_referral_id)) {
                                    //Cộng tiền chia sẻ cho ctv t2
                                    if ($configAgencyExists->bonus_type_for_ctv_t2 == 1) {

                                        /////////Tính chia sẻ cho Agency
                                        $allCart =  $orderExists->line_items;
                                        $share_agency  = 0;

                                        foreach ($allCart as $lineItem) {

                                            if ($lineItem->is_bonus == false) {
                                                $agency = AgencyUtils::getAgencyByCustomerId($customer_agancy_f1->id);
                                                if ($agency  != null) {
                                                    $percent_agency = ProductUtils::get_percent_agency_with_agency_type($lineItem->product->id, $agency->agency_type_id);
                                                    $share_agency = $share_agency + (($lineItem->item_price * ($percent_agency / 100)) * $lineItem->quantity);
                                                }
                                            }
                                        }

                                        $share_agency = $share_agency * ($configAgencyExists->percent_agency_t1 / 100);

                                        $orderExists->update([
                                            'share_agency_referen' => $share_agency,
                                        ]);

                                        BalanceCustomerService::change_balance_agency(
                                            $request->store->id,
                                            $orderExists->agency_ctv_by_customer_referral_id,
                                            BalanceCustomerService::ORDER_COMPLETED_T1,
                                            $share_agency,
                                            $orderExists->id,
                                            $orderExists->order_code,
                                        );

                                        if ($share_agency > 0) {
                                            PushNotificationCustomerJob::dispatch(
                                                $request->store->id,
                                                $orderExists->agency_ctv_by_customer_referral_id,
                                                "Chúc mừng",
                                                "Bạn nhận được " . $share_agency . 'đ hoa hồng. Nội dung: Thưởng giới thiệu đơn ' . $orderExists->order_code,
                                                TypeFCM::GET_COMMISSION,
                                                null
                                            );
                                        }
                                    } else {
                                        $share_agency = ($orderExists->total_after_discount + $orderExists->bonus_points_amount_used + $orderExists->balance_agency_used + $orderExists->balance_collaborator_used) * ($configAgencyExists->percent_agency_t1 / 100);
                                        $orderExists->update([
                                            'share_agency_referen' => $share_agency,
                                            'agency_ctv_by_customer_referral_id' => $customer_agancy_f1->id
                                        ]);

                                        BalanceCustomerService::change_balance_agency(
                                            $request->store->id,
                                            $customer_agancy_f1->id,
                                            BalanceCustomerService::ORDER_COMPLETED_T1,
                                            $share_agency,
                                            $orderExists->id,
                                            $orderExists->order_code,
                                        );

                                        if ($share_agency > 0) {
                                            PushNotificationCustomerJob::dispatch(
                                                $request->store->id,
                                                $customer_agancy_f1->id,
                                                "Chúc mừng",
                                                "Bạn nhận được " . $share_agency . 'đ hoa hồng. Nội dung: Thưởng giới thiệu đơn ' . $orderExists->order_code,
                                                TypeFCM::GET_COMMISSION,
                                                null
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $orderExists->update([
                'is_handled_balance_collaborator' => true
            ]);
        }
    }

    static function handleBalanceAgencyCompleteOrder($request, $orderExists)
    {
        if ($orderExists->order_status ==  StatusDefineCode::COMPLETED && $orderExists->payment_status  ==  StatusDefineCode::PAID) {
            $configExists = AgencyConfig::where(
                'store_id',
                $request->store->id
            )->first();

            if (Cache::lock("handleBalanceAgencyCompleteOrder" . $orderExists->order_code, 6)->get()) {
                //tiếp tục handle
            } else {
                //đã handle rồi
                return;
            }

            if ($orderExists->is_handled_balance_agency == false) {
                //Xem đã tính toán cho Đại lý chưa  
                if ($configExists != null) {

                    //***Tính hoa hồng trực tiếp

                    //TH1 Đại lý bấm đặt hộ  và share link
                    //trước tiên kiểm tra đơn hàng có xuất phát từ id customer đã làm ctv ko (phải thì sét lại id ctv cho đơn)

                    //TH2 tìm người giới thiệu qua khách mua qua sdt (ưu tiên qua sdt)
                    $customer_mh = Customer::where('id', $orderExists->customer_id)->where(
                        'store_id',
                        $request->store->id
                    )->where('is_collaborator', false)->where('is_agency', false)->first();


                    if ($customer_mh != null) {
                        //Tìm ctv t2 (là CTV giới thiệu CTV chính của đơn)
                        $customer_gt = Customer::where('phone_number', $customer_mh->referral_phone_number)
                            ->where(
                                'store_id',
                                $request->store->id
                            )->where('is_agency', true)->first();


                        if ($customer_gt != null) {
                            $orderExists->update([
                                'agency_ctv_by_customer_id' => $customer_gt->id,
                                'agency_ctv_by_customer_referral_id' => null,
                            ]);
                        }
                    }



                    if ($orderExists->agency_ctv_by_customer_id  == $orderExists->customer_id && $orderExists->is_order_for_customer == false) {
                        $orderExists->update([
                            'agency_ctv_by_customer_id' => null,
                            'share_agency' => 0
                        ]);
                    }

                    if ($orderExists->agency_ctv_by_customer_referral_id  == $orderExists->agency_ctv_by_customer_id) {
                        $orderExists->update([
                            'agency_ctv_by_customer_referral_id' => null,
                            'share_agency_referen' => 0
                        ]);
                    }


                    if (
                        $orderExists->agency_ctv_by_customer_referral_id != null && $orderExists->agency_ctv_by_customer_id != null &&
                        AgencyUtils::isAgencyByCustomerId($orderExists->agency_ctv_by_customer_referral_id) == true &&
                        AgencyUtils::isAgencyByCustomerId($orderExists->agency_ctv_by_customer_id) == true
                    ) {
                        $orderExists->update([
                            'agency_ctv_by_customer_referral_id' => null,
                            'share_agency_referen' => 0
                        ]);
                    }


                    //Cộng tiền chia sẻ cho đại lý chính 
                    if (
                        $orderExists->agency_ctv_by_customer_id != null && $orderExists->agency_by_customer_id == null &&
                        CollaboratorUtils::isCollaborator($orderExists->customer_id, $request->store->id) == false &&
                        AgencyUtils::isAgencyByCustomerId($orderExists->agency_ctv_by_customer_id) == true
                    ) {


                        /////////Tính chia sẻ cho Agency
                        $allCart =  $orderExists->line_items;
                        $share_agency  = 0;

                        foreach ($allCart as $lineItem) {

                            if ($lineItem->is_bonus == false) {
                                $agency = AgencyUtils::getAgencyByCustomerId($orderExists->agency_ctv_by_customer_id);
                                if ($agency  != null) {
                                    $percent_agency = ProductUtils::get_percent_agency_with_agency_type($lineItem->product->id, $agency->agency_type_id);
                                    $share_agency = $share_agency + (($lineItem->item_price * ($percent_agency / 100)) * $lineItem->quantity);
                                }
                            }
                        }

                        $orderExists->update([
                            'share_agency' =>  $share_agency
                        ]);


                        BalanceCustomerService::change_balance_agency(
                            $request->store->id,
                            $orderExists->agency_ctv_by_customer_id,
                            BalanceCustomerService::ORDER_COMPLETED,
                            $orderExists->share_agency,
                            $orderExists->id,
                            $orderExists->order_code,
                        );

                        if ($orderExists->share_agency > 0) {
                            PushNotificationCustomerJob::dispatch(
                                $request->store->id,
                                $orderExists->agency_ctv_by_customer_id,
                                "Chúc mừng",
                                "Bạn nhận được " . $orderExists->share_agency . 'đ hoa hồng từ đơn ' . $orderExists->order_code,
                                TypeFCM::GET_COMMISSION,
                                null
                            );
                        }
                    }
                }

                //Cộng tiền hoa hồng cho đại lý đặt hộ
                if ($orderExists->is_order_for_customer && AgencyUtils::isAgencyByCustomerId($orderExists->agency_by_customer_id)) {

                    if ($orderExists->total_commission_order_for_customer > 0) {
                        $orderExists->update([
                            'share_agency' =>  $orderExists->total_commission_order_for_customer,
                            'agency_ctv_by_customer_id' =>  $orderExists->agency_by_customer_id
                        ]);
                    }

                    BalanceCustomerService::change_balance_agency(
                        $request->store->id,
                        $orderExists->agency_by_customer_id,
                        BalanceCustomerService::AGENCY_ORDER_COMPLETED_FOR_CUSTOMER,
                        $orderExists->total_commission_order_for_customer,
                        $orderExists->id,
                        $orderExists->order_code,
                    );

                    if ($orderExists->total_commission_order_for_customer > 0) {
                        PushNotificationCustomerJob::dispatch(
                            $request->store->id,
                            $orderExists->agency_by_customer_id,
                            "Chúc mừng",
                            "Bạn nhận được " . $orderExists->total_commission_order_for_customer . 'đ hoa hồng từ đơn ' . $orderExists->order_code,
                            TypeFCM::GET_COMMISSION,
                            null
                        );
                    }
                }
            }

            $orderExists->update([
                'is_handled_balance_agency' => true
            ]);
        }
    }

    static function handleBalanceAgencyNotLoggedCompleteOrder($request, $orderExists)
    {
        if ($orderExists != null && $orderExists->order_status ==  StatusDefineCode::COMPLETED && $orderExists->payment_status  ==  StatusDefineCode::PAID) {
            $configExists = CollaboratorsConfig::where(
                'store_id',
                $request->store->id
            )->first();

            if (Cache::lock("handleBalanceAgencyNotLoggedCompleteOrder" . $orderExists->order_code, 3)->get()) {
                //tiếp tục handle
            } else {
                //đã handle rồi
                return;
            }

            if ($orderExists->is_handled_balance_agency == false) {
                $configExists = AgencyConfig::where(
                    'store_id',
                    $request->store->id
                )->first();

                if ($configExists != null) {
                    BalanceCustomerService::change_balance_agency(
                        $request->store->id,
                        $orderExists->agency_ctv_by_customer_referral_id,
                        BalanceCustomerService::ORDER_COMPLETED_T1,
                        $orderExists->share_agency_referen,
                        $orderExists->id,
                        $orderExists->order_code,
                    );

                    if ($orderExists->share_agency_referen > 0) {
                        PushNotificationCustomerJob::dispatch(
                            $request->store->id,
                            $orderExists->agency_ctv_by_customer_referral_id,
                            "Chúc mừng",
                            "Bạn nhận được " . $orderExists->share_agency_referen . 'đ hoa hồng. Nội dung: Thưởng giới thiệu đơn ' . $orderExists->order_code,
                            TypeFCM::GET_COMMISSION,
                            null
                        );
                    }
                }
            }

            $orderExists->update([
                'is_handled_balance_agency' => true
            ]);
        }
    }
}
