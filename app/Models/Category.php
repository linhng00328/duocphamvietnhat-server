<?php

namespace App\Models;

use App\Helper\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;

class Category extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['store_id', 'pivot', 'created_at', 'updated_at', 'banner_ads_json'];
    protected $appends = ['slug', 'category_children', 'banner_ads'];
    protected $casts = [
        'is_show_home' => 'boolean',
    ];



    public function getSlugAttribute()
    {
        $slug = \Str::slug($this->name);
        return $slug;
    }

    public function products()
    {
        return $this->belongsToMany(
            'App\Models\Product',
            'product_categories',
            'category_id',
            'product_id'
        );
    }

    public function getTotalProducts()
    {
        $ids_product = ProductCategory::where('category_id', $this->id)->pluck('product_id');

        $count = Product::whereIn('id', $ids_product)->where('status', '!=', Product::STATUS_DELETED)->count();

        return      $count;
    }
    public function getCategoryChildrenAttribute()
    {
        $categoryChild = CategoryChild::where('category_id', $this->id)->orderBy('position')->get();
        foreach ($categoryChild  as $cate) {
            $cate->image_url = empty($cate->image_url) ? null : Helper::pathReduceImage($cate->image_url, 450, 'webp');
            // $cate->image_url = empty($cate->image_url) ? null : strtok($cate->image_url, '?') . "?new-width=450&image-type=webp";
        }

        return $categoryChild;
    }

    public function getBannerAdsAttribute()
    {
        return json_decode($this->banner_ads_json);
    }
}
