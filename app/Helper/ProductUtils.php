<?php

namespace App\Helper;

use App\Http\Controllers\Api\User\GeneralSettingController;
use App\Models\AgencyEleDisPrice;
use App\Models\AgencyMainPriceOverride;
use App\Models\AgencySubDisPrice;
use App\Models\Distribute;
use App\Models\ElementDistribute;
use App\Models\InventoryEleDis;
use App\Models\InventorySubDis;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\SubElementDistribute;
use Illuminate\Support\Facades\Cache;

class ProductUtils
{
    const  HAS_SUB = "HAS_SUB";
    const  HAS_ELE = "HAS_ELE";
    const  NO_ELE_SUB = "NO_ELE_SUB";


    static public function get_price_with_distribute($product, $distribute_name, $sub_distribute_name, $column = 'price', $is_order_for_customer, $customer, $returnMainPriceIfNonEle = true)
    {
        $price_is_ordered_initially = ProductUtils::get_price_agency_order_initially($customer, $product);

        $price_product = $is_order_for_customer ? $price_is_ordered_initially : $product[$column];
        $type_product =   ProductUtils::check_type_distribute($product);

        if ($distribute_name == null) {

            if (($type_product  == ProductUtils::HAS_SUB || $type_product  == ProductUtils::HAS_ELE) &&  $returnMainPriceIfNonEle == false) {
                return false;
            }

            return $price_product;
        }

        if (count($product->distributes) == 0) {
            return $price_product;
        }

        if (count($product->distributes[0]->element_distributes) == 0) {
            return $price_product;
        }

        //chạy cái element đầu tiên
        foreach ($product->distributes[0]->element_distributes as $element_distributes) {

            //check tên distribute đúng
            if ($element_distributes->name == $distribute_name) {

                //kiểm tra sub của distrite đó có > 0 
                if (count($element_distributes->sub_element_distributes) > 0 &&   $sub_distribute_name != null) {

                    //chạy hết sub đó
                    foreach ($element_distributes->sub_element_distributes as $sub_element_distributes) {

                        if ($sub_element_distributes->name ==  $sub_distribute_name) {

                            return ($column === 'price' ? $sub_element_distributes->price : $sub_element_distributes->price_before_override) ?? $price_product;
                        }
                    }
                } else {
                    return ($column === 'price' ? $element_distributes->price : $element_distributes->price_before_override) ?? $price_product;
                }
            }
        }


        if (($type_product  == ProductUtils::HAS_SUB || $type_product  == ProductUtils::HAS_ELE) &&  $returnMainPriceIfNonEle == false) {
            return false;
        }


        return $price_product;
    }

    static public function get_image_url_distribute($product, $distribute_name, $sub_distribute_name)
    {

        $image_url = null;
        if (count($product->images) > 0) {
            $image_url = $product->images[0]["image_url"];
        };
        if ($distribute_name == null) {
            return $image_url;
        }

        if (count($product->distributes) == 0) {
            return $image_url;
        }

        if (count($product->distributes[0]->element_distributes) == 0) {
            return $image_url;
        }

        //chạy cái element đầu tiên
        foreach ($product->distributes[0]->element_distributes as $element_distributes) {
            //check tên distribute đúng
            if ($element_distributes->name == $distribute_name) {

                //kiểm tra sub của distrite đó có > 0 
                if (count($element_distributes->sub_element_distributes) > 0 &&   $sub_distribute_name != null) {

                    //chạy hết sub đó
                    foreach ($element_distributes->sub_element_distributes as $sub_element_distributes) {

                        if ($sub_element_distributes->name ==  $sub_distribute_name) {

                            return $element_distributes->image_url ?: $image_url;
                        }
                    }
                } else {
                    return $element_distributes->image_url ?: $image_url;
                }
            }
        }

        return $image_url;
    }

    static public function product_has_element_distribute($product)
    {

        if (count($product->distributes) == 0) {
            return false;
        }

        if (count($product->distributes[0]->element_distributes) == 0) {
            return false;
        }

        return true;
    }

    static public function product_has_sub_element_distribute($product)
    {

        if (count($product->distributes) == 0) {
            return false;
        }

        if (count($product->distributes[0]->element_distributes) == 0) {
            return false;
        }
        if (count($product->distributes[0]->element_distributes[0]->sub_element_distributes) == 0) {
            return false;
        }

        return true;
    }

