<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class ConfigUserVip extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    
    protected $appends = ['list_id_theme_vip'];

    protected $hidden = ['list_json_id_theme_vip', 'user_id', 'id'];

    public function getListIdThemeVipAttribute()
    {
       return json_decode($this->list_json_id_theme_vip);
    }
}
