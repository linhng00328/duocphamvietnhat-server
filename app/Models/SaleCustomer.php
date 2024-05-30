<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;

class SaleCustomer extends Model
{

    use Searchable;
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'payment_auto' => 'boolean',
    ];


    protected $searchable = [
        'columns' => [
            'sale_customers.first_and_last_name',
            'sale_customers.account_number',
            'customers.name',
            'customers.name',
            'customers.phone_number',
            'customers.email'
        ],
        'joins' => [
            'customers' => ['customers.id', 'sale_customers.customer_id']
        ]

    ];

    protected $with = [
        'customer',
    ];

    public function customer()
    {
        return $this->belongsto(Customer::class);
    }
}
