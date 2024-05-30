<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\ProductUtils;
use App\Http\Controllers\Controller;
use App\Models\AgencyEleDisPrice;
use App\Models\AgencyMainPriceOverride;
use App\Models\AgencySubDisPrice;
use App\Models\AgencyType;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Distribute;
use App\Models\ElementDistribute;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\ProductDistribute;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\SubElementDistribute;
use Illuminate\Http\Request;


/**
 * @group  User/Giá sản phẩm cho đại lý
 */
class ProductAgencyController extends Controller
{
    static function get_price_discount($price, $percent)
    {
        return round($price * (1 - ($percent / 100)));
    }

    static function updateAllPercentAgeny(Request $request, $product_id, $agency_type_id, $percent)
    {


        $main_price_exists = AgencyMainPriceOverride::where('product_id', $product_id)
            ->where('agency_type_id', $agency_type_id,)
            ->where('store_id', $request->store->id)->first();

        if ($main_price_exists != null) {
            $main_price_exists->update([
                "percent_agency" => $percent,
            ]);
        } else {
            AgencyMainPriceOverride::create([
                "percent_agency" => $percent,
                'store_id' =>  $request->store->id,
                'product_id' => $product_id,
                "agency_type_id" => $agency_type_id,
            ]);
        }
    }

    static function updateAllPriceAgeny(Request $request, $product_id, $agency_type_id, $percent)
    {

        $productExists = Product::where('id', $product_id)->first();


        $distributes = Distribute::where('store_id', $request->store->id)
            ->where('product_id', $productExists->id)->get();

        foreach ($distributes  as     $distribute) {
            $elements =  ElementDistribute::where('store_id', $request->store->id)
                ->where('product_id', $productExists->id)
                ->where('distribute_id',  $distribute->id)->get();

            foreach ($elements   as     $element) {


                if ($element != null) {

                    $ele_price = AgencyEleDisPrice::where('store_id', $request->store->id)
                        ->where('product_id', $productExists->id)
                        ->where('agency_type_id', $agency_type_id,)
                        ->where('element_distribute_id', $element->id)->first();


                    if ($ele_price != null) {

                        $ele_price->update([
                            "price" => ProductAgencyController::get_price_discount($element->price, $percent)
                        ]);
                    } else {

                        AgencyEleDisPrice::create([
                            "store_id" =>  $request->store->id,
                            "product_id" => $productExists->id,
                            "element_distribute_id" => $element->id,
                            "agency_type_id" => $agency_type_id,
                            "price" => ProductAgencyController::get_price_discount($element->price, $percent)
                        ]);
                    }
                }



                if ($element != null) {
                    $subs =  SubElementDistribute::where('store_id', $request->store->id)
                        ->where('product_id', $productExists->id)
                        ->where('distribute_id',  $distribute->id)
                        ->where('element_distribute_id',  $element->id)->get();
                    foreach ($subs   as     $sub) {
                        if ($sub != null) {

                            $price_sub =
                                AgencySubDisPrice::where('store_id', $request->store->id)
                                ->where('product_id', $productExists->id)
                                ->where('element_distribute_id',    $element->id)
                                ->where('sub_element_distribute_id',  $sub->id)
                                ->where('agency_type_id', $agency_type_id)->first();

                            if ($price_sub == null) {
                                AgencySubDisPrice::create([
                                    "store_id" =>  $request->store->id,
                                    "product_id" => $productExists->id,
                                    "element_distribute_id" => $element->id,
                                    "sub_element_distribute_id" => $sub->id,
                                    "agency_type_id" => $agency_type_id,
                                    "price" => ProductAgencyController::get_price_discount($sub->price, $percent)
                                ]);
                            } else {
                                $price_sub->update([
                                    "price" => ProductAgencyController::get_price_discount($sub->price, $percent)
                                ]);
                            }
                        }
                    }
                }
            }
        }




        $main_price_exists = AgencyMainPriceOverride::where('product_id', $productExists->id)
            ->where('agency_type_id', $agency_type_id,)
            ->where('store_id', $request->store->id)->first();

        if ($main_price_exists != null) {
            $main_price_exists->update([
                "price" => ProductAgencyController::get_price_discount($productExists->price, $percent),
            ]);
        } else {
            AgencyMainPriceOverride::create([
                'store_id' =>  $request->store->id,
                'product_id' =>  $productExists->id,
                "price" => ProductAgencyController::get_price_discount($productExists->price, $percent),
                "agency_type_id" => $agency_type_id,
            ]);
        }
    }

