<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class CategoryPost extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['slug', 'category_children'];
    protected $hidden = ['store_id', 'pivot'];

    public function getSlugAttribute()
    {
        $slug = \Str::slug($this->title);
        return $slug;
    }


    protected $casts = [
        'is_show_home' => 'boolean',
    ];

    public function posts()
    {
        return $this->belongsToMany(
            'App\Models\Post',
            'post_category_posts',
            'categorypost_id',
            'post_id'
        );
    }
    public function getCategoryChildrenAttribute()
    {
        return CategoryPostChild::where('category_post_id', $this->id)->orderBy('name')->get();
    }
}
