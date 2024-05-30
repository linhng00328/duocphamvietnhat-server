<?php

namespace App\Models;

use App\Helper\Helper;
use Nicolaslopezj\Searchable\SearchableTrait;
use App\Http\Middleware\UpSpeed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class Post extends BaseModel
{
    use HasFactory;
    use SearchableTrait;

    protected $guarded = [];

    protected $with = ['categories'];
    protected $casts = [
        'published' => 'boolean',
    ];

    protected $searchable = [
        'columns' => [
            'title' => 2,
            'summary' => 1,

        ],
    ];

    protected $appends = [
        'slug'
    ];

    protected $hidden = ['pivot', 'json_list_promotion', 'store_id'];


    public function __construct(array $attributes = array())
    {
        $up_speed = request('up_speed', $default = null);
        if ($up_speed == UpSpeed::SPEED_HOME_APP_CUSTOMER) {
            array_push($this->hidden, 'seo_description',  'published', 'content', 'seo_title', 'category_children');
        }

        if (!isset(static::$booted[get_class($this)])) {
            static::boot();

            static::$booted[get_class($this)] = true;
        }


        $this->fill($attributes);
    }

    public function index()
    {
        $customer = request('customer', $default = null);
        array_push($hidden, 'seo_description');
    }

    public function getSlugAttribute()
    {
        $slug = $this->post_url ??  \Str::slug($this->title);
        return $slug;
    }

    public function categories()
    {
        return $this->belongstoMany(
            'App\Models\CategoryPost',
            'post_category_posts',
            'post_id',
            'categorypost_id'
        );
    }

    public function category_posts()
    {
        return $this->belongstoMany(
            'App\Models\CategoryPost',
            'post_category_posts',
            'post_id',
            'categorypost_id'
        );
    }

    public function category_children()
    {
        return $this->belongsToMany(
            'App\Models\PostCategoryPostChild',
            'post_category_post_children',
            'post_id',
            'category_post_children_id'
        );
    }

    public function getContentAttribute()
    {
        $string      = $this->attributes['content'];
        $spaceString = str_replace('<', ' <', $string);
        $doubleSpace = strip_tags($spaceString);
        $singleSpace = str_replace('  ', ' ', $doubleSpace);
        $singleSpace = str_replace('  ', ' ', $singleSpace);
        $singleSpace = str_replace('  ', ' ', $singleSpace);
        $singleSpace = trim($singleSpace);

        if (strlen($singleSpace) > 70) {
            $singleSpace = substr($singleSpace, 0, 70) . '';
            $singleSpace =  mb_convert_encoding($singleSpace, 'UTF-8', 'UTF-8');
        }



        return     $singleSpace;
    }

    public function getImageUrlAttribute()
    {
        $up_speed = request('up_speed_image', $default = null);
        if ($up_speed != UpSpeed::SPEED_IMAGE_ONE_POST_CUSTOMER) {
            $image_url = empty($this->attributes['image_url']) ? null : Helper::pathReduceImage($this->attributes['image_url'], 452, 'webp');
            // $image_url = empty($this->attributes['image_url']) ? null : strtok($this->attributes['image_url'], '?') . "?new-width=452&image-type=webp";
            return $image_url;
        }
        return  $this->attributes['image_url'];
    }
}
