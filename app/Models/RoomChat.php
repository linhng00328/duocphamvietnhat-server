<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class RoomChat extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['store_id'];
    protected $with = ['customer'];

    protected $appends = ['last_message'];

    public function getLastMessageAttribute()
    {
        $mess = Messages::where('id', $this->messages_id)->get()->first();
        return $mess;
    }
    public function customer()
    {
        return $this->belongsto('App\Models\Customer');
    }

}
