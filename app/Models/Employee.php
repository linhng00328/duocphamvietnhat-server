<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class Employee extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    
}
