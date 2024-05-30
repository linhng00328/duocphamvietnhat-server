<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeSearchChild extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'attribute_search_id' => 'integer',
    ];
    
    protected $appends = ['slug'];

    public function getSlugAttribute()
    {
        $slug = \Str::slug($this->name);
        return $slug;
    }
}
