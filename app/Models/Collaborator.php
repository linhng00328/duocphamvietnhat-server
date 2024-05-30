<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class Collaborator extends BaseModel
{

    use Searchable;
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'payment_auto' => 'boolean',
    ];


    protected $searchable = [
        'columns' => [
            'collaborators.first_and_last_name',
            'collaborators.account_number',
            'customers.name',
            'customers.name',
            'customers.phone_number',
            'customers.email'
        ],
        'joins' => [
            'customers' => ['customers.id', 'collaborators.customer_id']
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
