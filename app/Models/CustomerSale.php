<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;
use Illuminate\Support\Facades\DB;

class CustomerSale extends Model
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'email',
            'phone_number',
            'name'
        ],
    ];

    protected $appends = ['staff', 'has_customer', 'customer_id'];

    public function getStaffAttribute()
    {
        return Staff::where('id', $this->staff_id)->first();
    }

    public function getHasCustomerAttribute()
    {
        return DB::table('customers')->select('id')->where('store_id', $this->store_id)->where('phone_number', $this->phone_number)->first() != null;
    }

    public function getCustomerIdAttribute()
    {
        $cus = DB::table('customers')->select('id')->where('store_id', $this->store_id)->where('phone_number', $this->phone_number)->first();
        return  $cus == null ? null :  $cus->id;
    }
}
