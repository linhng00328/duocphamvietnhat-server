<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCustomer extends Model
{
    const GROUP_TYPE_CONDITION = 0;
    const GROUP_TYPE_LIST_CUSTOMER = 1;
    use HasFactory;
    protected $guarded = [];

    public function groupCustomerConditionItems()
    {
        return $this->hasMany(
            'App\Models\GroupCustomerConditionItem',
            "group_customer_id"
        );
    }
    public function customers()
    {
        return $this->belongsToMany(Customer::class, "customer_group_customers");
    }
}
