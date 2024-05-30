<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class HomeButton extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['store_id', 'json_buttons'];
    protected $appends = ['buttons'];

    public function getButtonsAttribute()
    {
        return json_decode($this->json_buttons);
    }
}
