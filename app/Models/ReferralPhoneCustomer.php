<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

//Lưu người giới thiệu
class ReferralPhoneCustomer extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
}
