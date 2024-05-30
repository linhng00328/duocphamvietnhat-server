<?php

namespace App\Models;

use App\Helper\Helper;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class Combo extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_end' => 'boolean',
        'set_limit_amount' => 'boolean',
        'group_customers' => 'array',
        'agency_types' => 'array',
        'group_types' => 'array',
    ];

    protected $with = ['products_combo'];

    public function products_combo()
    {
        return $this->hasMany('App\Models\ProductCombo');
    }

    public function products()
    {
        return $this->belongsToMany(
            'App\Models\Product',
            'product_combos',
            'combo_id',
            'product_id'
        );
    }

    public function canUse()
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



        if ($d1 <   $now  &&  $d2 > $now) {

            return true;
        }

        return false;
    }

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
}
