<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Nicolaslopezj\Searchable\SearchableTrait;
use App\Models\Base\BaseModel;
use Illuminate\Support\Facades\Cache;

class EcommerceProduct extends BaseModel
{
    use HasFactory;

    const STATUS_SHOW = 0;
    const STATUS_HIDE = -1;
    const STATUS_DELETED = 1;

    use HasFactory;
    // use Searchable;
    use SearchableTrait;


    //list releted
    protected $guarded = [];
    protected $with = [];
    protected $casts = [
        'price' => 'float',
        'check_inventory' => 'boolean'
    ];
    protected $searchable = [
        'columns' => [
            'name_str_filter' => 1,
            'sku' => 2,
            'barcode' => 3,
            'name_str_filter' => 4,
            // 'id', bỏ vào là die
        ],
    ];
    protected $hidden = ['pivot', 'json_list_promotion', "json_images"];
    protected $appends = [

        'min_price',
        'max_price',
        'slug',
        'price',
        'images',
    ];



    public function getSlugAttribute()
    {
        $slug = null;
        if (!empty($this->seo_title)) {
            $slug = \Str::slug($this->seo_title);
        } else {
            $slug = \Str::slug($this->name);
        }

        return $slug;
    }



    public function getImagesAttribute()
    {
        return json_decode($this->json_images);
    }

    public function getDistributesAttribute()
    {
        return Distribute::where('product_id', $this->id)->take(1)->get();
    }

    public function getAttributesAttribute()
    {
        return Cache::remember(json_encode(["attributes", $this->id,]), 6, function () {
            return Attribute::select('name', 'value', 'id')->where('product_id', $this->id)->get();
        });
    }



    public function getPriceAttribute()
    {
        return  $this->attributes['price'];
    }


    public function getDescriptionAttribute()
    {
        $string      = $this->attributes['description'];
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




    public function getMinPriceAttribute()
    {

        return doubleval($this->attributes['min_price']);
    }

    public function getMaxPriceAttribute()
    {
        return doubleval($this->attributes['max_price']);
    }


    public function getQuantityInStockWithDistributeAttribute()
    {
        return null;
    }
}
