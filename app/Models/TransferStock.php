<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;

class TransferStock extends BaseModel
{
    use HasFactory;
    protected $guarded = [];


    protected $appends = ['to_branch', 'from_branch', 'user', 'staff'];

    public function getToBranchAttribute()
    {
        return Branch::select('name')->where('id', $this->to_branch_id)->first();
    }

    public function getFromBranchAttribute()
    {
        return Branch::select('name')->where('id', $this->from_branch_id)->first();
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
