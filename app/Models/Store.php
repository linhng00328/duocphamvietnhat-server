<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
use AjCastro\Searchable\Searchable;
use App\Helper\TypeOfStoreHelper;

class Store extends BaseModel
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];
    protected $casts = [
        'is_block_app' => 'boolean',
        'has_upload_store' => 'boolean',
        'id_type_of_store' => 'integer',
    ];
    protected $with = ['user'];

    protected $searchable = [
        'columns' => [
            'stores.name',
            'stores.store_code',
            'users.phone_number',
        ],
        'joins' => [
            'users' => ['stores.user_id', 'users.id']
        ]

    ];

    protected $appends = [
        'name_career',
        'name_type',
        'total_products', 'total_product_categories',
        'total_posts', 'total_post_categories',
        'total_customers',
        'total_orders',
    ];

    public function getNameTypeAttribute()
    {
        return  TypeOfStoreHelper::getNametype($this->id_type_of_store);
    }

    public function getNameCareerAttribute()
    {
        return  TypeOfStoreHelper::getNameCareer($this->career);
    }

    public function getTotalProductsAttribute()
    {
        return  Product::where('store_id', $this->id)->where('status', '<>', 1)->count();
    }

    public function getTotalProductCategoriesAttribute()
    {
        return  Category::where('store_id', $this->id)->count();
    }

    public function getTotalPostsAttribute()
    {
        return  Post::where('store_id', $this->id)->count();
    }

    public function getTotalPostCategoriesAttribute()
    {
        return  CategoryPost::where('store_id', $this->id)->count();
    }

    public function getTotalCustomersAttribute()
    {
        return  Customer::where('store_id', $this->id)->where('official', true)->count();
    }

    public function getTotalOrdersAttribute()
    {
        return  Order::where('store_id', $this->id)->count();
    }


    public function user()
    {
        return $this->belongsto('App\Models\User');
    }

    public function appTheme()
    {
        return $this->hasOne('App\Models\AppTheme');
    }
}
