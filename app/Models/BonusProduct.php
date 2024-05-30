<?php

namespace App\Models;

use App\Helper\Helper;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusProduct extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_end' => 'boolean',
        'set_limit_amount' => 'boolean',
        'multiply_by_number' => 'boolean',
        'ladder_reward' => 'boolean',
        'group_customers' => 'array',
        'agency_types' => 'array',
        'group_types' => 'array',
    ];

    public function comingOrHappenning()
    {
        if (
            $this->is_end == true
            || $this->start_time == null
            || $this->end_time == null
            ||
            ($this->set_limit_amount == true &&  $this->amount -  $this->used <= 0)
        ) {
            return false;
        }

        $now = Helper::getTimeNowDateTime();

        $d1 = new DateTime($this->start_time);
        $d2 = new DateTime($this->end_time);

        if ($now <   $d2) {
            return true;
        }

        return false;
    }

    public function bonus_product_items()
    {
        return $this->hasMany(BonusProductItem::class, 'bonus_product_id');
    }
    public function select_products()
    {
        return $this->hasMany(BonusProductItem::class, 'bonus_product_id')
            ->where('is_select_product', true);
    }
    public function bonus_products()
    {
        return $this->hasMany(BonusProductItem::class, 'bonus_product_id')
            ->where('is_select_product', false);
    }
}
