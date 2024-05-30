<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;


class FriendRequest extends BaseModel
{
    use HasFactory;
    protected $guarded = [];


    protected $appends = [
        'customer',
    ];

    public function getCustomerAttribute()
    {
        return Customer::select('id', 'name', 'avatar_image')->where('id', $this->customer_id)->first();
    }
}
