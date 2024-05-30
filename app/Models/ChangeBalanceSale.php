<?php

namespace App\Models;

use App\Services\BalanceCustomerService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class ChangeBalanceSale extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['type_name'];

    public function getTypeNameAttribute()
    {
        if ($this->type == BalanceCustomerService::ORDER_COMPLETED) {
            return "Hoàn thành đơn hàng " . $this->references_value . "";
        }

        if ($this->type == BalanceCustomerService::ORDER_COMPLETED_T1) {
            return "Tiền chia sẻ cho người giới thiệu gián tiếp, đơn hàng " . $this->references_value . "";
        }

        if ($this->type == BalanceCustomerService::BONUS_MONTH) {
            return "Thưởng hoa hồng tháng " . $this->references_value . "";
        }
        if ($this->type == BalanceCustomerService::PAYMENT_REQUEST) {
            return "Hoàn thành yêu cầu thanh toán";
        }

        if ($this->type == BalanceCustomerService::ORDER_COMPLETED_CTV_RE) {
            $order = Order::where('order_code', $this->references_value)->first();
            $phone_cus_order =  $order  == null ? "" :  $order->customer_phone;
            return "Tiền hoa hồng từ khách hàng bạn giới thiệu " . $phone_cus_order . ", đơn hàng " . $this->references_value . "";
        }
        if ($this->type == BalanceCustomerService::USE_BALANCE_ORDER) {
            return "Sử dụng số dư thanh toán đơn hàng " . $this->references_value . "";
        }

        if ($this->type == BalanceCustomerService::ORDER_REFUND_CTV) {
            return "Trừ tiền hoàn tiền đơn hàng " . $this->references_value . "";
        }


        if ($this->type == BalanceCustomerService::CTV_CANCEL_ORDER) {
            return "Cộng lại tiền đơn hàng hủy đơn " . $this->references_value . "";
        }

        if ($this->type == BalanceCustomerService::ADD_BALANCE_CTV) {
            return "Cộng tiền, Lý do: " . $this->note;
        }
        if ($this->type == BalanceCustomerService::SUB_BALANCE_CTV) {
            return "Trừ tiền, Lý do: " . $this->note;
        }
        return "";
    }
}
