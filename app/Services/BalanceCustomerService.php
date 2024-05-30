<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\ChangeBalanceAgency;
use App\Models\ChangeBalanceCollaborator;
use App\Models\Collaborator;
use App\Models\Customer;
use App\Models\Order;
use Exception;

class BalanceCustomerService
{
    const ORDER_COMPLETED = 0; //hoàn thành đơn hàng
    const BONUS_MONTH = 1; //thưởng tháng
    const PAYMENT_REQUEST = 2; //thanh toán yêu cầu
    const USE_BALANCE_ORDER = 3; //Sử dụng để lên đơn hàng
    const ORDER_COMPLETED_T1 = 4; //Cộng tiền cho cộng tác viên T1
    const ORDER_COMPLETED_CTV_RE = 5; //Cộng tiền cho cộng tác viên giới thiệu
    const ORDER_REFUND_CTV = 6; //trừ tiền hoàn đơn hàng
    const CTV_CANCEL_ORDER = 7; //CTV hủy đơn hàng hoàn lại số dư

    const SUB_BALANCE_CTV = 8; //Trừ tiền ctv
    const ADD_BALANCE_CTV = 9; //Cộng tiền ctv
    const AGENCY_ORDER_COMPLETED_FOR_CUSTOMER = 10; //Cộng tiền cho đại lý đại đơn cho khách

    const AGENCY_CANCEL_ORDER = 11; //Agency hủy đơn hàng hoàn lại số dư

