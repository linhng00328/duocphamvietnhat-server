<?php

namespace App\Models;

// use AjCastro\Searchable\Searchable;
use Nicolaslopezj\Searchable\SearchableTrait;
use App\Helper\PaymentMethodHelper;
use App\Helper\ShipperHelper;
use App\Helper\StatusDefineCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;


class Order extends BaseModel
{

    const ORDER_FROM_WEB = 0;
    const ORDER_FROM_APP = 1;
    const ORDER_FROM_POS_IN_STORE = 2;
    const ORDER_FROM_POS_DELIVERY = 3;
    const ORDER_FROM_POS_SHIPPER = 4;

    use HasFactory;
    // use Searchable;
    use SearchableTrait;

    protected $guarded = [];

    protected $casts = [
        'reviewed' => 'boolean',
        'is_handled_balance_collaborator' => 'boolean',
        'is_handled_balance_agency' => 'boolean',
        'logged' => 'boolean',
        'from_pos' => 'boolean',
        'has_refund_money_for_ctv' => 'boolean',
        'has_refund_money_for_agency' => 'boolean',
        
        'has_refund_point_for_customer' => 'boolean',
        'is_use_points' => 'boolean',
        'is_order_for_customer' => 'boolean',
    ];
    protected $with = ['customer'];

    // protected $searchable = [
    //     'columns' => [
    //         'customer_name',
    //         'order_code',
    //         'customer_phone',
    //     ]
    // ];
    protected $searchable = [
        'columns' => [
            'orders.customer_name' => 1,
            'orders.order_code' => 2,
            'orders.customer_phone' => 3,
            'orders.phone_number' => 4,

            // 'id', bỏ vào là die
        ],
    ];


    protected $appends = [
        'payment_status_code',
        'payment_status_name',
        'order_status_code',
        'order_status_name',
        'payment_method_name',
        'payment_partner_name',
        'shipper_name',

        'customer_used_discount',
        'customer_used_combos',
        'customer_used_voucher',
        'customer_used_bonus_products',
        'line_items_at_time',
        'bonus_agency_history',
        'customer_address',
        'branch',
        'partner_shipper_name'
    ];

    protected $hidden = [
        'store_id',
        'used_discount',
        'used_combos',
        'used_voucher',
        'line_items_in_time',
        // 'customer_name',
        // 'customer_country',
        // 'customer_province',
        // 'customer_district',
        // 'customer_wards',
        // 'customer_village',
        // 'customer_postcode',
        // 'customer_email',
        // 'customer_phone',
        // 'customer_address_detail',
    ];


    public function store()
    {
        return $this->belongsto('App\Models\Store');
    }

    public function getBranchAttribute()
    {
        return Branch::select('name', 'id')->where('id', $this->branch_id)->first();
    }

    public function customer()
    {
        return $this->belongsto('App\Models\Customer');
    }

    public function line_items()
    {
        return $this->hasMany('App\Models\LineItem');
    }
    public function order_shipper_code()
    {
        return $this->hasOne('App\Models\OrderShiperCode', $this->order_id, $this->id);
    }

    public function getBonusAgencyHistoryAttribute()
    {
        return BonusAgencyHistory::where('order_id', $this->id)->first();
    }

    public function getPaymentStatusCodeAttribute()
    {
        return StatusDefineCode::getPaymentStatusCode($this->payment_status);
    }

    public function getPaymentStatusNameAttribute()
    {
        return StatusDefineCode::getPaymentStatusCode($this->payment_status, true);
    }

    public function getOrderStatusCodeAttribute()
    {
        return StatusDefineCode::getOrderStatusCode($this->order_status);
    }

    public function getOrderStatusNameAttribute()
    {
        return StatusDefineCode::getOrderStatusCode($this->order_status, true);
    }

    public function getPaymentMethodNameAttribute()
    {
        return PaymentMethodHelper::getNamePaymentMethod($this->payment_method_id);
    }
    public function getPaymentPartnerNameAttribute()
    {
        return PaymentMethodHelper::getNamePaymentPartner($this->payment_partner_id);
    }

    public function getShipperNameAttribute()
    {
        return ShipperHelper::getNameShipper($this->partner_shipper_id);
    }

    public function getCustomerUsedDiscountAttribute()
    {
        $used_discount = json_decode($this->used_discount);
        return  $used_discount;
    }

    public function getCustomerUsedCombosAttribute()
    {
        $used_combos = json_decode($this->used_combos);
        return  $used_combos;
    }

    public function getCustomerUsedBonusProductsAttribute()
    {
        $used_bonus_products = json_decode($this->used_bonus_products);
        return  $used_bonus_products;
    }

    public function getCustomerUsedVoucherAttribute()
    {
        $used_voucher = json_decode($this->used_voucher);
        return  $used_voucher;
    }

    public function getLineItemsAtTimeAttribute()
    {
        $line_items_at_time = json_decode($this->line_items_in_time);
        return  $line_items_at_time;
    }

    public function getPartnerShipperNameAttribute()
    {
        if ($this->partner_shipper_id == 0) {
            return "Giao Hàng Tiết Kiệm";
        }
        if ($this->partner_shipper_id == 0) {
            return "Giao Hàng Nhanh";
        }
        if ($this->partner_shipper_id == 0) {
            return "Viettel Post";
        }

        return "Giao Hàng Trực Tiếp";
    }


    public function getCustomerAddressAttribute()
    {

        return [
            "name" => $this->customer_name,
            "address_detail" => $this->customer_address_detail,
            "country" => $this->customer_country,
            "province" => $this->customer_province,
            "district" => $this->customer_district,
            "wards" => $this->customer_wards,
            "village" => $this->customer_village,
            "postcode" => $this->customer_postcode,
            "email" => $this->customer_email,
            "phone" => $this->customer_phone,
            "province_name" => $this->customer_province_name,
            "district_name" => $this->customer_district_name,
            "wards_name" => $this->customer_wards_name,
        ];
    }
}