    static public function get_max_price_with_agency_price($agency_price)
    {
        $main_price =  $agency_price['main_price'] ?? 0;
        $distributes =  $agency_price['distributes'] ?? 0;

        if ($distributes == null || count($distributes) == 0) {
            return   $main_price;
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

        return  doubleval($currentPrice == -1 ? $main_price : $currentPrice);
    }

    static public function get_min_price_with_agency_price($agency_price)
    {

        $main_price =  $agency_price['main_price'] ?? 0;
        $distributes =  $agency_price['distributes'] ?? 0;

        if ($distributes == null || count($distributes) == 0) {
            return   $main_price;
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

    static public function get_main_price_with_agency_type($product_id, $agency_type_id, $default_price)
    {
        $mainPrice = null;

        if (UserUtils::isUser() || AgencyUtils::isAgency()) {
            $mainPrice = Cache::remember(json_encode(['product_id', $product_id, "get_main_price_with_agency_type", $agency_type_id]), 6, function () use ($product_id, $agency_type_id) {
                return AgencyMainPriceOverride::where('product_id', $product_id)
                    ->where('agency_type_id', $agency_type_id)
                    ->first();
            });
        }

        return    doubleval($mainPrice == null ?  $default_price :    $mainPrice->price);
    }

    static public function get_percent_agency_with_agency_type($product_id, $agency_type_id)
    {

        $mainPrice = Cache::remember(json_encode(['product_id', $product_id, "get_main_price_with_agency_type", $agency_type_id]), 6, function () use ($product_id, $agency_type_id) {
            return AgencyMainPriceOverride::where('product_id', $product_id)
                ->where('agency_type_id', $agency_type_id)
                ->first();
        });


        return    doubleval($mainPrice == null ?  0 :    $mainPrice->percent_agency);
    }



    static public function get_price_distributes_with_agency_type($product_id, $agency_type_id, $customer_id, $store_id, $key = "", $is_order_for_customer = false)
    {
        $distributes_db = Distribute::where('product_id', $product_id)->take(1)->get();


        if ((UserUtils::isUser() || AgencyUtils::isAgency()) && $agency_type_id != null) {

            if ($distributes_db != null && count($distributes_db) > 0) {

                $distributes = [];
                foreach ($distributes_db as $distribute) {

                    $element_distributes = [];
                    foreach ($distribute->element_distributes as $element_distribute) {
                        $sub_element_distributes = [];
                        foreach ($element_distribute->sub_element_distributes as $sub_element_distribute) {

                            $price_sub =
                                AgencySubDisPrice::where('product_id', $product_id)
                                ->where('element_distribute_id',  $element_distribute->id)
                                ->where('sub_element_distribute_id', $sub_element_distribute->id)
                                ->where('agency_type_id', $agency_type_id)->first();

                            $new_price_sub = $price_sub != null ?  $price_sub->price : $sub_element_distribute->price;
                            array_push(
                                $sub_element_distributes,
                                [
                                    "id" => $sub_element_distribute->id,
                                    "element_distribute_id" => $sub_element_distribute->element_distribute_id,
                                    "name" => $sub_element_distribute->name,
                                    "quantity_in_stock" =>  $sub_element_distribute->quantity_in_stock,
                                    "price" => $is_order_for_customer ? $sub_element_distribute->price : $new_price_sub,
                                    "price_before_override" => $is_order_for_customer ? $new_price_sub : $sub_element_distribute->price
                                ]
                            );
                        }

                        $ele_price = AgencyEleDisPrice::where('product_id',  $product_id)
                            ->where('agency_type_id',  $agency_type_id,)
                            ->where('element_distribute_id', $element_distribute->id)->first();

                        $new_price_ele = $ele_price != null ? $ele_price->price :  $element_distribute->price;
                        array_push(
                            $element_distributes,
                            [
                                "name" =>  $element_distribute->name,
                                "id" =>  $element_distribute->id,
                                "price" => $is_order_for_customer ? $element_distribute->price : $new_price_ele,
                                "quantity_in_stock" =>  $element_distribute->quantity_in_stock,
                                "image_url" => $element_distribute->image_url,
                                "price_before_override" => $is_order_for_customer ? $new_price_ele : $element_distribute->price,
                                "sub_element_distributes" =>   $sub_element_distributes,

                            ]
                        );
                    }

                    $object = json_decode(json_encode((object) [
                        "id" => $distribute->id,
                        "name" => $distribute->name,
                        "sub_element_distribute_name" =>  $distribute->sub_element_distribute_name,
                        "element_distributes" =>  $element_distributes
                    ]), FALSE);

                    $distribute->element_distributes = [];

                    // $element_distributes

                    $distributes = [
                        $object
                    ];
                }


                return  $distributes;
            }
        }

        return  $distributes_db;
    }

    static public function get_main_stock_with_branch_ids($product_id, $branch_ids, $default_stock)
    {
        $mainPrice = null;

        $stock = 0;


        if (count($branch_ids) > 1) {

            $stock  = Cache::remember(json_encode(["ProductInventorymul", "product_id", $branch_ids, $product_id,]), 6, function ()  use ($product_id, $branch_ids) {
                return ProductInventory::where('product_id', $product_id)
                    ->whereIn('branch_id', $branch_ids)
                    ->sum('stock');
            });
        } else {
            $mainPrice  = Cache::remember(json_encode(["ProductInventory1", "product_id", $branch_ids, $product_id,]), 6, function ()  use ($product_id, $branch_ids) {
                return ProductInventory::where('product_id', $product_id)
                    ->whereIn('branch_id', $branch_ids)
                    ->first();
            });
            $stock = $mainPrice == null ?  $default_stock :    $mainPrice->stock;
        }


        return   $stock;
    }

    static public function get_main_cost_of_capital_with_branch_ids($product_id, $branch_ids, $default_price)
    {
        $mainPrice = null;

        $mainPrice  = Cache::remember(json_encode(["ProductInventory", "product_id", $product_id,]), 6, function ()  use ($product_id, $branch_ids) {
            return ProductInventory::where('product_id', $product_id)
                ->whereIn('branch_id', $branch_ids)
                ->first();
        });

        if (count($branch_ids) > 1) {
            $stock =
                ProductInventory::where('product_id', $product_id)
                ->whereIn('branch_id', $branch_ids)
                ->whereIn('branch_id', $branch_ids)->sum('stock');

            return  $stock;
        }

        return  $mainPrice == null ?  $default_price :    $mainPrice->cost_of_capital;
    }

    static public function get_stock_distributes_with_branch_ids($product_id, $branch_ids, $customer_id, $store_id)
    {



        $data = [];

        $distributes_db = Distribute::where('product_id', $product_id)->take(1)->get();

        if ($branch_ids != null && is_array($branch_ids) && count($branch_ids) > 0) {

            if ($distributes_db != null && count($distributes_db) > 0) {

                $distributes = [];
                foreach ($distributes_db as $distribute) {

                    $element_distributes = [];
                    foreach ($distribute->element_distributes as $element_distribute) {

                        $sub_element_distributes = [];
                        foreach ($element_distribute->sub_element_distributes as $sub_element_distribute) {

                            $stock = 0;

                            $stock_sub =
                                InventorySubDis::where('product_id', $product_id)
                                ->where('element_distribute_id',  $element_distribute->id)
                                ->where('sub_element_distribute_id', $sub_element_distribute->id)
                                ->whereIn('branch_id', $branch_ids)->first();

                            $stock = $stock_sub != null ?  $stock_sub->stock : 0;

                            if (count($branch_ids) > 1) {
                                $stock =
                                    InventorySubDis::where('product_id', $product_id)
                                    ->where('element_distribute_id',  $element_distribute->id)
                                    ->where('sub_element_distribute_id', $sub_element_distribute->id)
                                    ->whereIn('branch_id', $branch_ids)->sum('stock');
                            }

                            array_push(
                                $sub_element_distributes,
                                [
                                    "id" => $sub_element_distribute->id,
                                    "sku" =>  $sub_element_distribute->sku ?? null,
                                    "element_distribute_id" => $sub_element_distribute->element_distribute_id,
                                    "name" => $sub_element_distribute->name,
                                    "quantity_in_stock" =>  $sub_element_distribute->quantity_in_stock,
                                    "stock" => $stock,
                                    "cost_of_capital" => $stock_sub != null ?  $stock_sub->cost_of_capital : 0,
                                ]
                            );
                        }

                        $ele_stock = InventoryEleDis::where('product_id',  $product_id)
                            ->whereIn('branch_id',  $branch_ids)
                            ->where('element_distribute_id', $element_distribute->id)->first();

                        $stock = $ele_stock != null ?  $ele_stock->stock : 0;

                        if (count($branch_ids) > 1) {
                            $stock =
                                InventoryEleDis::where('product_id',  $product_id)
                                ->whereIn('branch_id',  $branch_ids)
                                ->where('element_distribute_id', $element_distribute->id)->sum('stock');
                        }

                        array_push(
                            $element_distributes,
                            [
                                "name" =>  $element_distribute->name,
                                "image_url" =>  $element_distribute->image_url,
                                "sku" =>  $element_distribute->sku ?? null,
                                "id" =>  $element_distribute->id,
                                "quantity_in_stock" =>  $element_distribute->quantity_in_stock,
                                "sub_element_distributes" =>   $sub_element_distributes,
                                "stock" => $stock,
                                "cost_of_capital" => $ele_stock != null ?  $ele_stock->cost_of_capital : 0,
                            ]
                        );
                    }

                    $object = json_decode(json_encode((object) [
                        "id" => $distribute->id,
                        "name" => $distribute->name,
                        "sub_element_distribute_name" =>  $distribute->sub_element_distribute_name,
                        "element_distributes" =>  $element_distributes
                    ]), FALSE);

                    $distribute->element_distributes = [];

                    // $element_distributes

                    $distributes = [
                        $object
                    ];
                }


                return  $distributes;
            }
        }
        $object = json_decode(json_encode((object) $distributes_db), FALSE);


        return  $object;
    }

    static public function auto_choose_distribute($product)
    {
        $data = [];
        $type_product =    ProductUtils::check_type_distribute($product);
        if ($type_product  == ProductUtils::HAS_ELE) {
            $element_distributes = $product->distributes[0]->element_distributes;
            foreach ($element_distributes  as  $element_distribute) {
                $data['distribute_name'] =   $product->distributes[0]->name;

                $data['element_distribute_id'] =  $element_distribute->id;
                $data['element_distribute_name'] =  $element_distribute->name;
                break;
            }
        }

        if ($type_product  == ProductUtils::HAS_SUB) {
            $element_distributes = $product->distributes[0]->element_distributes;
            foreach ($element_distributes  as  $element_distribute) {
                foreach ($element_distribute->sub_element_distributes  as  $sub_element_distribute) {
                    $data['distribute_name'] =   $product->distributes[0]->name;
                    $data['element_distribute_name'] =  $element_distribute->name;
                    $data['element_distribute_id'] =  $element_distribute->id;
                    $data['sub_element_distribute_id'] =  $sub_element_distribute->id;
                    $data['sub_element_distribute_name'] =  $sub_element_distribute->name;
                    break;
                }
            }
        }

        return  $data;
    }
    static public function check_type_distribute($productExists)
    {
        $type_product =   ProductUtils::NO_ELE_SUB;
        if ($productExists->distributes != null && count($productExists->distributes) > 0) {

            if (isset($productExists->distributes[0]->element_distributes) && count($productExists->distributes[0]->element_distributes) > 0) {

                if (count($productExists->distributes[0]->element_distributes[0]->sub_element_distributes) > 0) {
                    $type_product =   ProductUtils::HAS_SUB;
                } else {
                    $type_product =   ProductUtils::HAS_ELE;
                }
            }
        } else {
            $type_product =   ProductUtils::NO_ELE_SUB;
        }

        return $type_product;
    }
    // $product_id = request("product_id");
    // $agency_type_id = $request->input('agency_type_id');

    // $mainPrice = AgencyMainPriceOverride::where('store_id', $request->store->id)
    //     ->where('product_id', $product_id)
    //     ->where('agency_type_id', $agency_type_id)
    //     ->first();

    // $element_distribute_price = AgencyEleDisPrice::where('store_id', $request->store->id)
    //     ->where('product_id', $product_id)
    //     ->where('agency_type_id', $agency_type_id)
    //     ->get();

    // $sub_element_distributes_price = AgencySubDisPrice::where('store_id', $request->store->id)
    //     ->where('product_id', $product_id)
    //     ->where('agency_type_id', $agency_type_id)
    //     ->get();


    static public function get_id_distribute_and_stock($store_id, $branch_id, $product_id, $distribute_name, $element_distribute_name, $sub_element_distribute_name)
    {

        if (!empty($distribute_name) && !empty($element_distribute_name) && !empty($sub_element_distribute_name)) {

            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_name)->where('store_id', $store_id)->first();

            if ($distribute != null) {
                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                if ($ele_distribute != null) {
                    $sub_ele_distribute =    SubElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('element_distribute_id', $ele_distribute->id)
                        ->where('name', $sub_element_distribute_name)->where('store_id', $store_id)->first();

                    if ($sub_ele_distribute  != null) {
                        $inventorySub = InventorySubDis::where('element_distribute_id', $ele_distribute->id)
                            ->where('sub_element_distribute_id', $sub_ele_distribute->id)
                            ->where('product_id', $product_id)
                            ->first();

                        if ($inventorySub != null) {

                            return  [
                                'type' =>  ProductUtils::HAS_SUB,
                                'stock' =>  $inventorySub->stock,
                                'distribute_id' => $distribute->id,
                                'element_distribute_id' =>  $ele_distribute->id,
                                'sub_element_distribute_id' =>     $sub_ele_distribute->id
                            ];
                        } else {
                            return  [
                                'type' =>  ProductUtils::HAS_SUB,
                                'stock' =>  0,
                                'distribute_id' => $distribute->id,
                                'element_distribute_id' =>  $ele_distribute->id,
                                'sub_element_distribute_id' =>     $sub_ele_distribute->id
                            ];
                        }
                    }
                }
            }
        } else if (!empty($distribute_name) && !empty($element_distribute_name)) {

            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_name)->where('store_id', $store_id)->first();

            if ($distribute != null) {

                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                if ($ele_distribute != null) {

                    $inventory_ele = InventoryEleDis::where('element_distribute_id', $ele_distribute->id)
                        ->where('branch_id', $branch_id)
                        ->where('product_id', $product_id)
                        ->first();


                    if ($inventory_ele  != null) {
                        return  [
                            'type' =>  ProductUtils::HAS_ELE,
                            'stock' =>  $inventory_ele->stock ?? 0,
                            'distribute_id' => $distribute->id,
                            'element_distribute_id' =>  $ele_distribute->id,
                        ];
                    } else {
                        return  [
                            'type' =>  ProductUtils::HAS_ELE,
                            'stock' =>  0,
                            'distribute_id' => $distribute->id,
                            'element_distribute_id' =>  $ele_distribute->id,
                        ];
                    }
                }
            }
        } else {

            $productInventoryExists =    ProductInventory::where('product_id', $product_id)
                ->where('branch_id', $branch_id)
                ->where('store_id', $store_id)->first();

            if ($productInventoryExists != null) {

                return  [
                    'type' =>  ProductUtils::NO_ELE_SUB,
                    'stock' =>  $productInventoryExists->stock,
                ];
            } else if ($branch_id != null) {
                ProductInventory::create([
                    "product_id" => $product_id,
                    "branch_id" => $branch_id,
                    "store_id" => $store_id,
                    "stock" => 0
                ]);
                return  [
                    'type' =>  ProductUtils::NO_ELE_SUB,
                    'stock' =>  0,
                ];
            } else {
                return  [
                    'type' =>  ProductUtils::NO_ELE_SUB,
                    'stock' =>  0,
                ];
            }
        }

        return null;
    }


    static public function arr_list_product_out_of_stock($store_id, $arr_product)
    {

        $arr_out_stock = array();

        $config = GeneralSettingController::defaultOfStoreID($store_id);

        $near = $config['noti_stock_count_near'];
        foreach ($arr_product->get() as $product) {

            if (!in_array($product->id, $arr_out_stock) &&  $product->check_inventory == true) {


                $type_product =   ProductUtils::check_type_distribute($product);

                if ($type_product  == ProductUtils::HAS_SUB) {

                    foreach ($product->inventory['distributes'][0]->element_distributes as $element_distributes) {
                        foreach ($element_distributes->sub_element_distributes as $sub_element_distribute) {

                            if ($sub_element_distribute->stock <= $near) {
                                if (!in_array($product->id, $arr_out_stock)) {
                                    array_push($arr_out_stock, $product->id);
                                }
                            }
                        }
                    }
                } else if ($type_product  == ProductUtils::HAS_ELE) {


                    foreach ($product->inventory['distributes'][0]->element_distributes as $element_distributes) {

                        if ($element_distributes->stock <= $near) {
                            if (!in_array($product->id, $arr_out_stock)) {
                                array_push($arr_out_stock, $product->id);
                            }
                        }
                    }
                } else {
                    if ($product->inventory['main_stock'] <= $near) {
                        if (!in_array($product->id, $arr_out_stock)) {
                            array_push($arr_out_stock, $product->id);
                        }
                    }
                }
            }
        }

        return   $arr_out_stock;
    }

    static public function get_price_agency_order_initially($customer, $product)
    {
        $customer = $customer ?: null;
        $main_price = null;

        if ($customer != null) {
            $agency = AgencyUtils::getAgencyByCustomerId($customer->id);
            if ($agency != null) {

                return ProductUtils::get_main_price_with_agency_type($product->id, $agency->agency_type_id, $product['price']);
            }
        }

        return  doubleval($main_price ?? $product['price']);
    }
}
