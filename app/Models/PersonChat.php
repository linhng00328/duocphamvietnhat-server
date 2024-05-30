<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonChat extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'seen' => 'boolean',
    ];

    protected $appends = ['to_customer'];


    public function getToCustomerAttribute()
    {

        $customer = Customer::select('id', 'name', 'avatar_image')->where('id', $this->to_customer_id)->where('store_id', $this->store_id)->get()->first();
        return $customer;
    }
}
