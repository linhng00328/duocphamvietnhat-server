<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerSpinWheel extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = [
        'spin_wheel'
    ];

    public function getSpinWheelAttribute()
    {
        return SpinWheel::where('id', $this->spin_wheel_id)->first();
    }
}
