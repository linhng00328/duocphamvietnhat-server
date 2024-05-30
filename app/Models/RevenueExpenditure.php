<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;
use App\Helper\RevenueExpenditureUtils;
use App\Models\Base\BaseModel;

class RevenueExpenditure extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];
    protected $searchable = [
        'columns' => [
            'code',
        ],
    ];
    protected $casts = [
        'is_revenue' => 'boolean',
        'allow_accounting' => 'boolean',
    ];


    protected $appends = ['type_action_name', 'staff', 'user'];


    public function getTypeActionNameAttribute()
    {

        return RevenueExpenditureUtils::ACTION_NAME_BY_ID[$this->action_create] ?? "";
    }


    public function getStaffAttribute()
    {
        $staff =    Staff::select("name")->where('id', $this->staff_id)->first();
        return  $staff;
    }

    public function getUserAttribute()
    {
        $user =    User::select("name")->where('id', $this->user_id)->first();
        return  $user;
    }
}
