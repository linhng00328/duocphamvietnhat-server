<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kreait\Firebase\Factory;
use App\Services\FirebaseService;
use App\Models\Base\BaseModel;

class OtpCodePhone extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
}
