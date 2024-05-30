<?php

namespace App\Models;

use App\Helper\StatusDefineCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
use Illuminate\Support\Facades\Cache;
// use Nicolaslopezj\Searchable\SearchableTrait;

class Staff extends BaseModel
{
    // use SearchableTrait;

    protected $hidden = [
        'password',
        'remember_token',
        'store_id',
        'id_decentralization'
    ];

    protected $casts = [
        'is_sale' => 'boolean',
    ];

    // protected $searchable = [
    //     'columns' => [
    //         'name' => 1,
    //     ],
    // ];

    protected $appends = ['decentralization', 'online', 'total_customers'];

    use HasFactory;
    protected $guarded = [];


    public function getDecentralizationAttribute()
    {
        return Cache::remember(json_encode(["Decentralization", $this->store_id, $this->id]), 6, function () {
            return Decentralization::where('store_id', $this->store_id)
                ->where('id',   $this->id_decentralization)->first();
        });
    }


    public function getTotalCustomersAttribute()
    {
        return  Customer::where('sale_staff_id', $this->id)->count();
    }

    public function getOnlineAttribute()
    {
        return  $this->isOnline();
    }


    public function isOnline()
    {
        return Cache::has('staff-is-online-' . $this->id);
    }

    function order()
    {
        return $this->hasMany(Order::class, 'sale_by_staff_id', 'id');
    }
}
