<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;

class AgencyRegisterRequest extends Model
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];
    protected $searchable = [
        'columns' => [],
    ];
    protected $with = [
        'agency',
    ];

    public function agency()
    {
        return $this->belongsto(Agency::class);
    }
}
