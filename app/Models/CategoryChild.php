<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;

class CategoryChild extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['updated_at', 'created_at'];
    protected $casts = [
        'category_id' => 'integer',
    ];
    protected $appends = ['slug'];

    public function getSlugAttribute()
    {
        $slug = \Str::slug($this->name);
        return $slug;
    }
}
