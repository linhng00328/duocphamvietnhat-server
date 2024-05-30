<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;
use App\Helper\Helper;
use App\Models\Base\BaseModel;

class Agency extends BaseModel
{
    use Searchable;
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'payment_auto' => 'boolean',
    ];

    protected $appends = ['agency_type'];

    protected $searchable = [
        'columns' => [
            'customers.name',
            'agencies.first_and_last_name',
            // 'agencies.account_number',
            'customers.phone_number',
            'customers.email'
        ],
        'joins' => [
            'customers' => ['customers.id', 'agencies.customer_id']
        ]

    ];
    protected $with = [
        'customer',
    ];

    // public function getCustomerAttribute()
    // {
    //     return DB::table('customers')->select('phone_number', 'name', 'id', 'avatar_image', 'address_detail')->where('id', $this->customer_id)->first();
    // }

    public function getAgencyTypeAttribute()
    {

        return AgencyType::where('id', $this->agency_type_id)->where('store_id', $this->store_id)->first();
    }

    public function customer()
    {
        return $this->belongsto(Customer::class);
    }

    function staff_sale_visit_agency()
    {
        $now = Helper::getTimeNowCarbon();
        if (!empty(request('staff'))) {
            return $this->hasOne(SaleVisitAgency::class, 'agency_id', 'id')->whereDate('created_at', $now)->where('staff_id', request('staff')->id)->orderBy('created_at', 'desc');
        } else {
            return null;
        }
    }
}
