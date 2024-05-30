<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;
use AjCastro\Searchable\Searchable;

class TallySheet extends BaseModel
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];
    protected $searchable = [
        'columns' => [
            'code',
        ],
    ];
    protected $appends = ['branch','staff','user'];


    public function getBranchAttribute()
    {
        $branch =    Branch::select("name")->where('id', $this->branch_id)->first();
        return  $branch;
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
