<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;

class DateTimekeepingShift extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['days_of_week_list'];

    public function getDaysOfWeekListAttribute()
    {
        return json_decode($this->days_of_week);
    }
}