    /**
     * Cập nhật giá đại lý 1 sản phẩm
     * @urlParam  store_code required Store code
     * @bodyParam agency_type_id required agency_type_id
     * @bodyParam main_price double required Giá đại lý thay đổi khi không có distribute
     * @bodyParam element_distributes_price List required [{distribute_name:"Màu", element_distribute:"Đỏ", price:180000}]
     * @bodyParam sub_element_distributes_price List required [{distribute_name:"Màu", element_distribute:"Đỏ", sub_element_distribute:"vàng", price:180000}]
     */
    public function updatePriceAgency(Request $request, $id)
    {

        $product_id = request("product_id");
        $agency_type = $request->agency_type_id;


        $productExists = Product::where(
            'id',
            $product_id
        )
            ->where('status', '<>', 1)
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($productExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        }

        $gencyTypeExists = AgencyType::where(
            'id',
            $agency_type
        )
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($gencyTypeExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
            ], 404);
        }



        if (ProductUtils::product_has_element_distribute($productExists) == true &&  ProductUtils::product_has_sub_element_distribute($productExists) == true) {

            if ($request->sub_element_distributes_price != null && is_array($request->sub_element_distributes_price)) {
                foreach ($request->sub_element_distributes_price as $sub_element_distributes_price) {
                    if ($sub_element_distributes_price['distribute_name']  != null && $sub_element_distributes_price['element_distribute'] != null && $sub_element_distributes_price['sub_element_distribute'] != null) {
                        $distribute = Distribute::where('store_id', $request->store->id)
                            ->where('product_id', $productExists->id)->where('name', $sub_element_distributes_price['distribute_name'])->first();
                        if ($distribute != null) {

                            $element =  ElementDistribute::where('store_id', $request->store->id)
                                ->where('product_id', $productExists->id)
                                ->where('distribute_id',  $distribute->id)
                                ->where('name', $sub_element_distributes_price['element_distribute'])->first();

                            if ($element != null) {
                                $sub =  SubElementDistribute::where('store_id', $request->store->id)
                                    ->where('product_id', $productExists->id)
                                    ->where('distribute_id',  $distribute->id)
                                    ->where('element_distribute_id',  $element->id)
                                    ->where('name', $sub_element_distributes_price['sub_element_distribute'])->first();


                                if ($sub != null) {

                                    $price_sub =
                                        AgencySubDisPrice::where('store_id', $request->store->id)
                                        ->where('product_id', $productExists->id)
                                        ->where('element_distribute_id',    $element->id)
                                        ->where('sub_element_distribute_id',  $sub->id)
                                        ->where('agency_type_id', $request->agency_type_id)->first();

                                    if ($price_sub == null) {
                                        AgencySubDisPrice::create([
                                            "store_id" =>  $request->store->id,
                                            "product_id" => $productExists->id,
                                            "element_distribute_id" => $element->id,
                                            "sub_element_distribute_id" => $sub->id,
                                            "agency_type_id" => $request->agency_type_id,
                                            "price" => $sub_element_distributes_price['price'],
                                        ]);
                                    } else {
                                        $price_sub->update([
                                            "price" => $sub_element_distributes_price['price'],
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        if ($request->element_distributes_price != null && is_array($request->element_distributes_price)) {


            foreach ($request->element_distributes_price as $element_distributes_price) {

                if ($element_distributes_price['distribute_name']  != null && $element_distributes_price['element_distribute'] != null) {

                    $distribute = Distribute::where('store_id', $request->store->id)
                        ->where('product_id', $productExists->id)
                        ->where('name', $element_distributes_price['distribute_name'])->first();

                    if ($distribute != null) {


                        $element =  ElementDistribute::where('store_id', $request->store->id)
                            ->where('product_id', $productExists->id)
                            ->where('distribute_id',  $distribute->id)
                            ->where('name', $element_distributes_price['element_distribute'])->first();

                        if ($element != null) {

                            $ele_price = AgencyEleDisPrice::where('store_id', $request->store->id)
                                ->where('product_id', $productExists->id)
                                ->where('agency_type_id', $request->agency_type_id,)
                                ->where('element_distribute_id', $element->id)->first();


                            if ($ele_price != null) {

                                $ele_price->update([
                                    "price" => $element_distributes_price['price']
                                ]);
                            } else {

                                AgencyEleDisPrice::create([
                                    "store_id" =>  $request->store->id,
                                    "product_id" => $productExists->id,
                                    "element_distribute_id" => $element->id,
                                    "agency_type_id" => $request->agency_type_id,
                                    "price" => $element_distributes_price['price'],
                                ]);
                            }
                        }
                    }
                }
            }
        }


        if ($request->main_price != null) {
            $main_price_exists = AgencyMainPriceOverride::where('product_id', $productExists->id)
                ->where('agency_type_id', $request->agency_type_id,)
                ->where('store_id', $request->store->id)->first();

            if ($main_price_exists != null) {
                $main_price_exists->update([
                    'price' =>  $request->main_price
                ]);
            } else {
                AgencyMainPriceOverride::create([
                    'store_id' =>  $request->store->id,
                    'product_id' =>  $productExists->id,
                    'price' =>  $request->main_price,
                    "agency_type_id" => $request->agency_type_id,
                ]);
            }
        }




        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  [
                "main_price" => ProductUtils::get_main_price_with_agency_type($product_id, $request->agency_type_id, null),
                "distributes" => ProductUtils::get_price_distributes_with_agency_type($product_id,  $request->agency_type_id, null, $request->store->id),
            ]
        ], 200);
    }

    /**
     * Cập nhật giá đại lý nhiều sản phẩm
     * @urlParam  store_code required Store code
     * @bodyParam agency_type_id required agency_type_id
     * @bodyParam main_price double required Giá đại lý thay đổi khi không có distribute
     * @bodyParam element_distributes_price List required [{distribute_name:"Màu", element_distribute:"Đỏ", price:180000}]
     * @bodyParam sub_element_distributes_price List required [{distribute_name:"Màu", element_distribute:"Đỏ", sub_element_distribute:"vàng", price:180000}]
     */
    public function updateListPriceAgency(Request $request, $id)
    {
        $list_agency_price = $request->list_agency_price;

        if (!$list_agency_price || !is_array($list_agency_price['data'])) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_LIST_AGENCY_PRICE_EXISTS[0],
                'msg' => MsgCode::NO_LIST_AGENCY_PRICE_EXISTS[1],
            ], 404);
        }

        $agency_type_exists = AgencyType::where('id', $list_agency_price['agency_type_id'])
            ->where('store_id', $request->store->id)
            ->first();

        if ($agency_type_exists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
            ], 404);
        }

        foreach ($list_agency_price['data'] as $agency_price) {
            $productExists = Product::where('id', $agency_price['product_id'])
                ->where('store_id', $request->store->id)
                ->where('status', '<>', 1)->first();

            if ($productExists == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                    'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
                ], 404);
            }
        }

        foreach ($list_agency_price['data'] as $agency_price) {
            $productExists = Product::where('id', $agency_price['product_id'])
                ->where('store_id', $request->store->id)
                ->where('status', '<>', 1)->first();

            if (ProductUtils::product_has_element_distribute($productExists) == true &&  ProductUtils::product_has_sub_element_distribute($productExists) == true) {

                if ($agency_price['sub_element_distributes_price'] != null && is_array($agency_price['sub_element_distributes_price'])) {
                    foreach ($agency_price['sub_element_distributes_price'] as $sub_element_distributes_price) {
                        if ($sub_element_distributes_price['distribute_name']  != null && $sub_element_distributes_price['element_distribute'] != null && $sub_element_distributes_price['sub_element_distribute'] != null) {
                            $distribute = Distribute::where('store_id', $request->store->id)
                                ->where('product_id', $productExists->id)->where('name', $sub_element_distributes_price['distribute_name'])->first();
                            if ($distribute != null) {

                                $element =  ElementDistribute::where('store_id', $request->store->id)
                                    ->where('product_id', $productExists->id)
                                    ->where('distribute_id',  $distribute->id)
                                    ->where('name', $sub_element_distributes_price['element_distribute'])->first();

                                if ($element != null) {
                                    $sub =  SubElementDistribute::where('store_id', $request->store->id)
                                        ->where('product_id', $productExists->id)
                                        ->where('distribute_id',  $distribute->id)
                                        ->where('element_distribute_id',  $element->id)
                                        ->where('name', $sub_element_distributes_price['sub_element_distribute'])->first();


                                    if ($sub != null) {

                                        $price_sub =
                                            AgencySubDisPrice::where('store_id', $request->store->id)
                                            ->where('product_id', $productExists->id)
                                            ->where('element_distribute_id',    $element->id)
                                            ->where('sub_element_distribute_id',  $sub->id)
                                            ->where('agency_type_id', $list_agency_price['agency_type_id'])->first();

                                        if ($price_sub == null) {
                                            AgencySubDisPrice::create([
                                                "store_id" =>  $request->store->id,
                                                "product_id" => $productExists->id,
                                                "element_distribute_id" => $element->id,
                                                "sub_element_distribute_id" => $sub->id,
                                                "agency_type_id" => $list_agency_price['agency_type_id'],
                                                "price" => $sub_element_distributes_price['price'],
                                            ]);
                                        } else {
                                            $price_sub->update([
                                                "price" => $sub_element_distributes_price['price'],
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }


            if ($agency_price['element_distributes_price'] != null && is_array($agency_price['element_distributes_price'])) {


                foreach ($agency_price['element_distributes_price'] as $element_distributes_price) {

                    if ($element_distributes_price['distribute_name']  != null && $element_distributes_price['element_distribute'] != null) {

                        $distribute = Distribute::where('store_id', $request->store->id)
                            ->where('product_id', $productExists->id)
                            ->where('name', $element_distributes_price['distribute_name'])->first();

                        if ($distribute != null) {


                            $element =  ElementDistribute::where('store_id', $request->store->id)
                                ->where('product_id', $productExists->id)
                                ->where('distribute_id',  $distribute->id)
                                ->where('name', $element_distributes_price['element_distribute'])->first();

                            if ($element != null) {

                                $ele_price = AgencyEleDisPrice::where('store_id', $request->store->id)
                                    ->where('product_id', $productExists->id)
                                    ->where('agency_type_id', $list_agency_price['agency_type_id'])
                                    ->where('element_distribute_id', $element->id)->first();


                                if ($ele_price != null) {

                                    $ele_price->update([
                                        "price" => $element_distributes_price['price']
                                    ]);
                                } else {

                                    AgencyEleDisPrice::create([
                                        "store_id" =>  $request->store->id,
                                        "product_id" => $productExists->id,
                                        "element_distribute_id" => $element->id,
                                        "agency_type_id" => $list_agency_price['agency_type_id'],
                                        "price" => $element_distributes_price['price'],
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            $agency_price_main = $agency_price['main_price'] ?? null;
            if ($agency_price_main !== null) {
                $main_price_exists = AgencyMainPriceOverride::where('product_id', $productExists->id)
                    ->where('agency_type_id', $list_agency_price['agency_type_id'])
                    ->where('store_id', $request->store->id)->first();

                if ($main_price_exists != null) {
                    $main_price_exists->update([
                        'price' =>  $agency_price_main
                    ]);
                } else {
                    AgencyMainPriceOverride::create([
                        'store_id' =>  $request->store->id,
                        'product_id' =>  $productExists->id,
                        'price' =>  $agency_price_main,
                        "agency_type_id" => $list_agency_price['agency_type_id'],
                    ]);
                }
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ], 200);
    }


    /**
     * Lấy giá đại lý theo tầng
     * @urlParam  store_code required Store code
     * @bodyParam agency_type_id double required agency_type_id
     */
    public function getPriceAgency(Request $request, $id)
    {

        $product_id = request("product_id");
        $agency_type_id = (int) $request->input('agency_type_id');

        $product = Product::where('store_id', $request->store->id)->where('id', $product_id)->first();

        if ($product == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  [
                "main_price" => ProductUtils::get_main_price_with_agency_type($product_id,  $agency_type_id, $product->price),
                "distributes" => ProductUtils::get_price_distributes_with_agency_type($product_id,  $agency_type_id, null, $request->store->id),
                "default_distributes" => ProductUtils::get_price_distributes_with_agency_type($product_id, null, null, $request->store->id),
            ]
        ], 200);
    }
}
