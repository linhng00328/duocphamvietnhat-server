<?php

namespace App\Http\Controllers\Api\User\Ecommerce\Product;

use App\Helper\Ecommerce\LazadaUtils;
use App\Helper\Ecommerce\ShopeeUtils;
use App\Helper\Ecommerce\TikiUtils;
use App\Helper\Helper;
use App\Helper\ProductUtils;
use App\Helper\StringUtils;
use App\Http\Controllers\Api\User\Ecommerce\Connect\LazadaController;
use App\Http\Controllers\Api\User\Ecommerce\Connect\ShopeeController;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\EcommercePlatform;
use App\Models\EcommerceProduct;
use App\Models\EcommerceWarehouses;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * @group  User/Kết nối sàn
 */
class SyncProductController extends Controller
{


    function getListProduct($request)
    {
        $shop_ids = request("shop_ids") == null ? [] : explode(',', request("shop_ids"));
        $name = request('name');
        $sku = request('sku');
        $sku_pair_type = request('sku_pair_type'); // 0 null tất cả, 1 chưa ghép với ikitech, 2 đã ghép với ikitech

        $datas = EcommerceProduct::where('store_id', $request->store->id)
            ->when($sku  != null, function ($query) use ($sku) {
                $query->where('sku_in_ecommerce', 'like', '%' . $sku . '%');
            })->when($name  != null, function ($query) use ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            })->whereIn('shop_id',   $shop_ids);;

        $allSkusEcom = $datas->pluck('sku_in_ecommerce')->toArray();

        $merged_skus = array();
        $un_merge_skus = array();

        //Tìm danh sách sku của ecom có trên iki
        $arrHas = DB::table('products')->where('store_id', $request->store->id)->where('status', '<>', 1)->whereIn('sku',  $allSkusEcom)->pluck('sku')->toArray();
        //Lên danh sách cần lấy
        foreach ($allSkusEcom  as $sk) {

            if (in_array($sk, $arrHas)) { //đã merge
                array_push($merged_skus, $sk);
            }

            if (!in_array($sk, $arrHas)) { //chưa merge
                array_push($un_merge_skus, $sk);
            }
        }

        $total_sku = (clone $datas)->count();
        $total_merged = (clone $datas)->whereIn('sku_in_ecommerce', $merged_skus)->count();
        $total_un_merge = (clone $datas)->whereIn('sku_in_ecommerce', $un_merge_skus)->count();

        $list  =    $datas
            ->when($sku_pair_type == 1, function ($query) use ($un_merge_skus) {
                $query->whereIn('sku_in_ecommerce', $un_merge_skus);
            })
            ->when($sku_pair_type == 2, function ($query) use ($merged_skus) {
                $query->whereIn('sku_in_ecommerce', $merged_skus);
            })
            ->get()
            ->take(request('limit') == null ? 20 : request('limit'))
            ->groupBy('parent_product_id_in_ecommerce');

        $arr_response  = array();

        foreach ($list as $key => $items) {
            //san pham khong co master id
            if (empty($key) && $key === "") {
                foreach ($items as $key => $item) {
                    $listImages = json_decode($item->json_images);

                    if (!isset($arr_response[$item->product_id_in_ecommerce])) {
                        $arr_response[$item->product_id_in_ecommerce] = [
                            "name" => $item->name,
                            "name_str_filter" => $item->name_str_filter,
                            "parent_sku_in_ecommerce" => $item->parent_sku_in_ecommerce,
                            "parent_product_id_in_ecommerce" => $item->parent_product_id_in_ecommerce,
                            "min_price" => $item->min_price,
                            "max_price" => $item->max_price,
                            "product_id_in_ecommerce" => $item->product_id_in_ecommerce,
                            'main_image' => isset($listImages[0]) ? $listImages[0] : null,
                            'sku_in_ecommerce' => $item->sku_in_ecommerce,
                            "children" => [
                                $item
                            ]
                        ];
                    } else {
                        array_push($arr_response[$item->product_id_in_ecommerce]['children'], $item);
                    }
                }
            }

            //san pham  co master id
            if (!empty($key) && strlen($key) > 1) {
                $children = array();
                $mainImage = null;
                foreach ($items as $key => $item) {
                    $listImages = json_decode($item->json_images);
                    $mainImage = isset($listImages[0]) ? $listImages[0] : null;
                    array_push($children,  $item);
                }

                array_push($arr_response, [
                    "name" => $item->name,
                    "name_str_filter" => $item->name_str_filter,
                    "parent_sku_in_ecommerce" => $item->parent_sku_in_ecommerce,
                    "parent_product_id_in_ecommerce" => $item->parent_product_id_in_ecommerce,
                    "max_price" => $item->max_price,
                    "min_price" => $item->min_price,
                    "product_id_in_ecommerce" => $item->product_id_in_ecommerce,
                    'main_image' => $mainImage,
                    'sku_in_ecommerce' => $item->sku_in_ecommerce,
                    "children" =>   $children
                ]);
            }
        }