    //Thay đổi và cập nhật lịch sử số dư
    public static function change_balance_collaborator($store_id, $customer_id, $type, $money,  $references_id = 0,  $references_value = "", $note = "")
    { // references_id là id của nơi cung cấp tiền như order


        if ($customer_id === null || $money == 0) {
            return;
        }


        $collaborator  = Collaborator::where('store_id', $store_id)->where('customer_id', $customer_id)->first();

        if ($collaborator != null && $money !== null && $type !== null) {

            try {


                $nextBalance = $collaborator->balance + $money;
                //Đã cộng tiền xử lý lưu db
                if ($type == BalanceCustomerService::ORDER_COMPLETED) { // thanh toan thanh cong cong tien
                    ChangeBalanceCollaborator::create([
                        'store_id' => $store_id,
                        'collaborator_id' =>  $collaborator->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" =>  $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::ORDER_COMPLETED_T1) { // CTV T1
                    ChangeBalanceCollaborator::create([
                        'store_id' => $store_id,
                        'collaborator_id' =>  $collaborator->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::ORDER_COMPLETED_CTV_RE) { // CTV GIỚI THIỆU
                    ChangeBalanceCollaborator::create([
                        'store_id' => $store_id,
                        'collaborator_id' =>  $collaborator->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::USE_BALANCE_ORDER) { // su dung de thanh toan don hang

                    $order = Order::where('store_id', $store_id)->where('id', $references_id)->first();
                    $history = ChangeBalanceCollaborator::where('store_id', $store_id)
                        ->where('collaborator_id', $customer_id)
                        ->where('references_id', $references_id)
                        ->where('type', $type)->first();
                    //kiểm tra order tồn tại và phải chua có bản ghi

                    if ($order !== null &&  $history == null) {
                        //thêm history

                        ChangeBalanceCollaborator::create([
                            'store_id' => $store_id,
                            'collaborator_id' =>  $collaborator->id,
                            "type" => $type,
                            "current_balance" => $nextBalance,
                            "money" => $money,
                            "references_id" => $references_id,
                            "references_value" => $order->order_code,
                            "note" => ""
                        ]);
                    }
                }

                if ($type == BalanceCustomerService::BONUS_MONTH) { //thuong thang

                    ChangeBalanceCollaborator::create([
                        'store_id' => $store_id,
                        'collaborator_id' =>  $collaborator->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::PAYMENT_REQUEST) { // thanh toan yeu cau thanh toan

                    ChangeBalanceCollaborator::create([
                        'store_id' => $store_id,
                        'collaborator_id' =>  $collaborator->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::ORDER_REFUND_CTV) { // trừ tiền hoàn đơn hàng

                    ChangeBalanceCollaborator::create([
                        'store_id' => $store_id,
                        'collaborator_id' =>  $collaborator->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => $note
                    ]);
                }


                if ($type == BalanceCustomerService::CTV_CANCEL_ORDER) { // cộng tác viên hủy đơn cộng lại tiền cho nó

                    ChangeBalanceCollaborator::create([
                        'store_id' => $store_id,
                        'collaborator_id' =>  $collaborator->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::ADD_BALANCE_CTV) { // cộng tiền ctv

                    ChangeBalanceCollaborator::create([
                        'store_id' => $store_id,
                        'collaborator_id' =>  $collaborator->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => $note
                    ]);
                }
                if ($type == BalanceCustomerService::SUB_BALANCE_CTV) { // trừ tiền ctv

                    ChangeBalanceCollaborator::create([
                        'store_id' => $store_id,
                        'collaborator_id' =>  $collaborator->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => $note
                    ]);
                }

                $collaborator->update([
                    'balance' => $nextBalance
                ]);
            } catch (Exception $e) {
            }
        }
    }

    //Thay đổi và cập nhật lịch sử số dư agency
    public static function change_balance_agency($store_id, $customer_id, $type, $money,  $references_id = 0,  $references_value = "", $note = "")
    { // references_id là id của nơi cung cấp tiền như order


        if ($customer_id === null || $money == 0) {
            return;
        }


        $agency  = Agency::where('store_id', $store_id)->where('customer_id', $customer_id)->first();

        if ($agency != null && $money !== null && $type !== null) {

            try {


                $nextBalance = $agency->balance + $money;
                //Đã cộng tiền xử lý lưu db
                if ($type == BalanceCustomerService::ORDER_COMPLETED) { // thanh toan thanh cong cong tien
                    ChangeBalanceAgency::create([
                        'store_id' => $store_id,
                        'agency_id' =>  $agency->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" =>  $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::ORDER_COMPLETED_T1) { // CTV T1
                    ChangeBalanceAgency::create([
                        'store_id' => $store_id,
                        'agency_id' =>  $agency->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::AGENCY_ORDER_COMPLETED_FOR_CUSTOMER) { // Đại lý đặt đơn hộ KH
                    ChangeBalanceAgency::create([
                        'store_id' => $store_id,
                        'agency_id' =>  $agency->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::ORDER_COMPLETED_CTV_RE) { // CTV GIỚI THIỆU
                    ChangeBalanceAgency::create([
                        'store_id' => $store_id,
                        'agency_id' =>  $agency->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::USE_BALANCE_ORDER) { // su dung de thanh toan don hang

                    $order = Order::where('store_id', $store_id)->where('id', $references_id)->first();
                    $history = ChangeBalanceAgency::where('store_id', $store_id)
                        ->where('agency_id', $customer_id)
                        ->where('references_id', $references_id)
                        ->where('type', $type)->first();
                    //kiểm tra order tồn tại và phải chua có bản ghi

                    if ($order !== null &&  $history == null) {
                        //thêm history

                        ChangeBalanceAgency::create([
                            'store_id' => $store_id,
                            'agency_id' =>  $agency->id,
                            "type" => $type,
                            "current_balance" => $nextBalance,
                            "money" => $money,
                            "references_id" => $references_id,
                            "references_value" => $order->order_code,
                            "note" => ""
                        ]);
                    }
                }

                if ($type == BalanceCustomerService::BONUS_MONTH) { //thuong thang

                    ChangeBalanceAgency::create([
                        'store_id' => $store_id,
                        'agency_id' =>  $agency->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::PAYMENT_REQUEST) { // thanh toan yeu cau thanh toan

                    ChangeBalanceAgency::create([
                        'store_id' => $store_id,
                        'agency_id' =>  $agency->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::ORDER_REFUND_CTV) { // trừ tiền hoàn đơn hàng

                    ChangeBalanceAgency::create([
                        'store_id' => $store_id,
                        'agency_id' =>  $agency->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => $note
                    ]);
                }


                if ($type == BalanceCustomerService::AGENCY_CANCEL_ORDER) { // cộng tác viên hủy đơn cộng lại tiền cho nó

                    ChangeBalanceAgency::create([
                        'store_id' => $store_id,
                        'agency_id' =>  $agency->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => ""
                    ]);
                }

                if ($type == BalanceCustomerService::ADD_BALANCE_CTV) { // cộng tiền ctv

                    ChangeBalanceAgency::create([
                        'store_id' => $store_id,
                        'agency_id' =>  $agency->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => $note
                    ]);
                }
                if ($type == BalanceCustomerService::SUB_BALANCE_CTV) { // trừ tiền ctv

                    ChangeBalanceAgency::create([
                        'store_id' => $store_id,
                        'agency_id' =>  $agency->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => $note
                    ]);
                }

                $agency->update([
                    'balance' => $nextBalance
                ]);
            } catch (Exception $e) {
            }
        }
    }
}
