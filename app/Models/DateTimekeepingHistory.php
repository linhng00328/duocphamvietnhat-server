<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;
use Nicolaslopezj\Searchable\SearchableTrait;

class DateTimekeepingHistory extends BaseModel
{
    use HasFactory;
    use SearchableTrait;
    protected $guarded = [];
    protected $hidden = [
        'store_id',
    ];
    protected $casts = [
        'is_checkin' => "boolean",
        'remote_timekeeping' => 'boolean',
        'from_user' => 'boolean',
        'is_bonus' => 'boolean',
    ];

    protected $searchable = [
        'columns' => [
            'staff.name' => 1,
        ],
        'joins' => [
            'staff' => ['staff.id', 'mobile_checkins.staff_id']
        ]
    ];

    protected $appends = ['from_staff_created', 'from_user_created'];

    public function getFromStaffCreatedAttribute()
    {
        $staff =    Staff::select("name")->where('id', $this->from_staff_id)->first();
        return  $staff;
    }

    public function getFromUserCreatedAttribute()
    {
        $user =    User::select("name")->where('id', $this->from_user_id)->first();
        return  $user;
    }
}
