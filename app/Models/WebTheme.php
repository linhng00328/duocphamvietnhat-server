<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class WebTheme extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_show_logo' => 'boolean',
        'is_show_icon_hotline' => 'boolean',
        'is_show_icon_email' => 'boolean',
        'is_show_icon_facebook' => 'boolean',
        'is_show_icon_zalo' => 'boolean',
        'home_carousel_is_show' => 'boolean',
        'home_top_is_show' => 'boolean',
        'is_show_same_product' => 'boolean',
        'home_list_category_is_show' => 'boolean',
        'is_scroll_button' => 'boolean',
        'is_use_footer_html' => 'boolean',
        'no_use_sub_domain' => 'boolean',
        'is_show_icon_youtube' => 'boolean',
        'is_show_icon_ministry_of_industry_and_trade' => 'boolean',
        'is_show_icon_tiktok' => 'boolean',
        'is_show_list_post_contact' => 'boolean',
        'is_use_custom_menu' => 'boolean',
        'is_show_product_new' => 'boolean',
        'is_show_product_top_sale' => 'boolean',
        'is_show_product_sold' => 'boolean',
        'is_show_product_view' => 'boolean',
        'is_show_product_count_stars' => 'boolean',
    ];

    protected $table = 'web_themes';
    protected $appends = ['carousel_app_images'];

    public function store()
    {
        return $this->belongsto('App\Models\Store');
    }



    public function boolean($key = null, $default = false)
    {
        return filter_var($this->input($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public function getCarouselAppImagesAttribute()
    {
        $store = request('store', $default = null);
        return CarouselAppImage::where('store_id', $store->id)->get();
    }
}
