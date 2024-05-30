<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Nicolaslopezj\Searchable\SearchableTrait;
use App\Helper\AgencyUtils;
use App\Helper\ProductUtils;
use App\Singleton\ProductListID;
use App\Models\Base\BaseModel;

use App\Helper\GroupCustomerUtils;
use App\Helper\Helper;
use App\Http\Middleware\UpSpeed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Product extends BaseModel
{
    const STATUS_SHOW = 0;
    const STATUS_HIDE = -1;
    const STATUS_DELETED = 1;

    use HasFactory;
    // use Searchable;
    use SearchableTrait;


    //list releted
    protected $guarded = [];
    protected $with = [
        //'distributes',
        // 'categories',
        // 'category_children'
        // 'product_retail_steps',
    ];
    protected $casts = [
        'price' => 'float',
        'check_inventory' => 'boolean',
        'is_medicine' => 'boolean',
        'is_product_retail_step' => 'boolean',
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
    protected $hidden = ['pivot', 'json_list_promotion'];
    protected $appends = [
        'distributes',
        'product_discount',
        'quantity_in_stock_with_distribute',
        'min_price',
        'max_price',
        'min_price_before_override',
        'max_price_before_override',
        'is_new',
        'is_top_sale',
        'is_favorite',
        'list_promotion',
        'slug',
        'inventory',
        'price',
        'attributes',
        'images',
        'percent_agency',

    ];


    public function __construct(array $attributes = array())
    {
        $up_speed = request('up_speed', $default = null);

        if ($up_speed == UpSpeed::SPEED_HOME_APP_CUSTOMER) {
            array_push(
                $this->hidden,
                'seo_description',
                'seo_title',
                'content_for_collaborator',
                'name_str_filter',
                'sku',
                'description',
                'index_image_avatar',
                'barcode',
                'status',
                'created_at',
                'updated_at',
                'attributes',
                'import_price',
                'default_price'
            );

            $this->appends = $this->array_remove($this->appends, [
                'list_promotion',
                'inventory',
                'quantity_in_stock_with_distribute',
                // 'distributes'
            ]);
            array_push(
                $this->appends,
                'has_in_combo',
                'has_in_product_discount',
                'has_in_bonus_product'
            );

            if (!isset(static::$booted[get_class($this)])) {
                static::boot();

                static::$booted[get_class($this)] = true;
            }
        }

        if ($up_speed == UpSpeed::SPEED_HOME_CUSTOMER_PRODUCT_BY_CATEGORY) {
            array_push(
                $this->hidden,
                'seo_description',
                'seo_title',
                'content_for_collaborator',
                'name_str_filter',
                'sku',
                'description',
                'index_image_avatar',
                'barcode',
                'status',
                'created_at',
                'updated_at',
                'attributes',
                'import_price',
                'default_price'
            );

            $this->appends = $this->array_remove($this->appends, [
                'list_promotion',
                'inventory',
                'quantity_in_stock_with_distribute',
                'has_in_combo',
                'has_in_product_discount',
                'has_in_bonus_product',
                'distributes',
                'list_promotion',
                'inventory',
                'attributes',
            ]);


            if (!isset(static::$booted[get_class($this)])) {
                static::boot();

                static::$booted[get_class($this)] = true;
            }
        }


        if ($up_speed == UpSpeed::SPEED_PRODUCTS_CUSTOMER) {
            // array_push(
            //     $this->hidden,
            //     'seo_description',
            //     'seo_title',
            //     'content_for_collaborator',
            //     'name_str_filter',
            //     'sku',
            //     'description',
            //     'index_image_avatar',
            //     'barcode',
            //     'status',
            //     'created_at',
            //     'updated_at',
            //     'attributes',
            //     'import_price',
            //     'default_price'
            // );

            // $this->appends =      $this->array_remove($this->appends, [
            //     'list_promotion',
            //     'inventory',
            //     'quantity_in_stock_with_distribute',
            //     // 'distributes'
            // ]);
            array_push(
                $this->appends,
                'has_in_combo',
                'has_in_product_discount',
                'has_in_bonus_product'
            );

            if (!isset(static::$booted[get_class($this)])) {
                static::boot();

                static::$booted[get_class($this)] = true;
            }
        }

        $this->fill($attributes);
    }

    function array_remove(array &$array, array $values)
    {
        foreach ($values as $value) {
            $array =      array_filter($array, function ($a) use ($value) {

                return $a !== $value;
            });
        }
        return  $array;
    }

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

    public function product_retail_steps()
    {
        return $this->hasMany(
            ProductRetailStep::class,
            'product_id',
            'id'
        )->distinct()->orderBy('from_quantity', 'asc');
    }

    public function category_children()
    {
        return $this->belongsToMany(
            'App\Models\CategoryChild',
            'product_category_children',
            'product_id',
            'category_children_id'
        )->distinct();
    }
    public function categories()
    {
        return $this->belongsToMany(
            'App\Models\Category',
            'product_categories',
            'product_id',
            'category_id'
        )->distinct();
    }

    public function attribute_search_children()
    {
        return $this->belongsToMany(
            'App\Models\AttributeSearchChild',
            'pro_att_search_children',
            'product_id',
            'attribute_search_child_id'
        );
    }

    public function attribute_searches()
    {
        $request = request();

        $attribute_search_child_ids = ProAttSearchChild::where(
            'product_id',
            $this->id
        )->get()->pluck('attribute_search_child_id');

        $attribute_search_ids = AttributeSearchChild::where('store_id', $request->store->id)
            ->whereIn('id', $attribute_search_child_ids)
            ->get()->pluck('attribute_search_id');

        $attribute_searches = DB::table('attribute_searches')
            ->where('store_id', $request->store->id)
            ->whereIn('id', $attribute_search_ids)
            ->get();

        foreach ($attribute_searches as $attribute_search) {
            $attribute_search_children = AttributeSearchChild::where('store_id', $request->store->id)
                ->where('attribute_search_id', $attribute_search->id)
                ->whereIn('id', $attribute_search_child_ids)
                ->get();

            $attribute_search->attribute_search_children = $attribute_search_children;
        }

        return $attribute_searches;
    }

    public function getImagesAttribute()
    {
        $up_speed = request('up_speed_image', $default = null);
        return Cache::remember(json_encode(["getImagesAttribute2", $this->id, $up_speed]), 6, function () use ($up_speed) {
            $images = ProductImage::select('image_url')->where('product_id', $this->id)->get();
            if ($up_speed == UpSpeed::SPEED_IMAGE_ONE_PRODUCT_CUSTOMER) {
                foreach ($images as $image) {
                    $image->image_url = empty($image->image_url) ? null : Helper::pathReduceImage($image->image_url, 720, 'webp');
                }
            } else
            if ($up_speed == UpSpeed::SPEED_PRODUCTS_CUSTOMER) {
                foreach ($images as $image) {
                    $image->image_url = empty($image->image_url) ? null : Helper::pathReduceImage($image->image_url, 320, 'webp');
                }
            } else {
                foreach ($images as $image) {
                    $image->image_url = empty($image->image_url) ? null : Helper::pathReduceImage($image->image_url, 320, 'webp');
                }
            }
            // if ($up_speed == UpSpeed::SPEED_IMAGE_ONE_PRODUCT_CUSTOMER) {
            //     foreach ($images as $image) {
            //         $image->image_url = empty($image->image_url) ? null : strtok($image->image_url, '?') . "?new-width=720&image-type=webp";
            //     }
            // } else
            // if ($up_speed == UpSpeed::SPEED_PRODUCTS_CUSTOMER) {
            //     foreach ($images as $image) {
            //         $image->image_url = empty($image->image_url) ? null : strtok($image->image_url, '?') . "?new-width=320&image-type=webp";
            //     }
            // } else {
            //     foreach ($images as $image) {
            //         $image->image_url = empty($image->image_url) ? null : strtok($image->image_url, '?') . "?new-width=320&image-type=webp";
            //     }
            // }


            return $images;
        });
    }

    public function getDistributesAttribute()
    {
        // return $this->belongsToMany(
        //     'App\Models\Distribute',
        //     'product_distributes',
        //     'product_id',
        //     'distribute_id'
        // );

        if (!isset($this->attributes['id'])) {
            return null;
        }

        $customer = request('customer', $default = null);
        $is_order_for_customer = request('is_order_for_customer', false);
        $agency = null;

        if ($customer != null) {
            $agency = AgencyUtils::getAgencyByCustomerId($customer->id);
        }

        $price_dis = ProductUtils::get_price_distributes_with_agency_type(
            $this->attributes['id'],
            $agency == null ? null : $agency->agency_type_id,
            $customer != null ? $customer->id : null,
            $this->attributes['store_id'],
            "",
            $is_order_for_customer
        );

        return $price_dis ?? array();
    }

    public function getAttributesAttribute()
    {
        return Cache::remember(json_encode(["attributes", $this->id,]), 6, function () {
            return Attribute::where('value', '!=', '')->select('name', 'value', 'id')->where('product_id', $this->id)->get();
        });
    }

    public function product_discounts()
    {
        return Cache::remember(json_encode(["ProductDiscount", "product_id", $this->id,]), 6, function () {
            return ProductDiscount::where('product_id', $this->id)->get();
        });

        //  return $this->hasMany('App\Models\ProductDiscount');
    }

    public function product_combos()
    {
        return $this->hasMany('App\Models\ProductCombo');
    }


    public function getPriceAttribute()
    {

        $request = request();
        $customer = request('customer', $default = null);
        $is_order_for_customer = request('is_order_for_customer', false);

        $main_price = null;

        if (!$is_order_for_customer) {
            if ($customer != null) {
                $agency = AgencyUtils::getAgencyByCustomerId($customer->id);
                if ($agency != null) {
                    return ProductUtils::get_main_price_with_agency_type($this->id, $agency->agency_type_id, $this->attributes['price']);
                }
            }
        }

        return  doubleval($main_price ?? $this->attributes['price']);
    }

    public function getPercentAgencyAttribute()
    {

        $request = request();
        $customer = request('customer', $default = null);

        $percent_agency = null;
        if ($customer != null) {
            $agency = AgencyUtils::getAgencyByCustomerId($customer->id);
            if ($agency != null) {
                return ProductUtils::get_percent_agency_with_agency_type($this->id, $agency->agency_type_id);
            }
        }

        return  doubleval($percent_agency ?? 0);
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


    public function getContentForCollaboratorAttribute()
    {
        $string      = $this->attributes['content_for_collaborator'];
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


    public function getProductDiscountAttribute()
    {

        $request = request();
        $customer = request('customer', $default = null);

        return Cache::remember(json_encode(["getProductDiscountAttribute", $this->id,  $customer == null ? null :  $customer->id]), 6, function ()  use ($customer, $request) {
            $product_discount = $this->product_discounts()->pluck('discount_id');
            if (count($product_discount)  == 0) return null;

            $discounts = Discount::whereIn('id', $product_discount)->get();
            if (count($discounts)  == 0) return null;

            $discount = null;
            foreach ($discounts  as $discountItem) {

                if (!$discountItem->canUse()) {
                    continue;
                }

                $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                    $request,
                    $discountItem->group_customer,
                    $discountItem->agency_type_id,
                    $discountItem->group_type_id,
                    $customer,
                    $request->store->id,
                    $discountItem->group_customers,
                    $discountItem->agency_types,
                    $discountItem->group_types
                );

                if ($ok_customer) {

                    $discount = $discountItem;
                    break;
                }
            }

            if ($discount == null) return null;

            $productDiscount = [
                'value' => $discount->value,
                'end_time' => $discount->end_time,
                'discount_price' => round($this->min_price * (1 - $discount->value / 100), 8),
            ];

            return $productDiscount;
        });
    }

    public function hasInDiscount()
    {

        if ($this->getProductDiscountAttribute() != null) return true;

        return false;
    }

    public function getHasInProductDiscountAttribute()
    {
        return $this->hasInDiscount();
    }

    // public function hasInBonusProduct()
    // {
        


    //     $has =  Cache::remember(json_encode(["hasInBonusProduct", "product_id", $this->id]), 6, function () {
    //         $now = Helper::getTimeNowString();

    //         $has1 = DB::table('bonus_product_item_ladders')
    //             ->leftJoin('bonus_products', 'bonus_products.id', '=', 'bonus_product_item_ladders.bonus_product_id')
    //             ->where([
    //                 ['bonus_products.start_time', '<=', $now],
    //                 ['bonus_products.end_time', '>=', $now],
    //                 ['bonus_product_item_ladders.product_id', $this->id],
    //             ])
    //             ->select('bonus_products.id', 'bonus_products.name')->first() != null;


    //         $has2 = DB::table('bonus_product_items')
    //             ->leftJoin('bonus_products', 'bonus_products.id', '=', 'bonus_product_items.bonus_product_id')
    //             ->where([
    //                 ['bonus_products.start_time', '<=', $now],
    //                 ['bonus_products.end_time', '>=', $now],
    //                 ['bonus_product_items.product_id', $this->id],
    //             ])
    //             ->select('bonus_products.id')->first() != null;

    //         return  $has1  ||  $has2;
    //     });

    //     return  $has;
    // }

    public function hasInBonusProduct()
    {

     
        //product discount
     

        $has =  Cache::remember(json_encode(["hasInBonusProduct", "product_id", $this->id]), 6, function () {
            $request = request();
            $customer = request('customer', $default = null);
            $customerRole = null;
            if($customer != null) {
                
                $customerRole = 5;
                if($customer->is_collaborator) {
                    $customerRole = 1;
                }
                if($customer->is_agency) {
                    $customerRole = 2;
                }
            } else {
                $customerRole = 6;
            }
            $now = Helper::getTimeNowString();

            $has1 = DB::table('bonus_product_item_ladders')
                ->leftJoin('bonus_products', 'bonus_products.id', '=', 'bonus_product_item_ladders.bonus_product_id')
                ->where([
                    ['bonus_products.start_time', '<=', $now],
                    ['bonus_products.end_time', '>=', $now],
                    ['bonus_product_item_ladders.product_id', $this->id],
                ])
                ->where(function ($query) use ($customerRole) {
                    $query->where(function ($subquery) {
                        $subquery->leftJoin('bonus_products', 'bonus_products.id', '=', 'bonus_product_item_ladders.bonus_product_id')
                            ->whereJsonContains('bonus_products.group_customers', 0);
                    })->orWhere(function ($subquery) use ($customerRole) {
                        $subquery->leftJoin('bonus_products', 'bonus_products.id', '=', 'bonus_product_item_ladders.bonus_product_id')
                            ->whereJsonContains('bonus_products.group_customers', [$customerRole]);
                    });
                })
                ->select('bonus_products.id', 'bonus_products.name')->first() != null;


            $has2 = DB::table('bonus_product_items')
                ->leftJoin('bonus_products', 'bonus_products.id', '=', 'bonus_product_items.bonus_product_id')
                ->where([
                    ['bonus_products.start_time', '<=', $now],
                    ['bonus_products.end_time', '>=', $now],
                    ['bonus_product_items.product_id', $this->id],
                ])
                ->where(function ($query) use ($customerRole) {
                    $query->where(function ($subquery) {
                        $subquery->leftJoin('bonus_products', 'bonus_products.id', '=', 'bonus_product_items.bonus_product_id')
                            ->whereJsonContains('bonus_products.group_customers', 0);
                    })->orWhere(function ($subquery) use ($customerRole) {
                        $subquery->leftJoin('bonus_products', 'bonus_products.id', '=', 'bonus_product_items.bonus_product_id')
                            ->whereJsonContains('bonus_products.group_customers', [$customerRole]);
                    });
                })
                ->select('bonus_products.id')->first() != null;

            return  $has1  ||  $has2;
        });

        return  $has;
    }

    public function getHasInBonusProductAttribute()
    {
        return $this->hasInBonusProduct();
    }

    public function hasInCombo()
    {

        $request = request();
        $customer = request('customer', $default = null);

        $product_combo = $this->product_combos()->get()->pluck('combo_id');
        if (count($product_combo)  == 0) return false;

        $combos = Combo::whereIn('id', $product_combo)->get();
        if (count($combos)  == 0) return false;

        foreach ($combos  as $comboItem) {

            $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                $request,
                $comboItem->group_customer,
                $comboItem->agency_type_id,
                $comboItem->group_type_id,
                $customer,
                $request->store->id,
                $comboItem->group_customers,
                $comboItem->agency_types,
                $comboItem->group_types
            );

            if (
                $comboItem->comingOrHappenning() == true   &&    $ok_customer
            ) {
                return true;
                break;
            }
        }

        return false;
    }

    public function getHasInComboAttribute()
    {
        return $this->hasInCombo();
    }


    public function getIsNewAttribute()
    {

        // $st_dt = $this->created_at;
        // $end_dt = Helper::getTimeNowDateTime();
        // $st_dt->modify('+5 days');

        // // is the end date more ancient than the start date?
        // if ($st_dt > $end_dt) {
        //     return true;
        // }

        // return false;

        $data = ProductListID::getInstance($this->store_id);
        if ($data->list_id_new  == null || count($data->list_id_new) == 0) {
            return false;
        }
        return in_array($this->id, (array)$data->list_id_new);
    }

    public function getIsTopSaleAttribute()
    {

        $data = ProductListID::getInstance($this->store_id);
        if ($data->list_id_top_sale  == null || count($data->list_id_top_sale) == 0) {
            return false;
        }
        return in_array($this->id, (array)$data->list_id_top_sale);
    }

    public function getIsFavoriteAttribute()
    {
        $request = request();
        $customer = request('customer', $default = null);
        if ($customer  != null) {
            $fv = Favorite::where(
                'store_id',
                $request->store->id
            )->where(
                'customer_id',
                $request->customer->id
            )->where(
                'product_id',
                $this->id
            )
                ->first();
            if ($fv != null) return true;
        }

        return false;
    }


    public function getMinPriceAttribute()
    {

        $customer = request('customer', $default = null);

        $distributes = null;
        if ($customer != null) {
            $agency = AgencyUtils::getAgencyByCustomerId($customer->id);

            if ($agency != null) {
                // $distributes = ProductUtils::get_price_distributes_with_agency_type(
                //     $this->attributes['id'],
                //     $agency == null ? null :  $agency->agency_type_id,
                //     $customer != null ? $customer->id : null,
                //     $this->attributes['store_id']
                // );
                $distributes = Cache::remember(json_encode([
                    "get_price_distributes_with_agency_type",
                    $this->attributes['id'],
                    $agency == null ? null :  $agency->agency_type_id,
                    $customer != null ? $customer->id : null,
                    $this->attributes['store_id']
                ]), 6, function () use ($agency, $customer) {
                    return ProductUtils::get_price_distributes_with_agency_type(
                        $this->attributes['id'],
                        $agency == null ? null :  $agency->agency_type_id,
                        $customer != null ? $customer->id : null,
                        $this->attributes['store_id']
                    );
                });
            } else {
                return doubleval($this->attributes['min_price']);
            }
        } else {

            // $distributes = ProductUtils::get_price_distributes_with_agency_type(
            //     $this->attributes['id'],
            //     null,
            //     null,
            //     $this->attributes['store_id']
            // );
            return doubleval($this->attributes['min_price']);
        }

        $main_price =  $this->attributes['price'] ?? 0;
        if ($customer != null &&    $agency != null) {


            $main_price = Cache::remember(json_encode(["get_main_price_with_agency_type", $this->id, $agency->agency_type_id, $this->attributes['price']]), 6, function () use ($agency, $customer) {
                return ProductUtils::get_main_price_with_agency_type($this->id, $agency->agency_type_id, $this->attributes['price']);
            });
        }

        if ($distributes == null || count($distributes) == 0) {


            return  doubleval($main_price);
        }

        $currentPrice = -1;
        if ($distributes[0]->element_distributes != null && count($distributes[0]->element_distributes) > 0) {
            foreach ($distributes[0]->element_distributes as $element) {

                if ($element->sub_element_distributes != null && count($element->sub_element_distributes) > 0) {
                    foreach ($element->sub_element_distributes as $sub) {

                        $currentPrice = $currentPrice == -1 || (isset($sub->price) && $sub->price < $currentPrice) ? $sub->price  : $currentPrice;
                    }
                } else {
                    $currentPrice =  $currentPrice == -1 ||  (isset($element->price) && $element->price < $currentPrice) ? $element->price  : $currentPrice;
                }
            }
        }

        return  doubleval($currentPrice == -1 ?   $main_price  : $currentPrice);
    }

    public function getMinPriceBeforeOverrideAttribute()
    {
        $distributes = ProductUtils::get_price_distributes_with_agency_type(
            $this->attributes['id'],
            null,
            null,
            $this->attributes['store_id']
        );
        return doubleval($this->attributes['min_price']);
        $main_price =  $this->attributes['price'] ?? 0;
        if ($distributes == null || count($distributes) == 0) {
            return  doubleval($main_price);
        }
        $currentPrice = -1;
        if ($distributes[0]->element_distributes != null && count($distributes[0]->element_distributes) > 0) {
            foreach ($distributes[0]->element_distributes as $element) {

                if ($element->sub_element_distributes != null && count($element->sub_element_distributes) > 0) {
                    foreach ($element->sub_element_distributes as $sub) {

                        $currentPrice = $currentPrice == -1 || (isset($sub->price) && $sub->price < $currentPrice) ? $sub->price  : $currentPrice;
                    }
                } else {
                    $currentPrice =  $currentPrice == -1 ||  (isset($element->price) && $element->price < $currentPrice) ? $element->price  : $currentPrice;
                }
            }
        }
        return  doubleval($currentPrice == -1 ?   $main_price  : $currentPrice);
    }

    public function getMaxPriceAttribute()
    {
        $customer = request('customer', $default = null);


        $distributes = null;

        if ($customer != null) {
            $agency = AgencyUtils::getAgencyByCustomerId($customer->id);

            if ($agency  != null) {
                $distributes = ProductUtils::get_price_distributes_with_agency_type(
                    $this->attributes['id'],
                    $agency == null ? null :  $agency->agency_type_id,
                    $customer != null ? $customer->id : null,
                    $this->attributes['store_id']
                );
            } else {
                return  doubleval($this->attributes['max_price']);
            }
        } else {
            return doubleval($this->attributes['max_price']);

            $distributes = ProductUtils::get_price_distributes_with_agency_type(
                $this->attributes['id'],
                null,
                null,
                $this->attributes['store_id']
            );
        }


        if ($distributes == null || count($distributes) == 0) {
            return doubleval($this->price);
        }

        $currentPrice = -1;

        if ($distributes[0]->element_distributes != null && count($distributes[0]->element_distributes) > 0) {
            foreach ($distributes[0]->element_distributes as $element) {

                if ($element->sub_element_distributes != null && count($element->sub_element_distributes) > 0) {
                    foreach ($element->sub_element_distributes as $sub) {
                        $currentPrice = $currentPrice == -1 ||   (isset($sub->price) && $sub->price > $currentPrice) ? $sub->price : $currentPrice;
                    }
                } else {
                    $currentPrice =  $currentPrice == -1 || (isset($element->price) && $element->price > $currentPrice) ?  $element->price : $currentPrice;
                }
            }
        }

        return  doubleval($currentPrice == -1 ? $this->price : $currentPrice);
    }


    public function getMaxPriceBeforeOverrideAttribute()
    {
        $distributes = ProductUtils::get_price_distributes_with_agency_type(
            $this->attributes['id'],
            null,
            null,
            $this->attributes['store_id']
        );

        if ($distributes == null || count($distributes) == 0) {
            return doubleval($this->attributes['max_price']);
        }

        $currentPrice = -1;

        if ($distributes[0]->element_distributes != null && count($distributes[0]->element_distributes) > 0) {
            foreach ($distributes[0]->element_distributes as $element) {

                if ($element->sub_element_distributes != null && count($element->sub_element_distributes) > 0) {
                    foreach ($element->sub_element_distributes as $sub) {
                        $currentPrice = $currentPrice == -1 ||   (isset($sub->price) && $sub->price > $currentPrice) ? $sub->price : $currentPrice;
                    }
                } else {
                    $currentPrice =  $currentPrice == -1 || (isset($element->price) && $element->price > $currentPrice) ?  $element->price : $currentPrice;
                }
            }
        }

        return  doubleval($currentPrice == -1 ? $this->attributes['max_price'] : $currentPrice);
    }

    public function getQuantityInStockWithDistributeAttribute()
    {

        $customer = request('customer', $default = null);
        $distributes = null;
        if ($customer != null) {
            $agency = AgencyUtils::getAgencyByCustomerId($customer->id);
            if ($agency  != null) {
                $distributes = ProductUtils::get_price_distributes_with_agency_type(
                    $this->attributes['id'],
                    $agency == null ? null :  $agency->agency_type_id,
                    $customer != null ? $customer->id : null,
                    $this->attributes['store_id']
                );
            } else {
                $distributes = ProductUtils::get_price_distributes_with_agency_type(
                    $this->attributes['id'],
                    null,
                    null,
                    $this->attributes['store_id']
                );
            }
        } else {
            $distributes = ProductUtils::get_price_distributes_with_agency_type(
                $this->attributes['id'],
                null,
                null,
                $this->attributes['store_id']
            );
        }


        $is_infinite = false;
        if ($distributes == null || count($distributes) == 0) {
            return $this->quantity_in_stock;
        }


        $current_quantity_in_stock = $this->quantity_in_stock;

        if ($distributes[0]->element_distributes != null && count($distributes[0]->element_distributes) > 0) {
            $current_quantity_in_stock = -1;
            foreach ($distributes[0]->element_distributes as $element) {
                if ($element->sub_element_distributes != null && count($element->sub_element_distributes) > 0) {
                    foreach ($element->sub_element_distributes as $sub) {

                        if ($sub->quantity_in_stock === null) $is_infinite = true;

                        if (isset($sub->quantity_in_stock) && $sub->quantity_in_stock >= 0) {
                            if ($current_quantity_in_stock == -1)  $current_quantity_in_stock = 0;
                            $current_quantity_in_stock = $current_quantity_in_stock + $sub->quantity_in_stock;
                        }
                    }
                } else {

                    if ($element->quantity_in_stock === null) $is_infinite = true;

                    if (isset($element->quantity_in_stock) && $element->quantity_in_stock != null && $element->quantity_in_stock >= 0) {
                        if ($current_quantity_in_stock == -1)  $current_quantity_in_stock = 0;
                        $current_quantity_in_stock = $current_quantity_in_stock + $element->quantity_in_stock;
                    }
                }
            }
        }

        if ($is_infinite  == true) return null;

        return  $current_quantity_in_stock;
    }

    public function getListPromotionAttribute()
    {
        return  json_decode($this->json_list_promotion);
    }


    public function getInventoryAttribute()
    {
        $branch = request('branch', $default = null);
        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $branch_ids_input = array();
        if ($branch != null) {
            $branch_ids_input = [$branch->id];
        } else if (count($branch_ids) > 0) {
            $branch_ids_input =  $branch_ids;
        }

        return [
            "main_cost_of_capital" => ProductUtils::get_main_cost_of_capital_with_branch_ids($this->attributes['id'],  $branch_ids_input, 0) ?? 0,
            "main_stock" => ProductUtils::get_main_stock_with_branch_ids($this->attributes['id'],  $branch_ids_input, 0) ?? 0,
            "distributes" => ProductUtils::get_stock_distributes_with_branch_ids($this->attributes['id'],  $branch_ids_input, null,  $this->attributes['store_id']),
        ];
    }
}