        $arr_response = array_values($arr_response);

        $custom = collect(
            [
                'total_merged' =>  $total_merged,
                'total_un_merge' =>  $total_un_merge,
                'total_sku' => $total_sku
            ]
        );

        $list_res  = Helper::paginateArr($arr_response);
        $list_res = $custom->merge($list_res);

        return [
            "list_res" =>  $list_res,
            "merged_skus" => $merged_skus,
        ];
    }
    /**
     * Danh sách sản phẩm
     * 
     * @queryParam shop_ids  Id của shop
     * @queryParam sku  sku của sản phẩm
     * @queryParam name  tên sản phẩm
     * 
     */
    public function getAllProductEcommerce(Request $request)
    {
        $branch_id = request("branch_id");

        $branchExists = Branch::where('store_id', $request->store->id)
            ->where('id', $branch_id)
            ->first();

        if ($branchExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_BRANCH_EXISTS[0],
                'msg' => MsgCode::NO_BRANCH_EXISTS[1],
            ], 400);
        }

        $data2 = $this->getListProduct($request);

        $list_res = $data2['list_res'];
        $merged_skus = $data2['merged_skus'];

        foreach ($list_res['data'] as $itemParent) {
            foreach ($itemParent['children'] as $item) {

                $stock = null;

                if (in_array($item['sku_in_ecommerce'], $merged_skus)) { //chưa merge

                    $pro = DB::table('products')->where('store_id', $request->store->id)->where('status', '<>', 1)->where('sku',  $item['sku_in_ecommerce'])->first();
                    $stock =   ProductUtils::get_main_stock_with_branch_ids($pro->id, [$branch_id], 0) ?? null;
                }

                $item['quantity_in_stock'] =  $stock;
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $list_res
        ], 200);
    }


    /**
     * 
     * Lưu sản phẩm từ sàn về IKI
     * 
     * @queryParam shop_ids  Id của shop
     * @queryParam sku  sku của sản phẩm
     * @queryParam name  tên sản phẩm
     * @bodyParam override true sẽ xóa sp cũ và thay lại thông tin mới theo SKU, false sẽ bỏ qua sản phẩm đã có sku
     * 
     * 
     */
    public function saveProductToDB(Request $request)
    {
        $branch_id = request("branch_id");

        $branchExists = Branch::where('store_id', $request->store->id)
            ->where('id', $branch_id)
            ->first();

        if ($branchExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_BRANCH_EXISTS[0],
                'msg' => MsgCode::NO_BRANCH_EXISTS[1],
            ], 400);
        }

        $data2 = $this->getListProduct($request);


        $list_res = $data2['list_res'];

        $list_product =  $list_res['data'];
        $total_override = 0;
        $total_success_save = 0;

        foreach ($list_product as $item_pro_ecom) {

            $productExists = Product::where('store_id', $request->store_id)->where('sku', $item_pro_ecom['sku_in_ecommerce'])->first();

            if ($request->override == true) {

                if ($productExists != null) {
                    $productExists->delete();
                    $total_override += 1;
                }

            
                $productCreate = Product::create(
                    [
                        'sku' => $item_pro_ecom['sku_in_ecommerce'],
                        'content_for_collaborator' => 0,
                        'description' => "",
                        'name' => $item_pro_ecom['name'],
                        'name_str_filter' => StringUtils::convert_name_lowcase($item_pro_ecom['name']),
                        'index_image_avatar' => 0,
                        'store_id' => $request->store->id,
                        'video_url' => null,
                        'price' => $item_pro_ecom['min_price'],
                        'import_price' => $item_pro_ecom['min_price'],
                        'percent_collaborator' => 0,
                        'type_share_collaborator_number' => 0,
                        'money_amount_collaborator' =>  0,
                        'barcode' => null,
                        'status' => 0,
                        'quantity_in_stock' => 0,
                        'json_list_promotion' =>  null,
                        'seo_title' => null,
                        'seo_description' =>  null,
                        'check_inventory' => false,
                        'point_for_agency' => 0,
                        'weight' => null,
                        'shelf_position' => null,
                    ]
                );

                $total_success_save += 1;
            }
        }

        $list_res['total_override'] =  $total_override;
        $list_res['total_success_save'] =  $total_success_save;


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $list_res
        ], 200);
    }

    /**
     * Lấy 1 sản phẩm
     * 
     * @queryParam shop_id  Id của shop
     * @queryParam sku  sku của sản phẩm
     * @queryParam name  tên sản phẩm
     * 
     */
    public function getOneProductEcommerce(Request $request)
    {
        $shop_id = request('shop_id');
        $ecommerce_product_id = request('ecommerce_product_id');
        $sku = request('sku');

        $productExists = EcommerceProduct::where([
            ['id', $ecommerce_product_id]
        ])
            ->when($shop_id  != null, function ($query) use ($shop_id) {
                $query->where('shop_id', $shop_id);
            })
            ->when($sku  != null, function ($query) use ($sku) {
                $query->where('sku', 'like', '%' . $sku . '%');
            })
            ->first();

        if ($productExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $productExists
        ], 200);
    }


    /**
     * Đồng bộ sản phẩm từ các sàn
     * 
     * @bodyParam shop_ids  Danh sách id của shop
     * @bodyParam page 
     */

    public function syncProductEcommerce(Request $request)
    {
        $now = Helper::getTimeNowDateTime();
        if ($request->page == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Thiếu page",
            ], 400);
        }

        $shop_ids = is_array($request->shop_ids) ?  $request->shop_ids : array();
        $ecommercePlatforms = EcommercePlatform::where('store_id',  $request->store->id)
            ->when(count($shop_ids) > 0, function ($query) use ($shop_ids) {
                $query->whereIn('shop_id',   $shop_ids);
            })
            ->get();
        $total_in_page = 0;
        $sync_updated = 0;
        $sync_created = 0;

        foreach ($ecommercePlatforms as $ecommercePlatform) {
            if ($ecommercePlatform->platform == "TIKI") {
                try {
                    $dataTiki = (TikiUtils::getProducts($ecommercePlatform->token, $request->page));


                    $total_in_page  += (count($dataTiki->data));
                    foreach ($dataTiki->data as $item) {

                        $data = [
                            "store_id" => $request->store->id,
                            "name" => $item->name,
                            "name_str_filter" => StringUtils::convert_name_lowcase($item->name),
                            "sku_in_ecommerce" => $item->master_sku,
                            "product_id_in_ecommerce" => $item->master_id,
                            "video_url" => null,
                            "description" => null,
                            "index_image_avatar" => null,
                            "price" => $item->price,
                            "import_price" => $item->price,
                            "check_inventory" => 0,
                            "quantity_in_stock" => 0,
                            "percent_collaborator" => 0,
                            "min_price" => $item->price,
                            "max_price" => $item->price,
                            "barcode" => null,
                            "status" => 0,
                            "json_images" => json_encode([$item->thumbnail]),
                            "from_platform" => 'TIKI',
                            "shop_id" => $ecommercePlatform->shop_id,
                            "shop_name" => $ecommercePlatform->name,
                            "is_element" => empty($item->super_sku) ? false : true,
                            "is_sub_element" => empty($item->super_sku) ? false : true,
                            "parent_product_id_in_ecommerce" => empty($item->super_sku) ? null : $item->super_id,
                            "parent_sku_in_ecommerce" => empty($item->super_sku) ? null : $item->super_sku,
                            "code" => null,
                        ];

                        if (!empty($item->super_sku)) {
                            $data["is_element"] = true;
                            $data["is_sub_element"] = true;
                            $data["parent_product_id_in_ecommerce"] = $item->super_id;
                            $data["parent_sku_in_ecommerce"] = $item->super_sku;
                        }

                        if (empty($item->super_sku)) {
                            $data["is_element"] = false;
                            $data["is_sub_element"] = false;
                        }



                        $ecommerceProductExists  = EcommerceProduct::where('product_id_in_ecommerce', $data["product_id_in_ecommerce"])->where('store_id', $request->store->id)->first();
                        if ($ecommerceProductExists   != null) {
                            $ecommerceProductExists->update($data);
                            $sync_updated += 1;
                        } else {
                            $ecommerceProductExists  =   EcommerceProduct::create($data);
                            $sync_created += 1;
                        }
                    }
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            }

            if ($ecommercePlatform->platform == "LAZADA") {
                $isGetSuccess = false;
                $idx = 0;
                $totalSkus = 0;
                do {
                    try {
                        $dataLazada = (LazadaUtils::getProducts($ecommercePlatform->token, $request->page))->data;
                        $total_in_page  += (count($dataLazada->products));
                        foreach ($dataLazada->products as $item) {
                            $totalSkus = count($item->skus);
                            foreach ($item->skus as $itemSku) {
                                $data = [
                                    "store_id" => $request->store->id,
                                    "product_id_in_ecommerce" => $item->item_id,
                                    "name" => $item->attributes->name,
                                    "name_str_filter" => StringUtils::convert_name_lowcase($item->attributes->name),
                                    "sku_in_ecommerce" => $itemSku->SkuId,
                                    "video_url" => null,
                                    "description" => null,
                                    "index_image_avatar" => null,
                                    "price" => $itemSku->price,
                                    "import_price" => $itemSku->price,
                                    "check_inventory" => 0,
                                    "quantity_in_stock" => 0,
                                    "percent_collaborator" => 0,
                                    "min_price" => $itemSku->price,
                                    "max_price" => $itemSku->price,
                                    "barcode" => null,
                                    "status" => 0,
                                    "json_images" => json_encode($item->images),
                                    "from_platform" => 'LAZADA',
                                    "shop_id" => $ecommercePlatform->shop_id,
                                    "shop_name" => $ecommercePlatform->name,
                                    "code" => null,

                                    "is_element" => $totalSkus > 1 ? false : true,
                                    "is_sub_element" => $totalSkus > 1 ? false : true,
                                    "parent_product_id_in_ecommerce" => $totalSkus > 1 ? null : $itemSku->item_id,
                                    "parent_sku_in_ecommerce" => $totalSkus > 1 ? null : $itemSku->SkuId,
                                    "code" => null,
                                ];

                                if ($totalSkus > 1) {
                                    $data["is_element"] = true;
                                    $data["is_sub_element"] = true;
                                    $data["parent_product_id_in_ecommerce"] = $itemSku->item_id;
                                    $data["parent_sku_in_ecommerce"] = $itemSku->SkuId;
                                }

                                if ($totalSkus == 1) {
                                    $data["is_element"] = false;
                                    $data["is_sub_element"] = false;
                                }


                                // $ecommerceProductExists  = EcommerceProduct::where('sku',  $itemSku->SkuId)->where('store_id', $request->store->id)->first();
                                // if ($ecommerceProductExists   != null) {
                                //     $ecommerceProductExists->update($data);
                                //     $sync_updated += 1;
                                // } else {
                                //     EcommerceProduct::create($data);
                                //     $sync_created += 1;
                                // }
                                $ecommerceProductExists  = EcommerceProduct::where('product_id_in_ecommerce', $data["product_id_in_ecommerce"])->where('store_id', $request->store->id)->first();
                                if ($ecommerceProductExists   != null) {
                                    $ecommerceProductExists->update($data);
                                    $sync_updated += 1;
                                } else {
                                    $ecommerceProductExists  =   EcommerceProduct::create($data);
                                    $sync_created += 1;
                                }
                            }
                        }
                        LazadaController::refresh_token($ecommercePlatform->store_id, $ecommercePlatform->seller_id, $ecommercePlatform->refresh_token);
                        $idx++;
                        $isGetSuccess = false;
                    } catch (Exception $e) {
                        if ($idx > 0) {
                            dd($e->getMessage());
                        }
                        $isGetSuccess = true;
                        $idx++;
                    }
                } while ($isGetSuccess);
            }

            if ($ecommercePlatform->platform == "SHOPEE") {
                try {
                    if ($ecommercePlatform->expiry_token < $now) {
                        ShopeeController::refresh_token($ecommercePlatform);
                    }
                    $dataShopee = (ShopeeUtils::getProducts($ecommercePlatform, $request->page));

                    $total_in_page  += (count($dataShopee->item_list));

                    foreach ($dataShopee->item_list as $item) {
                        if (isset($item->variation_item)) {
                            $variationStr = $item->item_name;

                            foreach ($item->variation_item->model as $variationItemModel) {
                                $variationStr = $item->item_name;
                                foreach ($variationItemModel->tier_index as $keyTierIndex => $tierIndex) {
                                    $variationStr .= " " . $item->variation_item->tier_variation[$keyTierIndex]->name . " " . $item->variation_item->tier_variation[$keyTierIndex]->option_list[$tierIndex]->option;
                                }

                                $data = [
                                    "store_id" => $request->store->id,
                                    "name" => $variationStr,
                                    "name_str_filter" => StringUtils::convert_name_lowcase($variationStr),
                                    "sku_in_ecommerce" => $variationItemModel->model_sku,
                                    "product_id_in_ecommerce" => $variationItemModel->model_id,
                                    "video_url" => null,
                                    "description" => $item->description,
                                    "index_image_avatar" => null,
                                    "price" => isset($item->price_info) ? $item->price_info[0]->current_price : 0,
                                    "import_price" => isset($item->price_info) ? $item->price_info[0]->current_price : 0,
                                    "percent_collaborator" => 0,
                                    "min_price" => isset($item->price_info) ? $item->price_info[0]->current_price : 0,
                                    "max_price" => isset($item->price_info) ? $item->price_info[0]->current_price : 0,
                                    "barcode" => null,
                                    "status" => 0,
                                    "json_images" => json_encode($item->image->image_url_list),
                                    "from_platform" => 'SHOPEE',
                                    "shop_id" => $ecommercePlatform->shop_id,
                                    "shop_name" => $ecommercePlatform->name,
                                    "is_element" => true,
                                    "is_sub_element" => true,
                                    "parent_product_id_in_ecommerce" => $item->item_id,
                                    "parent_sku_in_ecommerce" => $item->item_sku,
                                    "code" => null,
                                ];

                                $ecommerceProductExists  = EcommerceProduct::where('product_id_in_ecommerce', $data["product_id_in_ecommerce"])->where('store_id', $request->store->id)->first();
                                if ($ecommerceProductExists   != null) {
                                    $ecommerceProductExists->update($data);
                                    $sync_updated += 1;
                                } else {
                                    $ecommerceProductExists  =   EcommerceProduct::create($data);
                                    $sync_created += 1;
                                }
                            }
                        } else {
                            $data = [
                                "store_id" => $request->store->id,
                                "name" => $item->item_name,
                                "name_str_filter" => StringUtils::convert_name_lowcase($item->item_name),
                                "sku_in_ecommerce" => $item->item_sku,
                                "product_id_in_ecommerce" => $item->item_id,
                                "video_url" => null,
                                "description" => $item->description,
                                "index_image_avatar" => null,
                                "price" => isset($item->price_info) ? $item->price_info[0]->current_price : 0,
                                "import_price" => isset($item->price_info) ? $item->price_info[0]->current_price : 0,
                                "percent_collaborator" => 0,
                                "min_price" => isset($item->price_info) ? $item->price_info[0]->current_price : 0,
                                "max_price" => isset($item->price_info) ? $item->price_info[0]->current_price : 0,
                                "barcode" => null,
                                "status" => 0,
                                "json_images" => json_encode($item->image->image_url_list),
                                "from_platform" => 'SHOPEE',
                                "shop_id" => $ecommercePlatform->shop_id,
                                "shop_name" => $ecommercePlatform->name,
                                "is_element" => false,
                                "is_sub_element" => false,
                                "parent_product_id_in_ecommerce" => null,
                                "parent_sku_in_ecommerce" => null,
                                "code" => null,
                            ];

                            if (!empty($item->super_sku)) {
                                $data["is_element"] = true;
                                $data["is_sub_element"] = true;
                                $data["parent_product_id_in_ecommerce"] = $item->super_id;
                                $data["parent_sku_in_ecommerce"] = $item->super_sku;
                            }

                            if (empty($item->super_sku)) {
                                $data["is_element"] = false;
                                $data["is_sub_element"] = false;
                            }

                            $ecommerceProductExists  = EcommerceProduct::where('product_id_in_ecommerce', $data["product_id_in_ecommerce"])->where('store_id', $request->store->id)->first();
                            if ($ecommerceProductExists   != null) {
                                $ecommerceProductExists->update($data);
                                $sync_updated += 1;
                            } else {
                                $ecommerceProductExists  =   EcommerceProduct::create($data);
                                $sync_created += 1;
                            }
                        }
                    }
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                'total_in_page' => $total_in_page,
                'sync_created' => $sync_created,
                'sync_updated' => $sync_updated,
            ],
        ], 200);
    }

    /**
     * Cập nhật sản phẩm từ các sàn
     * 
     * @bodyParam shop_id  Danh sách id của shop
     * @bodyParam page 
     * @bodyParam price 
     * @bodyParam stock 
     * 
     */

    public function editProductEcommerce(Request $request)
    {
        $ecommerceProductExists = EcommerceProduct::where('id', $request->product_id)->where('store_id', $request->store->id)->first();

        if ($ecommerceProductExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }

        $shop_id =  $ecommerceProductExists->shop_id;

        $ecommercePlatformExists = EcommercePlatform::where('store_id',  $request->store->id)
            ->where('shop_id', $shop_id)
            ->first();

        if ($ecommercePlatformExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[0],
                'msg' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[1],
            ], 400);
        }

        if ($ecommercePlatformExists->platform == "TIKI") {
            try {

                $data = [
                    'original_sku' => $ecommerceProductExists->sku_in_ecommerce,
                    "price" => $request->price,
                ];
                $arr_seller_warehouse  = [];
                $arr_warehouse_quantities  = [];

                if ($request->stock != null) {
                    $list = EcommerceWarehouses::where('store_id',  $request->store->id)
                        ->where('shop_id',  $shop_id)->get();

                    foreach ($list as $item) {
                        if ($item->allow_sync) {
                            array_push($arr_seller_warehouse, $item->code);
                            array_push($arr_warehouse_quantities, [
                                'warehouse_id' => $item->code,
                                'qty_available' => $request->stock
                            ]);
                        }
                    }

                    $arr_seller_warehouse = str_replace(" ", "", implode(", ", $arr_seller_warehouse));

                    $data['seller_warehouse'] = $arr_seller_warehouse;
                    $data['warehouse_quantities'] = $arr_warehouse_quantities;
                }


                $ecommerceProductExists->update([
                    "price" => $request->price,
                    "import_price" => $request->price,
                    "min_price" => $request->price,
                    "max_price" => $request->price,

                ]);

                TikiUtils::updateProduct($ecommercePlatformExists->token, $data);
            } catch (Exception $e) {

                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }

        if ($ecommercePlatformExists->platform == "LAZADA") {
            try {
                $ecommerceProductExists->update([
                    "price" => $request->price,
                    "import_price" => $request->price,
                    "min_price" => $request->price,
                    "max_price" => $request->price,
                    // "quantity_in_stock" => $request->quantity != null && $request->quantity >= 0 ? $request->quantity : $ecommerceProductExists->quantity_in_stock,
                ]);

                LazadaUtils::updatePriceQuantityProduct($ecommercePlatformExists->token, $ecommerceProductExists);
            } catch (Exception $e) {

                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }
        if ($ecommercePlatformExists->platform == "SHOPEE") {
            try {
                $ecommerceProductExists->update([
                    "price" => $request->price,
                    "import_price" => $request->price,
                    "min_price" => $request->price,
                    "max_price" => $request->price,
                    // "quantity_in_stock" => $request->quantity != null && $request->quantity >= 0 ? $request->quantity : $ecommerceProductExists->quantity_in_stock,
                ]);

                ShopeeUtils::updatePriceProduct($ecommercePlatformExists->token, $ecommerceProductExists);
                if ($request->quantity != null) {
                }
            } catch (Exception $e) {

                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $ecommerceProductExists
        ], 200);
    }

    /**
     * Đồng bộ sản phẩm từ các sàn
     * 
     * @bodyParam shop_id  Danh sách id của shop
     * @bodyParam page 
     * @bodyParam price 
     * 
     */

    public function deleteProductEcommerce(Request $request)
    {
        $listProductId = explode(',', $request->list_product_id);
        $shopId = null;
        $listEcommerceProductExists = EcommerceProduct::whereIn('id', $listProductId)->where('store_id', $request->store->id)->get();


        if (count($listEcommerceProductExists) != count($listProductId)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_PRODUCT_ID[0],
                'msg' => MsgCode::INVALID_LIST_PRODUCT_ID[1],
            ], 400);
        }

        foreach ($listEcommerceProductExists as $key => $productItem) {
            if ($shopId != null && $productItem->shop_id != $shopId) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => 1,
                    'msg' => MsgCode::INVALID_LIST_PRODUCT_ID[1],
                ], 400);
            }
            $shopId = $productItem->shop_id;
        }

        $ecommercePlatformExists = EcommercePlatform::where('store_id',  $request->store->id)
            ->where('shop_id', $shopId)
            ->first();

        if ($ecommercePlatformExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[0],
                'msg' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[1],
            ], 400);
        }

        if ($ecommercePlatformExists->platform == "LAZADA") {
            try {
                LazadaUtils::deleteProduct($ecommercePlatformExists->token, $listEcommerceProductExists);
            } catch (Exception $e) {

                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            // 'data' =>  
        ], 200);
    }
}
