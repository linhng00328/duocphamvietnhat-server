<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
use App\Helper\PointCustomerUtils;

class PointHistory extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $hidden = ['store_id', "customer_id"];

    protected $appends = ['content', 'references_name_cus'];

    public function getContentAttribute()
    {
        if ($this->type == PointCustomerUtils::ORDER_COMPLETE) {
            return "Hoàn thành đơn hàng " . $this->references_value;
        }

        if ($this->type == PointCustomerUtils::REVIEW_PRODUCT) {
            return "Hoàn thành đánh giá sản phẩm " . $this->references_value;
        }
        if ($this->type == PointCustomerUtils::REFERRAL_CUSTOMER) {
            return "Giới thiệu khách hàng " . $this->references_value . " đăng ký ";
        }

        if ($this->type == PointCustomerUtils::USE_POINT_IN_ORDER) {
            return "Sử dụng điểm mua đơn hàng " . $this->references_value;
        }
        if ($this->type == PointCustomerUtils::REFUND_ORDER) {
            return "Hoàn sản phẩm trong đơn hàng " . $this->references_value;
        }
        if ($this->type == PointCustomerUtils::REGISTER_CUSTOMER) {
            return "Thưởng xu khi đăng ký đăng nhập lần đầu";
        }
        if ($this->type == PointCustomerUtils::CUSTOMER_CANCEL_ORDER) {
            return "Đơn hàng không thể hoàn thành, hoàn trả lại xu " . $this->references_value;
        }
        if ($this->type == PointCustomerUtils::BONUS_POINT_AGENCY) {
            return "Tặng xu trong sản phẩm cho đại lý khi hoàn thành đơn " . $this->references_value;
        }
        if ($this->type == PointCustomerUtils::ADD_POINT) {
            return "Cộng tiền, Lý do: " . $this->note;
        }
        if ($this->type == PointCustomerUtils::SUB_POINT) {
            return "Trừ tiền, Lý do: " . $this->note;
        }



        return "";
    }

    public function getReferencesNameCusAttribute()
    {

        if ($this->type == PointCustomerUtils::REFERRAL_CUSTOMER) {
            $cus = Customer::where('store_id', $this->store_id)->where('id', $this->references_id)->first();
            return $cus == null ? "" : $cus->name;
        }


        return "";
    }
}
