<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class Voucher extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_end' => 'boolean',
        'set_limit_amount' => 'boolean',
        'set_limit_total' => 'boolean',
        'set_limit_value_discount' => 'boolean',
        'is_show_voucher' => 'boolean',
        'is_public' => 'boolean',
        'is_use_once' => 'boolean',
        'is_use_once_code_multiple_time' => 'boolean',
        'group_customers' => 'array',
        'agency_types' => 'array',
        'group_types' => 'array',
        'is_free_ship' => 'boolean',
    ];

    // protected $with = ['products'];

    public function product_voucher()
    {
        return $this->hasMany('App\Models\ProductVoucher');
    }

    public function products()
    {
        return $this->belongsToMany(
            'App\Models\Product',
            'product_vouchers',
            'voucher_id',
            'product_id'
        );
    }

    public function voucher_codes()
    {
        return $this->hasMany(VoucherCode::class);
    }
}
