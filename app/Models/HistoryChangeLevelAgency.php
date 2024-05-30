<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryChangeLevelAgency extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['agency'];

    public function getAgencyAttribute()
    {
        return Agency::where('id', $this->agency_id)->where('store_id', $this->store_id)->first();
    }
}
