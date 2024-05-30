<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\DefineCompare;
use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\ProductUtils;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\SendToWebHookUtils;
use App\Helper\StringUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Models\Attribute;
use App\Models\AttributeSearch;
use App\Models\AttributeSearchChild;
use App\Models\Category;
use App\Models\CategoryChild;
use App\Models\Distribute;
use App\Models\ElementDistribute;
use App\Models\MsgCode;
use App\Models\ProAttSearchChild;
use App\Models\Product;
use App\Models\ProductDistribute;
use App\Models\ProductCategory;
use App\Models\ProductCategoryChild;
use App\Models\ProductImage;
use App\Models\ProductRetailStep;
use App\Models\SubElementDistribute;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @group  User/Sản phẩm
 */
class ProductController extends Controller
{

    /**
     * Thêm nhiều sản phẩm
     * 
     * @bodyParam allow_skip_same_name bool required Có bỏ qua sản phẩm trùng tên không (Không bỏ qua sẽ replace sản phẩm trùng tên)
     * @bodyParam list List required List danh sách sản phẩm  (item json như thêm 1 product)
     * @bodyParam item product thêm {category_name, list_category (VD: [ {"name":"tên cha", "childs": ["con1", "con2"]} ] )}
     * @bodyParam category_id danh mục cần thêm vào
     * 
     */
    public function createManyProduct(Request $request, $id)
    {

        $allow_skip_same_name = filter_var($request->allow_skip_same_name, FILTER_VALIDATE_BOOLEAN);

        $list = $request->list;

        $total_products_request = 0;
        $total_skip_same_name = 0;
        $total_changed_same_name = 0;
        $total_failed = 0;
        $total_new_add = 0;

        $category_import = Category::where('id', $request->category_id)
            ->where('store_id', $request->store->id)->first();

        function getInfoEleDetributeByName($list_distribute_request, $nameEle)
        {
            if ($list_distribute_request != null && is_array($list_distribute_request) && count($list_distribute_request) > 0) {
                //Distribute
                foreach ($list_distribute_request as $distribute) {
                    if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {

                        foreach ($distribute["element_distributes"] as $element_distribute) {

                            if (isset($element_distribute['name']) &&  $element_distribute['name'] == $nameEle) {
                                return $element_distribute;
                            }
                        }
                    }
                }
            }

            return [
                "name" => "",
                'price' => 0,
                "import_price" => 0,

                'image_url' => "",
                'barcode' => "",
                'quantity_in_stock' => 0
            ];
        }

        function getInfoSubDetributeByName($list_distribute_request, $nameEle, $nameSub)
        {
            if ($list_distribute_request != null && is_array($list_distribute_request) && count($list_distribute_request) > 0) {
                //Distribute
                foreach ($list_distribute_request as $distribute) {
                    if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {

                        foreach ($distribute["element_distributes"] as $element_distribute) {

                            foreach ($element_distribute["sub_element_distributes"] as $subElement) {

                                if (isset($subElement['name'])  && isset($element_distribute['name']) &&  $element_distribute['name'] == $nameEle && $subElement['name'] == $nameSub) {
                                    return $subElement;
                                }
                            }
                        }
                    }
                }
            }

            return [
                "name" => "",
                'price' => 0,
                "import_price" => 0,
                'barcode' => "",
                'quantity_in_stock' => 0
            ];
        }


        function getListEleNameArray($list_distribute_request)
        {
            $arrayEle = array();
            if ($list_distribute_request != null && is_array($list_distribute_request) && count($list_distribute_request) > 0) {
                //Distribute
                foreach ($list_distribute_request as $distribute) {
                    if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {

                        foreach ($distribute["element_distributes"] as $element_distribute) {

                            if ($element_distribute['name'] != null && $element_distribute['name'] != "") {
                                array_push($arrayEle, $element_distribute['name']);
                            }
                        }
                    }
                }
            }
            return   array_unique($arrayEle);
        }

        function getListSubNameArray($list_distribute_request)
        {
            $arrayEle = array();
            if ($list_distribute_request != null && is_array($list_distribute_request) && count($list_distribute_request) > 0) {
                //Distribute
                foreach ($list_distribute_request as $distribute) {
                    if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {

                        foreach ($distribute["element_distributes"] as $element_distribute) {

                            foreach ($element_distribute["sub_element_distributes"] as $subElement) {

                                if (isset($subElement['name'])  && isset($element_distribute['name']) && $element_distribute['name'] != "" && $subElement['name'] != "") {
                                    array_push($arrayEle, $subElement['name']);
                                }
                            }
                        }
                    }
                }
            }
            return   array_unique($arrayEle);
        }

        if (is_array($list) && count($list) > 0) {

            $total_products_request = count($list);

            // //Kiểm tra xem trong file có mã sku nào trùng không
            // $skuOccurrences = collect($list)
            //     ->pluck('sku')
            //     ->filter()
            //     ->duplicates();

            // if ($skuOccurrences) {
            //     return response()->json([
            //         'code' => 400,
            //         'success' => false,
            //         'msg_code' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[0],
            //         'msg' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[1],
            //     ], 400);
            // }


            foreach ($list as $pro) {
                $name = $pro['name'] ?? "";
                $sku = $pro['sku'] ?? "";
                $categories_request = !isset($pro['categories']) ? [] : $pro['categories'];
                $attribute_search_children = !isset($pro['attribute_search_children']) ? [] : $pro['attribute_search_children'];
                $quantity_in_stock_request = $pro['quantity_in_stock'] ?? null;
                $percent_collaborator_request = $pro['percent_collaborator'] ?? null;
                $type_share_collaborator_number_request = $pro['type_share_collaborator_number'] ?? null;
                $money_amount_collaborator_request = $pro['money_amount_collaborator'] ?? null;
                $status_request = $pro['status'] ?? 0;
                $barcode_request = $pro['barcode']  ?? "";
                $price_request = $pro['price'] ?? 0;
                $import_price_request = $pro['import_price'] ?? 0;
                $index_image_avatar_request = 0;
                $content_for_collaborator_request = $pro['content_for_collaborator'] ?? null;
                $description_request = $pro['description'] ?? null;
                $images_request = $pro['images'] ?? null;
                $list_distribute_request = isset($pro['distributes']) ?  $pro['distributes'] : [];
                $list_attribute_request = $pro['list_attribute'] ?? [];
                $category_name_request  = $pro['category_name'] ?? null;
                $list_category  = $pro['list_category'] ?? [];
                $list_attribute_search  = $pro['list_attribute_search'] ?? [];
                $seo_title_request = $pro['seo_title']  ?? "";
                $weight_request = $pro['weight']  ?? "";

                $seo_description_request = $pro['seo_description']  ?? "";
                $point_for_agency_request = $pro['point_for_agency']  ?? "";
                $check_inventory_request = $pro['check_inventory']  ?? false;
                $shelf_position_request = $pro['shelf_position']  ?? "";
                $is_product_retail_step = isset($pro['is_product_retail_step']) ? filter_var($pro['is_product_retail_step'], FILTER_VALIDATE_BOOLEAN) : false;

                if ($name == "") continue;
                try {
                    $productExists = Product::where('name', $name)
                        ->where('store_id', $request->store->id)
                        ->where('status', '<>', 1)
                        ->first();


                    $is_change = false;
                    if ($productExists != null) {

                        if ($allow_skip_same_name == true) {
                            $total_skip_same_name++;
                            continue;
                        } else {

                            $is_change = true;
                        }
                    }

                    foreach ($categories_request as $categoryId) {

                        $checkCategoryExists = Category::where(
                            'id',
                            $categoryId
                        )->where(
                            'store_id',
                            $request->store->id
                        )->first();

                        if (empty($checkCategoryExists)) {
                            return response()->json([
                                'code' => 400,
                                'success' => false,
                                'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                                'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
                            ], 400);
                        }
                    }

                    $quantity_in_stock = -1;
                    if ($quantity_in_stock_request != null && $quantity_in_stock_request >= 0) {
                        $quantity_in_stock = $quantity_in_stock_request;
                    }
                    /////////////////////////////////////////////////////////////

                    if ($percent_collaborator_request != null && ($percent_collaborator_request < 0 || $percent_collaborator_request > 100)) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::INVALID_PERCENT[0],
                            'msg' => MsgCode::INVALID_PERCENT[1],
                        ], 400);
                    }

                    ////////////////////////////////////////////////////////////////
                    if ($is_change == true) {
                        $total_changed_same_name++;
                    } else {
                        $total_new_add++;
                    }

                    if (is_array($description_request)) {
                        $description_request = $description_request[0];
                    }




                    $productCreate = null;
                    if ($productExists != null) {

                        $productCreate = $productExists->update(
                            Helper::sahaRemoveItemArrayIfNullValue([
                                'content_for_collaborator' => $content_for_collaborator_request,
                                'description' =>  $description_request,
                                'name' => $name,
                                'name_str_filter' => StringUtils::convert_name_lowcase($name),
                                'sku' => $sku,
                                'index_image_avatar' => $index_image_avatar_request,
                                'store_id' => $request->store->id,
                                'price' => $price_request,
                                'import_price' => $import_price_request,
                                'percent_collaborator' => $percent_collaborator_request ?? 0,
                                'type_share_collaborator_number' => $type_share_collaborator_number_request ?? 0,
                                'money_amount_collaborator' => $money_amount_collaborator_request ?? 0,
                                'barcode' => $barcode_request,
                                'status' => $status_request,
                                'quantity_in_stock' => $quantity_in_stock,
                                'min_price' => $price_request,
                                'max_price' => $price_request,
                                'seo_title' => $seo_title_request,
                                'point_for_agency' => $point_for_agency_request,
                                'seo_description' => $seo_description_request,
                                'check_inventory' =>  $check_inventory_request,
                                'weight' =>  $weight_request,
                                'shelf_position' => $shelf_position_request,
                                'is_product_retail_step' => $is_product_retail_step,
                            ])

                        );

                        ProAttSearchChild::where('product_id', $productExists->id)
                            ->delete();

                        $productCreate =   $productExists = Product::where(
                            'id',
                            $productExists->id
                        )->first();

                        ProductImage::where('product_id', $productExists->id)
                            ->delete();
                    } else {
                        $productCreate = Product::create(
                            [
                                'content_for_collaborator' => $content_for_collaborator_request,
                                'description' =>  $description_request,
                                'name' => $name,
                                'name_str_filter' => StringUtils::convert_name_lowcase($name),
                                'sku' => $sku,
                                'index_image_avatar' => $index_image_avatar_request,
                                'store_id' => $request->store->id,
                                'price' => $price_request,
                                'import_price' => $import_price_request,
                                'percent_collaborator' => $percent_collaborator_request ?? 0,
                                'type_share_collaborator_number' => $type_share_collaborator_number_request ?? 0,
                                'money_amount_collaborator' => $money_amount_collaborator_request ?? 0,
                                'barcode' => $barcode_request,
                                'status' => $status_request,
                                'quantity_in_stock' => $quantity_in_stock,
                                'min_price' => $price_request,
                                'max_price' => $price_request,
                                'seo_title' => $seo_title_request,
                                'point_for_agency' => $point_for_agency_request,
                                'seo_description' => $seo_description_request,
                                'check_inventory' =>  $check_inventory_request,
                                'weight' =>  $weight_request,
                                'shelf_position' => $shelf_position_request,
                                'is_product_retail_step' => $is_product_retail_step,
                            ]
                        );
                    }

                    if ($attribute_search_children != null && is_array($attribute_search_children)) {

                        foreach ($attribute_search_children as $attr) {

                            $trr =  AttributeSearchChild::where('name', $attr)->where('store_id', $request->store->id)->first();

                            if ($trr != null) {
                                $lkPro = ProAttSearchChild::where('product_id', $productCreate->id)
                                    ->where('attribute_search_child_id', $trr->id)->first();

                                if ($lkPro == null) {
                                    ProAttSearchChild::create(
                                        [
                                            'product_id' => $productCreate->id,
                                            'attribute_search_child_id' => $trr->id
                                        ]
                                    );
                                }
                            }
                        }
                    }


                    if ($images_request !== null && count((array)$images_request) > 0) {

                        foreach ((array)$images_request as $image) {
                            if (isset($image)) {
                                ProductImage::create(
                                    [
                                        'image_url' => $image,
                                        'product_id' => $productCreate->id,
                                    ]
                                );
                            }
                        }
                    }



                    if ($categories_request !== null && count($categories_request) > 0) {

                        foreach ($categories_request as $categoryId) {
                            if (ProductCategory::where('product_id', $productCreate->id)->where('category_id',  $categoryId)->first() == null) {
                                ProductCategory::create(
                                    [
                                        'product_id' => $productCreate->id,
                                        'category_id' => $categoryId
                                    ]
                                );
                            }
                        }
                    }

                    if ($category_import !== null) {
                        if (ProductCategory::where('product_id', $productCreate->id)->where('category_id',  $category_import->id)->first() == null) {
                            ProductCategory::create(
                                [
                                    'product_id' => $productCreate->id,
                                    'category_id' => $category_import->id
                                ]
                            );
                        }
                    }



                    //Xử lý 1 cate
                    if ($category_name_request != null && strlen($category_name_request) > 0) {
                        $cate_db = Category::where('name', $category_name_request)
                            ->where('store_id', $request->store->id)->first();
                        if ($cate_db == null) {
                            $cate_db =   Category::create(
                                [
                                    'store_id' => $request->store->id,
                                    'name' => $category_name_request,
                                ]
                            );
                        }

                        $lkPro = ProductCategory::where('product_id', $productCreate->id)
                            ->where('category_id', $cate_db->id)->first();

                        if ($lkPro == null) {
                            ProductCategory::create(
                                [
                                    'product_id' => $productCreate->id,
                                    'category_id' =>  $cate_db->id
                                ]
                            );
                        }
                    }

                    //Xử lý nhiều cate
                    if ($list_category != null && is_array($list_category)) {


                        foreach ($list_category as $cate) {
                            $name = $cate['name'] ?? "";
                            $childs = $cate['childs'] ?? [];

                            if ($name != "") {
                                $cate_db = Category::where('name', $name)
                                    ->where('store_id', $request->store->id)->first();
                                if ($cate_db == null) {
                                    $cate_db =   Category::create(
                                        [
                                            'store_id' => $request->store->id,
                                            'name' => $name,
                                        ]
                                    );
                                }

                                //Thêm con
                                if ($childs != null && is_array($childs)) {

                                    foreach ($childs as $cateChild) {
                                        $cate_child = CategoryChild::where('name', $cateChild)
                                            ->where('category_id', $cate_db->id)
                                            ->where('store_id', $request->store->id)->first();

                                        if ($cate_child == null) {
                                            $cate_child =   CategoryChild::create(
                                                [
                                                    'store_id' => $request->store->id,
                                                    'name' => $cateChild,
                                                    "category_id" => $cate_db->id
                                                ]
                                            );
                                        }

                                        $lkPro = ProductCategoryChild::where('product_id', $productCreate->id)
                                            ->where('category_children_id', $cate_child->id)->first();

                                        if ($lkPro == null) {
                                            ProductCategoryChild::create(
                                                [
                                                    'product_id' => $productCreate->id,
                                                    'category_children_id' =>  $cate_child->id
                                                ]
                                            );
                                        }
                                    }
                                }
                                /////

                                $lkPro = ProductCategory::where('product_id', $productCreate->id)
                                    ->where('category_id', $cate_db->id)->first();

                                if ($lkPro == null) {
                                    ProductCategory::create(
                                        [
                                            'product_id' => $productCreate->id,
                                            'category_id' =>  $cate_db->id
                                        ]
                                    );
                                }
                            }
                        }
                    }

                    //Xử lý thuộc tính tìm kiếm
                    if ($list_attribute_search != null && is_array($list_attribute_search)) {
                        foreach ($list_attribute_search as $attribute_search) {
                            $name = $attribute_search['name'] ?? "";
                            $childs = $attribute_search['childs'] ?? [];

                            if ($name != "") {
                                $attribute_search_db = AttributeSearch::where('name', $name)
                                    ->where('store_id', $request->store->id)
                                    ->first();

                                if ($attribute_search_db == null) {
                                    $attribute_search_db = AttributeSearch::create([
                                        'store_id' => $request->store->id,
                                        'name' => $name,
                                    ]);
                                }

                                //Thêm con
                                if ($childs != null && is_array($childs)) {

                                    foreach ($childs as $attributeSearchChild) {
                                        $attribute_search_child = AttributeSearchChild::where('name', $attributeSearchChild)
                                            ->where('store_id', $request->store->id)
                                            ->where('attribute_search_id', $attribute_search_db->id)
                                            ->first();

                                        if ($attribute_search_child == null) {
                                            $attribute_search_child = AttributeSearchChild::create([
                                                'name' => $attributeSearchChild,
                                                'store_id' => $request->store->id,
                                                "attribute_search_id" => $attribute_search_db->id
                                            ]);
                                        }

                                        $lkPro = ProAttSearchChild::where('product_id', $productCreate->id)
                                            ->where('attribute_search_child_id', $attribute_search_child->id)
                                            ->first();

                                        if ($lkPro == null) {
                                            ProAttSearchChild::create([
                                                'product_id' => $productCreate->id,
                                                'attribute_search_child_id' =>  $attribute_search_child->id
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }


                    ///   ///   ///   ///   ///   ///

                    try {
                        if ($list_distribute_request != null && is_array($list_distribute_request) && count($list_distribute_request) > 0) {

                            Distribute::where('product_id', $productCreate->id)->delete();
                            ElementDistribute::where('product_id', $productCreate->id)->delete();
                            ProductDistribute::where('product_id', $productCreate->id)->delete();
                            SubElementDistribute::where('product_id', $productCreate->id)->delete();


                            //Distribute
                            foreach ($list_distribute_request as $distribute) {
                                if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {

                                    $distributeCreated = Distribute::create(
                                        [
                                            'product_id' => $productCreate->id,
                                            'store_id' => $request->store->id,
                                            'name' => $distribute["name"] ?? "",
                                            'sub_element_distribute_name' => $distribute["sub_element_distribute_name"] ?? null,
                                        ]
                                    );


                                    $arrSub = [];
                                    //kiểm số lượng sub element chuẩn và định hình phân loại cộng thêm 
                                    if (isset($distribute["element_distributes"][0]) && isset($distribute["element_distributes"][0]["sub_element_distributes"]) && count($distribute["element_distributes"][0]["sub_element_distributes"]) > 0) {

                                        foreach ($distribute["element_distributes"][0]["sub_element_distributes"] as $subElement) {
                                            if (isset($subElement['name'])) {
                                                array_push($arrSub, $subElement['name']);
                                            }
                                        }
                                    }
                                    $arrSub = array_unique($arrSub);


                                    foreach (getListEleNameArray($list_distribute_request) as $nameEle) {


                                        $element_distribute = getInfoEleDetributeByName($list_distribute_request, $nameEle);

                                        $hasEle = ElementDistribute::where('product_id', $productCreate->id)->where('store_id',  $request->store->id)
                                            ->where('name', $element_distribute['name'])->first();

                                        $dataEle = [
                                            'product_id' => $productCreate->id,
                                            'store_id' => $request->store->id,
                                            'name' => $element_distribute['name'],
                                            'image_url' => $element_distribute["image_url"] ?? "",
                                            'distribute_id' => $distributeCreated->id,
                                            'price' => $element_distribute["price"] ?? 0,
                                            'import_price' => $element_distribute["import_price"] ?? 0,
                                            'barcode' => $element_distribute["barcode"] ?? null,
                                            'sku' => $element_distribute["sku"] ?? null,
                                            'quantity_in_stock' => (int)($element_distribute['quantity_in_stock'] ?? 0),
                                        ];
                                        if ($hasEle  != null) {
                                            $hasEle->update($dataEle);
                                        } else {
                                            $element_distribute_created = ElementDistribute::create(
                                                $dataEle
                                            );
                                        }



                                        foreach (getListSubNameArray($list_distribute_request) as $nameSub) {

                                            $itemSub = getInfoSubDetributeByName($list_distribute_request, $nameEle, $nameSub);


                                            $name = "";
                                            $quantity_in_stock = null;
                                            $price = null;
                                            $import_price = null;
                                            $barcode = null;


                                            $name = $itemSub['name'] ?? null;
                                            $quantity_in_stock = (int)($itemSub['quantity_in_stock'] ?? 0);
                                            $price = $itemSub['price'] ?? 0;
                                            $barcode =  $itemSub['barcode'] ?? null;
                                            $sku =  $itemSub['sku'] ?? null;
                                            $import_price =  $itemSub['import_price'] ?? 0;


                                            $hasSub = SubElementDistribute::where('product_id', $productCreate->id)->where('store_id',  $request->store->id)
                                                ->where('distribute_id',  $distributeCreated->id)
                                                ->where('element_distribute_id', $element_distribute_created->id)
                                                ->where('name', $name)->first();

                                            $dataSub =  [
                                                'product_id' => $productCreate->id,
                                                'store_id' => $request->store->id,
                                                'distribute_id' => $distributeCreated->id,
                                                'element_distribute_id' =>  $element_distribute_created->id,
                                                'name' => $name,
                                                'import_price' => $import_price,
                                                'price' => $price,
                                                'barcode' =>   $barcode,
                                                'sku' =>   $sku,
                                                'quantity_in_stock' => $quantity_in_stock,
                                            ];

                                            if ($hasSub  != null) {
                                                $hasSub->update($dataSub);
                                            } else {
                                                SubElementDistribute::create(
                                                    $dataSub
                                                );
                                            }
                                        }
                                    }


                                    ProductDistribute::create(
                                        [
                                            'store_id' => $request->store->id,
                                            'product_id' => $productCreate->id,
                                            'distribute_id' => $distributeCreated->id
                                        ]
                                    );
                                }
                                break;
                            }
                        }
                    } catch (Exception $e) {

                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::ERROR[0],
                            'msg' => $e->getMessage(),
                        ], 400);
                    }


                    if ($list_attribute_request !== null && is_array($list_attribute_request) && count((array)$list_attribute_request) > 0) {

                        Attribute::where('store_id', $request->store->id)
                            ->where('product_id', $productCreate->id)
                            ->delete();
                        foreach ((array)$list_attribute_request as $attribute) {
                            if (isset($attribute["name"]) && isset($attribute["value"]) != null) {
                                $distributeCreated = Attribute::create(
                                    [
                                        'store_id' => $request->store->id,
                                        'product_id' => $productCreate->id,
                                        'name' => $attribute["name"],
                                        'value' => $attribute["value"],
                                    ]
                                );
                            }
                        }
                    }

                    ProductController::update_min_max_product(Product::where('id', $productCreate->id)->first());
                } catch (Exception $e) {
                    $total_failed++;
                }
            }
        }
        PushNotificationAdminJob::dispatch(
            "User ",
            "Vừa thêm " . $total_products_request . " sản phẩm cho store " . $request->store->name,
        );


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "category_imported_name" => $category_import != null ? $category_import->name : null,
                "allow_skip_same_name" => $allow_skip_same_name,
                "total_products_request" => $total_products_request,
                "total_skip_same_name"  => $total_skip_same_name,
                "total_changed_same_name" =>  $total_changed_same_name,
                "total_failed" => $total_failed,
                "total_new_add" =>  $total_new_add,
            ]
        ], 201);
    }

    public function createManyProductInventory(Request $request, $id)
    {
        if (!$request->list || !is_array($request->list)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }

        $products_inventory = [];

        foreach ($request->list as $pro) {
            $name = $pro['name'] ?? "";
            $cost_of_capital = $pro['cost_of_capital'] ?? 0;
            $stock = $pro['stock'] ?? 0;
            $list_distribute_request = isset($pro['distributes']) ?  $pro['distributes'] : [];

            $productExists = Product::where('name', $name)
                ->where('store_id', $request->store->id)
                ->where('status', '<>', 1)
                ->first();

            if (!$productExists) continue;

            if (!$list_distribute_request || !is_array($list_distribute_request)) {
                array_push($products_inventory, [
                    "cost_of_capital" => $cost_of_capital,
                    "product_id" => $productExists->id,
                    "stock" => $stock,
                    "distribute_name" => "",
                    "element_distribute_name" => "",
                    "sub_element_distribute_name" => "",
                    "quantity_in_stock" => null
                ]);
            }

            if (is_array($list_distribute_request) && count($list_distribute_request) > 0) {
                foreach ($list_distribute_request as $distribute) {
                    if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {
                        foreach ($distribute["element_distributes"] as $element_distribute) {
                            if ($element_distribute["name"] == null) continue;

                            if (isset($element_distribute['sub_element_distributes']) && count($element_distribute['sub_element_distributes']) > 0) {
                                foreach ($element_distribute['sub_element_distributes'] as $itemSub) {
                                    if ($itemSub["name"] == null) continue;

                                    array_push($products_inventory, [
                                        "stock" => $itemSub["stock"] ?? 0,
                                        "cost_of_capital" => $itemSub["cost_of_capital"] ?? 0,
                                        "sub_element_distribute_name" => $itemSub["name"],
                                        "product_id" => $productExists->id,
                                        "distribute_name" => $distribute["name"],
                                        "element_distribute_name" => $element_distribute["name"],
                                        "quantity_in_stock" => null
                                    ]);
                                }
                            } else {
                                array_push($products_inventory, [
                                    "cost_of_capital" => $element_distribute["cost_of_capital"] ?? 0,
                                    "product_id" => $productExists->id,
                                    "stock" => $element_distribute["stock"] ?? 0,
                                    "distribute_name" => $distribute["name"],
                                    "element_distribute_name" => $element_distribute["name"],
                                    "sub_element_distribute_name" => "",
                                    "quantity_in_stock" => null
                                ]);
                            }
                        }
                    }
                }
            }
        }

        $request->merge([
            "products_inventory" => $products_inventory
        ]);

        $listInventoryBalance = (new InventoryController)->updateListInventoryBalance($request);

        return $listInventoryBalance;
    }



    static function update_min_max_product($product)
    {

        $distributes = ProductUtils::get_price_distributes_with_agency_type(
            $product->id,
            -1,
            -1,
            $product->store_id,
            Helper::getRandomOrderString()
        );


        if ($distributes == null || count($distributes) == 0) {
            $min_price =   $product->price;
            $max_price =   $product->price;
        } else {


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

            $max_price =  $currentPrice == -1 ? $product->price : $currentPrice;
            //    //   //  // //  //    //   //  // //  //    //   //  // //  //    //   //  // //  //    //   //  // //
            $distributes = ProductUtils::get_price_distributes_with_agency_type(
                $product->id,
                null,
                null,
                $product->store_id
            );


            $main_price =  $product->price ?? 0;


            if ($distributes == null || count($distributes) == 0) {


                $min_price =   $main_price;
            } else {
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


                $min_price =   $currentPrice == -1 ?   $main_price  : $currentPrice;
            }
        }




        $product->update([
            'min_price' => $min_price,
            'max_price' => $max_price
        ]);
    }

    /**
     * Tạo sản phẩm
     * @urlParam  store_code required Store code
     * @bodyParam name string required Tên sản phẩm
     * @bodyParam sku string required Sku
     * @bodyParam description string required Mô tả sản phẩm
     * @bodyParam video_url string required Video sản phẩm
     * @bodyParam content_for_collaborator string required Nội dung mô tả cho cộng tác viên bán
     * @bodyParam price double required Giá sản phẩm
     * @bodyParam import_price double required Giá nhập
     * @bodyParam main_cost_of_capital double giá vốn
     * @bodyParam main_stock int tồn kho ban đầu
     * @bodyParam status int Trạng thái sản phẩm (0 Hiển thị -1 Đang ẩn  1 Đã xóa)
     * @bodyParam barcode string Barcode sản phẩm
     * @bodyParam percent_collaborator double chia se cho CTV
     * @bodyParam type_share_collaborator_number int kiểu số tiền chia sẻ 0 là % 1 là theo số tiền
     * @bodyParam money_amount_collaborator double là số tiền hoa hồng nếu khách chọn 1 (theo số tiền)
     * @bodyParam list_distribute string List chi tiết [  {name:"ten", "sub_element_distribute_name": "Sub ten",element_distributes:[{name:"ten",image_url:"image",price:1000,"import_price":1,"cost_of_capital": 1, "stock": 1,barcode:"123456","price": 1, "quantity_in_stock": 2,},{name:"ten",image_url:"image",price:1000",barcode:"123456",price": 1, "quantity_in_stock": 2,"sub_element_distributes": [ {"name": "XL", "price": 3,barcode:"123456", "quantity_in_stock": 4,"cost_of_capital": 1,"import_price":1, "stock": 1, }]}]}  ] toi da 1 item
     * @bodyParam list_attribute string List chi tiết [  {"name": "Màu","value": "Xanh" }, { "name": "Xuất xứ", "value": "Vàng"}  ]
     * @bodyParam images string List chi tiết [ link1 link2 ]
     * @bodyParam list_promotion List [{content:"Noi dung khuyen mai","post_id":1, "post_name":"ten bai viet"  }]
     * @bodyParam categories List Danh sach danh muc
     * @bodyParam category_children_ids List Danh sach danh muc con
     * @bodyParam seo_title string tiêu đề cho seo
     * @bodyParam seo_description string Mô tả cho seo
     * @bodyParam check_inventory boolean Có kiểm kho hay ko (ko gửi mặc định false)
     * @bodyParam weight double Cân nặng
     * @bodyParam is_medicine boolean sản phẩm có phải là thuốc không
     * 
     * 
     */
    public function create(Request $request)
    {

        $now = Helper::getTimeNowCarbon();
        $name = $request->name;
        $sku_request = $request->sku;
        $categories_request = (array)$request->categories;
        $category_children_ids = (array)$request->category_children_ids;
        $quantity_in_stock_request = $request->quantity_in_stock;
        $percent_collaborator_request = $request->percent_collaborator;
        $type_share_collaborator_number_request = $request->type_share_collaborator_number;
        $money_amount_collaborator_request = $request->money_amount_collaborator;
        $status_request = $request->status ?? 0;
        $barcode_request = $request->barcode;
        $price_request = $request->price;
        $import_price = $request->import_price;
        $index_image_avatar_request = 0;
        $content_for_collaborator_request = $request->content_for_collaborator;
        $description_request = $request->description;
        $images_request = $request->images;
        $list_distribute_request = (array)$request->list_distribute;
        $list_attribute_request = $request->list_attribute;
        $list_promotion_request = $request->list_promotion;
        $weight_request = $request->weight;
        $shelf_position_request = $request->shelf_position;
        $is_medicine = $request->is_medicine;
        $arrProductRetailStep = [];

        if ($request->is_product_retail_step == true && !empty($request->product_retail_steps) && is_array($request->product_retail_steps)) {
            $previousToQuantity = $request->product_retail_steps[0]['from_quantity'];
            foreach ($request->product_retail_steps as $idx => $productRetailStep) {
                if ($productRetailStep['from_quantity'] == null || $productRetailStep['to_quantity'] == null || $productRetailStep['price'] == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_PRODUCT_RETAIL_STEP[0],
                        'msg' => MsgCode::INVALID_PRODUCT_RETAIL_STEP[1],
                    ], 400);
                }

                if ($productRetailStep['from_quantity'] <= $previousToQuantity && $idx != 0) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_PRODUCT_RETAIL_STEP[0],
                        'msg' => MsgCode::INVALID_PRODUCT_RETAIL_STEP[1],
                    ], 400);
                } else {
                    array_push($arrProductRetailStep, [
                        'product_id' => null,
                        'store_id' =>  $request->store->id,
                        'from_quantity' => $productRetailStep['from_quantity'],
                        'to_quantity' => $productRetailStep['to_quantity'],
                        'price' => $productRetailStep['price'],
                        'created_at' => $now->format('Y-m-d H:i:s'),
                        'updated_at' => $now->format('Y-m-d H:i:s'),
                    ]);
                }
                $previousToQuantity = $productRetailStep['to_quantity'];
            }
        }

        if (empty($request->name)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $productExists = Product::where(
            'name',
            $name
        )->where('status', '!=', '1')
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if ($productExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PRODUCT_NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::PRODUCT_NAME_ALREADY_EXISTS[1],
            ], 400);
        }


        // if (StringUtils::description_contains_image_base64($description_request)) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::DESCRIPTION_CANT_IMAGE_BAGE64[0],
        //         'msg' => MsgCode::DESCRIPTION_CANT_IMAGE_BAGE64[1],
        //     ], 400);
        // }

        //
        $productSkuExists = Product::where(
            'sku',
            $sku_request
        )->where('status', '<>', '1')
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if ($productSkuExists != null && !empty($request->sku)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[0],
                'msg' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[1],
            ], 400);
        }

        $productBarcodeExists = Product::where(
            'barcode',
            $barcode_request
        )->where('status', '<>', '1')
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if ($productBarcodeExists != null && !empty($barcode_request)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PRODUCT_BARCODE_ALREADY_EXISTS[0],
                'msg' => MsgCode::PRODUCT_BARCODE_ALREADY_EXISTS[1],
            ], 400);
        }

        $productUrlExists = Product::where(
            'barcode',
            $barcode_request
        )->where('status', '<>', '1')
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if ($productUrlExists != null && !empty($request->product_url)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => "PRODUCT_URL_EXISTS",
                'msg' => "Url này đã được sử dụng",
            ], 400);
        }


        $json_list_promotion = [];
        //Xu ly noi dung promotion
        if ($list_promotion_request != null && is_array($list_promotion_request) && count($list_promotion_request) > 0) {
            foreach ($list_promotion_request as $promotion_request) {
                array_push($json_list_promotion, [
                    "content" => $promotion_request["content"] ?? null,
                    "post_id" => $promotion_request["post_id"] ?? null,
                    "post_name" => $promotion_request["post_name"] ?? null,
                ]);
            }
        }

        foreach ($categories_request as $categoryId) {

            $checkCategoryExists = Category::where(
                'id',
                $categoryId
            )->where(
                'store_id',
                $request->store->id
            )->first();

            if (empty($checkCategoryExists)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                    'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
                ], 400);
            }
        }


        foreach ($category_children_ids as $categoryId) {

            $checkCategoryExists = CategoryChild::where(
                'id',
                $categoryId
            )->where(
                'store_id',
                $request->store->id
            )->first();

            if (empty($checkCategoryExists)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_CATEGORY_CHILD_ID_EXISTS[0],
                    'msg' => MsgCode::NO_CATEGORY_CHILD_ID_EXISTS[1],
                ], 400);
            }
        }



        $quantity_in_stock = -1;
        if ($quantity_in_stock_request != null && $quantity_in_stock_request >= 0) {
            $quantity_in_stock = $quantity_in_stock_request;
        }
        /////////////////////////////////////////////////////////////

        if ($percent_collaborator_request != null && ($percent_collaborator_request < 0 || $percent_collaborator_request > 100)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PERCENT[0],
                'msg' => MsgCode::INVALID_PERCENT[1],
            ], 400);
        }



        $productCreate = Product::create(
            [
                'sku' => $sku_request,
                'content_for_collaborator' => $content_for_collaborator_request,
                'description' => $description_request,
                'name' => $name,
                'name_str_filter' => StringUtils::convert_name_lowcase($name),
                'index_image_avatar' => $index_image_avatar_request,
                'store_id' => $request->store->id,
                'video_url' => $request->video_url,
                'price' => $price_request,
                'import_price' => $import_price,
                'percent_collaborator' => $percent_collaborator_request ?? 0,
                'type_share_collaborator_number' => $type_share_collaborator_number_request ?? 0,
                'money_amount_collaborator' =>  $money_amount_collaborator_request ?? 0,
                'barcode' => $barcode_request,
                'status' => $status_request,
                'quantity_in_stock' => $quantity_in_stock,
                'json_list_promotion' =>  json_encode($json_list_promotion),
                'seo_title' => $request->seo_title,
                'seo_description' => $request->seo_description,
                'check_inventory' =>  $request->check_inventory,
                'point_for_agency' => $request->point_for_agency,
                'weight' => $request->weight,
                'shelf_position' => $request->shelf_position,
                'is_medicine' => $request->is_medicine,
                'is_product_retail_step' => filter_var($request->is_product_retail_step, FILTER_VALIDATE_BOOLEAN) ?? false,
                'canonical_url' => $request->canonical_url,
                'meta_robots_index' => $request->meta_robots_index,
                'meta_robots_follow' => $request->meta_robots_follow,
                'product_url' => $request->product_url ? $request->product_url : Str::slug($request->name),
            ]
        );

        // $slug = Str::slug($name);
        if ($request->product_url == null) {
            $slug = Str::slug($request->name);
        } else {
            $slug = $request->product_url;
        }

        $slugCreate = DB::table('slugs')->insert([
            'type' => 'product',
            'value' => $slug,
        ]);


        if ($request->branch != null) {
            InventoryUtils::update_cost_of_capital_or_stock_by_id(
                $request->store->id,
                $request->branch->id,
                $productCreate->id,
                null,
                null,
                floatval($import_price ?? 0),
                intval($request->main_stock)
            );
        }

        if ($images_request !== null && count((array)$images_request) > 0) {

            foreach ((array)$images_request as $image) {
                if (isset($image)) {
                    ProductImage::create(
                        [
                            'image_url' => $image,
                            'product_id' => $productCreate->id,
                        ]
                    );
                }
            }
        }

        if ($categories_request !== null && count($categories_request) > 0) {

            foreach ($categories_request as $categoryId) {
                if (ProductCategory::where('product_id', $productCreate->id)->where('category_id',  $categoryId)->first() == null) {
                    ProductCategory::create(
                        [
                            'product_id' => $productCreate->id,
                            'category_id' => $categoryId
                        ]
                    );
                }
            }
        }

        if ($category_children_ids !== null && count($category_children_ids) > 0) {

            foreach ($category_children_ids as $categoryId) {

                $checkCategoryExists = CategoryChild::where(
                    'id',
                    $categoryId
                )->where(
                    'store_id',
                    $request->store->id
                )->first();
                $cateParentHas = ProductCategory::where('id', $checkCategoryExists->category_id)->first();

                if ($checkCategoryExists != null) {
                    if ($cateParentHas == null) {
                        ProductCategory::create(
                            [
                                'product_id' => $productCreate->id,
                                'category_id' => $checkCategoryExists->category_id
                            ]
                        );
                    }

                    ProductCategoryChild::create(
                        [
                            'product_id' => $productCreate->id,
                            'category_children_id' => $categoryId
                        ]
                    );
                }
            }
        }

        //Check sku distribute
        foreach ($list_distribute_request as $distribute) {
            if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {
                $arrSub = [];
                //kiểm số lượng sub element chuẩn và định hình phân loại cộng thêm 
                if (isset($distribute["element_distributes"][0]) && isset($distribute["element_distributes"][0]["sub_element_distributes"]) && count($distribute["element_distributes"][0]["sub_element_distributes"]) > 0) {

                    foreach ($distribute["element_distributes"][0]["sub_element_distributes"] as $subElement) {
                        if (isset($subElement['name'])) {
                            array_push($arrSub, $subElement['name']);
                        }
                    }
                }
                $arrSub = array_unique($arrSub);

                foreach ($distribute["element_distributes"] as $element_distribute) {
                    if (isset($element_distribute["sku"])) {
                        $elementDistributeSkuExists = ElementDistribute::where('sku', $element_distribute["sku"])
                            ->where('store_id', $request->store->id)
                            ->first();

                        if ($elementDistributeSkuExists != null) {
                            return response()->json([
                                'code' => 400,
                                'success' => false,
                                'msg_code' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[0],
                                'msg' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[1],
                            ], 400);
                        }
                    }

                    if (count($arrSub) > 0 && isset($element_distribute['sub_element_distributes']) && count($element_distribute['sub_element_distributes']) > 0 && count($element_distribute['sub_element_distributes']) == count($arrSub)) {
                        foreach ($element_distribute['sub_element_distributes'] as $itemSub) {
                            if (isset($itemSub["sku"])) {
                                $subElementDistributeSkuExists = SubElementDistribute::where('sku', $itemSub["sku"])
                                    ->where('store_id', $request->store->id)
                                    ->first();

                                if ($subElementDistributeSkuExists != null) {
                                    return response()->json([
                                        'code' => 400,
                                        'success' => false,
                                        'msg_code' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[0],
                                        'msg' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[1],
                                    ], 400);
                                }
                            }
                        }
                    }
                }
            }
            break;
        }

        //Distribute

        foreach ($list_distribute_request as $distribute) {
            if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {

                $distributeCreated = Distribute::create(
                    [
                        'product_id' => $productCreate->id,
                        'store_id' => $request->store->id,
                        'name' => $distribute["name"],
                        'sub_element_distribute_name' => $distribute["sub_element_distribute_name"] ?? null,
                    ]
                );


                $arrSub = [];
                //kiểm số lượng sub element chuẩn và định hình phân loại cộng thêm 
                if (isset($distribute["element_distributes"][0]) && isset($distribute["element_distributes"][0]["sub_element_distributes"]) && count($distribute["element_distributes"][0]["sub_element_distributes"]) > 0) {

                    foreach ($distribute["element_distributes"][0]["sub_element_distributes"] as $subElement) {
                        if (isset($subElement['name'])) {
                            array_push($arrSub, $subElement['name']);
                        }
                    }
                }
                $arrSub = array_unique($arrSub);

                foreach ($distribute["element_distributes"] as $element_distribute) {
                    $element_distribute_created = ElementDistribute::create(
                        [
                            'product_id' => $productCreate->id,
                            'store_id' => $request->store->id,
                            'name' => $element_distribute['name'],
                            'image_url' => $element_distribute["image_url"],
                            'distribute_id' => $distributeCreated->id,
                            'price' => $element_distribute["price"] ?? 0,
                            'import_price' => $element_distribute['import_price'] ?? 0,
                            'quantity_in_stock' => $element_distribute["quantity_in_stock"] ?? null,
                            'barcode' => $element_distribute["barcode"] ?? null,
                            'sku' => $element_distribute["sku"] ?? null,
                        ]
                    );

                    if ($request->branch != null) {

                        InventoryUtils::update_cost_of_capital_or_stock_by_id(
                            $request->store->id,
                            $request->branch->id,
                            $productCreate->id,
                            $element_distribute_created->id,
                            null,
                            floatval($element_distribute['import_price'] ?? 0),
                            intval($element_distribute['stock'] ?? 0),
                        );
                    }

                    if (count($arrSub) > 0 && isset($element_distribute['sub_element_distributes']) && count($element_distribute['sub_element_distributes']) > 0 && count($element_distribute['sub_element_distributes']) == count($arrSub)) {
                        foreach ($element_distribute['sub_element_distributes'] as $itemSub) {
                            $name = "";
                            $quantity_in_stock = null;
                            $price = 0;

                            if (in_array($itemSub["name"], $arrSub)) {
                                $name = $itemSub['name'] ?? null;
                                $quantity_in_stock = $itemSub['quantity_in_stock'] ?? null;
                                $price = $itemSub['price'] ?? 0;
                                $barcode = $itemSub['barcode'] ?? null;
                                $sku = $itemSub['sku'] ?? null;
                            }

                            $subEleCreate = SubElementDistribute::create(
                                [
                                    'product_id' => $productCreate->id,
                                    'store_id' => $request->store->id,
                                    'distribute_id' => $distributeCreated->id,
                                    'element_distribute_id' =>  $element_distribute_created->id,
                                    'name' => $name,
                                    'price' => $price,
                                    'import_price' => $itemSub['import_price'] ?? 0,
                                    'quantity_in_stock' => $quantity_in_stock,
                                    'barcode' =>  $barcode,
                                    'sku' =>  $sku,
                                ]
                            );

                            if ($request->branch != null) {

                                InventoryUtils::update_cost_of_capital_or_stock_by_id(
                                    $request->store->id,
                                    $request->branch->id,
                                    $productCreate->id,
                                    $element_distribute_created->id,
                                    $subEleCreate->id,
                                    floatval($itemSub['cost_of_capital'] ?? 0),
                                    intval($itemSub['stock'] ?? 0),
                                );
                            }
                        }
                    }
                }

                ProductDistribute::create(
                    [
                        'store_id' => $request->store->id,
                        'product_id' => $productCreate->id,
                        'distribute_id' => $distributeCreated->id
                    ]
                );
            }
            break;
        }


        if ($list_attribute_request !== null && is_array($list_attribute_request) && count((array)$list_attribute_request) > 0) {


            foreach ((array)$list_attribute_request as $attribute) {
                if (isset($attribute["name"]) && isset($attribute["value"]) != null) {
                    $distributeCreated = Attribute::create(
                        [
                            'store_id' => $request->store->id,
                            'product_id' => $productCreate->id,
                            'name' => $attribute["name"],
                            'value' => $attribute["value"],
                        ]
                    );
                }
            }
        }

        // price retail product step 
        try {
            if ($request->is_product_retail_step == true && !empty($arrProductRetailStep)) {
                $newProductRetailStep = array_map(function ($item) use ($productCreate) {
                    $item['product_id'] = $productCreate->id;
                    return $item;
                }, $arrProductRetailStep);

                ProductRetailStep::insert($newProductRetailStep);
            }
        } catch (\Throwable $th) {
        }


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_ADD,
            TypeAction::FUNCTION_TYPE_PRODUCT,
            "Thêm sản phẩm " . $name,
            $productCreate->id,
            $name
        );

        ProductController::update_min_max_product(Product::where('id', $productCreate->id)->first());



        // PushNotificationAdminJob::dispatch(
        //     "User ",
        //     "Vừa thêm sản phẩm " . $name . " cho store " . $request->store->name,
        // );

        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::NEW_PRODUCT,   $productCreate);

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Product::where('id', $productCreate->id)->first()
        ], 201);
    }

    /**
     * Cập nhật hoa hồng tất cả sản phẩm
     * 
     * @bodyParam percent_collaborator double phần trăm hoa hồng sản phẩm
     * @bodyParam product_ids danh sách id sp
     */
    public function updateAllPercentCollaboratorProduct(Request $request)
    {

        if ($request->percent_collaborator < 0 || $request->percent_collaborator > 100) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PERCENT[0],
                'msg' => MsgCode::INVALID_PERCENT[1],
            ], 400);
        }


        if ($request->product_ids != null && is_array($request->product_ids)) {
            Product::whereIn('id', $request->product_ids)->where('store_id', $request->store->id)->update(
                [
                    'percent_collaborator' => $request->percent_collaborator,
                ]
            );
        } else {
            Product::where('store_id', $request->store->id)->update(
                [
                    'percent_collaborator' => $request->percent_collaborator,
                ]
            );
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Cập nhật 1 sản phẩm
     * @urlParam  store_code required Store code
     * 
     * @bodyParam one_field boolean Cập nhật 1 biến khi này = true thì chỉ lấy đúng mỗi name_field (= false thì các field bên dưới mới có giá trị)
     * @bodyParam name_field Tên trường cần cập nhật
     * @bodyParam value_field Giá trị trường cần cập nhật
     * 
     * @bodyParam name string required Tên sản phẩm
     * @bodyParam sku string required Sku
     * @bodyParam description string required Mô tả sản phẩm
     * @bodyParam content_for_collaborator string required Nội dung mô tả cho cộng tác viên bán
     * @bodyParam price int required Giá sản phẩm
     * @bodyParam import_price double required Giá nhập
     * @bodyParam status int Trạng thái sản phẩm (0 Hiển thị -1 Đang ẩn, 1 xoa)
     * @bodyParam barcode string Barcode sản phẩm
     * @bodyParam list_distribute string List chi tiết [  {name:"ten", "sub_element_distribute_name": "Sub ten",element_distributes:[{name:"ten",image_url:"image",price:1000,"price": 1, "quantity_in_stock": 2,},{name:"ten",image_url:"image",price:1000"price": 1, "quantity_in_stock": 2,"sub_element_distributes": [ {"name": "XL", "price": 3, "quantity_in_stock": 4 }]}]}  ] toi da 1 item
     * @bodyParam images string List chi tiết [ link1 link2 ]
     * @bodyParam percent_collaborator double chia se cho CTV
     * @bodyParam type_share_collaborator_number int kiểu số tiền chia sẻ 0 là % 1 là theo số tiền
     * @bodyParam money_amount_collaborator double là số tiền hoa hồng nếu khách chọn 1 (theo số tiền)
     * @bodyParam list_promotion List [{content:"Noi dung khuyen mai","post_id":1, "post_name":"ten bai viet"  }]
     * @bodyParam categories List Danh sach danh muc
     * @bodyParam category_children_ids List Danh sach danh muc con
     * @bodyParam seo_title string tiêu đề cho seo
     * @bodyParam seo_description string Mô tả cho seo
     * @bodyParam check_inventory boolean Có kiểm kho hay ko (ko gửi mặc định false)
     * @bodyParam weight double Cân nặng
     * @bodyParam is_medicine boolean sản phẩm có phải là thuốc không
     * 
     */
    public function updateOneProduct(Request $request)
    {

        $barcode_request = $request->barcode;
        $now = Helper::getTimeNowCarbon();
        $category_children_ids = (array)$request->category_children_ids;
        $arrProductRetailStep = [];
        $product_id = request("product_id");
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

        if ($request->is_product_retail_step == true && !empty($request->product_retail_steps) && is_array($request->product_retail_steps)) {
            $previousToQuantity = $request->product_retail_steps[0]['from_quantity'];
            foreach ($request->product_retail_steps as $idx => $productRetailStep) {
                if ($productRetailStep['from_quantity'] == null || $productRetailStep['to_quantity'] == null || $productRetailStep['price'] == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_PRODUCT_RETAIL_STEP[0],
                        'msg' => MsgCode::INVALID_PRODUCT_RETAIL_STEP[1],
                    ], 400);
                }

                if (($productRetailStep['from_quantity'] <= $previousToQuantity && $idx != 0)) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_PRODUCT_RETAIL_STEP[0],
                        'msg' => MsgCode::INVALID_PRODUCT_RETAIL_STEP[1],
                    ], 400);
                } else {
                    array_push($arrProductRetailStep, [
                        'product_id' => null,
                        'store_id' =>  $request->store->id,
                        'from_quantity' => $productRetailStep['from_quantity'],
                        'to_quantity' => $productRetailStep['to_quantity'],
                        'price' => $productRetailStep['price'],
                        'created_at' => $now->format('Y-m-d H:i:s'),
                        'updated_at' => $now->format('Y-m-d H:i:s'),
                    ]);
                }
                $previousToQuantity = $productRetailStep['to_quantity'];
            }
        }

        if ($request->one_field == true) {
            $productExists->update([
                $request->name_field => $request->value_field
            ]);
        } else {
            if (empty($request->name)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                    'msg' => MsgCode::NAME_IS_REQUIRED[1],
                ], 400);
            }

            $productDupExists = Product::where(
                'name',
                $request->name
            )->where(
                'id',
                '!=',
                $product_id
            )->where('status', '!=', 1)
                ->where(
                    'store_id',
                    $request->store->id
                )->first();

            if ($productDupExists != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PRODUCT_NAME_ALREADY_EXISTS[0],
                    'msg' => MsgCode::PRODUCT_NAME_ALREADY_EXISTS[1],
                ], 400);
            }

            //SKU
            $productSKUExists = Product::where(
                'sku',
                $request->sku
            )->where(
                'id',
                '!=',
                $product_id
            )->where('status', '<>', 1)
                ->where(
                    'store_id',
                    $request->store->id
                )->first();


            if ($productSKUExists != null && !empty($request->sku)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[0],
                    'msg' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[1],
                ], 400);
            }

            $productBarcodeExists = Product::where(
                'barcode',
                $barcode_request
            )->where(
                'id',
                '!=',
                $product_id
            )->where('status', '<>', '1')
                ->where(
                    'store_id',
                    $request->store->id
                )->first();

            if ($productBarcodeExists != null && !empty($barcode_request)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PRODUCT_BARCODE_ALREADY_EXISTS[0],
                    'msg' => MsgCode::PRODUCT_BARCODE_ALREADY_EXISTS[1],
                ], 400);
            }

            $productUrlExists = Product::where(
                'product_url',
                $request->product_url
            )->where(
                'id',
                '!=',
                $product_id
            )->where('status', '<>', '1')
                ->where(
                    'store_id',
                    $request->store->id
                )->first();

            if ($productUrlExists != null && !empty($request->product_url)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => "PRODUCT_URL_EXISTS",
                    'msg' => "Url này được sử dụng",
                ], 400);
            }


            $has_change_image = false;
            $has_change_attributes = false;
            $has_change_distributes = false;
            $has_change_category = false;

            $images_request = (array)$request->images;
            $images_db = $productExists->images->pluck('image_url')->toArray();
            if (count($images_request) == count($images_db)) {

                $i = 0;
                foreach ($images_request as $image_request) {

                    if ($images_request[$i] != $images_db[$i]) {
                        $has_change_image = true;
                        break;
                    }
                    $i++;
                }
            } else {
                $has_change_image = true;
            }



            //attributes
            $list_attributes_request = (array)$request->list_attribute;

            $attributes_db = $productExists->attributes;
            if (count($list_attributes_request) ==  count($attributes_db)) {
                for ($i = 0; $i < count($list_attributes_request); $i++) {


                    if ($list_attributes_request[$i]['name'] !=  $attributes_db[$i]->name) {
                        $has_change_attributes = true;
                    }

                    if ($list_attributes_request[$i]['value'] !=  $attributes_db[$i]->value) {
                        $has_change_attributes = true;
                    }
                }
            } else {
                $has_change_attributes = true;
            }

            //distributes
            $list_distribute_request = (array)$request->list_distribute;
            $distributes_db = $productExists->distributes;


            // if (count($list_distribute_request) ==  count($distributes_db)) {

            //     for ($i = 0; $i < count($list_distribute_request); $i++) {

            //        dd( $list_distribute_request[$i]['name']);


            //     }
            // } else {
            //     $has_change_distributes = true;
            // }


            $categories_request = (array)$request->categories;


            $has_change_category = true;

            foreach ($categories_request as $categoryId) {

                $checkCategoryExists = Category::where(
                    'id',
                    $categoryId
                )->where(
                    'store_id',
                    $request->store->id
                )->first();

                if (empty($checkCategoryExists)) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                        'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
                    ], 400);
                }
            }

            //////////////////////////////////////////////////////////////////////////////////////////
            $quantity_in_stock = -1;
            if ($request->quantity_in_stock !== null && (int)$request->quantity_in_stock >= 0) {
                $quantity_in_stock = $request->quantity_in_stock;
            }


            if ($request->percent_collaborator != null && ($request->percent_collaborator < 0 || $request->percent_collaborator > 100)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_PERCENT[0],
                    'msg' => MsgCode::INVALID_PERCENT[1],
                ], 400);
            }

            $json_list_promotion = [];
            $list_promotion_request = $request->list_promotion;
            //Xu ly noi dung promotion
            if ($list_promotion_request != null && is_array($list_promotion_request) && count($list_promotion_request) > 0) {
                foreach ($list_promotion_request as $promotion_request) {
                    array_push($json_list_promotion, [
                        "content" => $promotion_request["content"] ?? null,
                        "post_id" => $promotion_request["post_id"] ?? null,
                        "post_name" => $promotion_request["post_name"] ?? null,
                    ]);
                }
            }

            $slug = Str::slug($request->name);

            if ($productExists->product_url == null) {
                $slugExsits = DB::table('slugs')
                    ->where('type', 'product')
                    ->where('value', Str::slug($productExists->name))->first();
            } else {
                $slugExsits = DB::table('slugs')
                    ->where('type', 'product')
                    ->where('value', $productExists->product_url)->first();
            }

            if ($slugExsits != null) {
                if ($request->product_url == null) {
                    $slug = Str::slug($request->name);
                } else {
                    $slug = $request->product_url;
                }
                DB::table('slugs')
                    ->where('id', $slugExsits->id)->update([
                        'value' => $slug,
                    ]);
            } else {
                if ($request->product_url == null) {
                    $slug = Str::slug($request->name);
                } else {
                    $slug = $request->product_url;
                }


                $slugCreate = DB::table('slugs')->insert([
                    'type' => 'product',
                    'value' => $slug,
                ]);
            }

            $productExists->update(
                [
                    'sku' => $request->sku ?? "",
                    'content_for_collaborator' => $request->content_for_collaborator,
                    'description' => $request->description,
                    'name' => $request->name,
                    'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
                    'price' => $request->price ?? 0,
                    'status' => $request->status,
                    'quantity_in_stock' => $quantity_in_stock,
                    'import_price' =>  $request->import_price,
                    'barcode' => $request->barcode ?? "",
                    'video_url' => $request->video_url ?: null,
                    'percent_collaborator' => $request->percent_collaborator ?? 0,
                    'type_share_collaborator_number' => $request->type_share_collaborator_number ?? 0,
                    'money_amount_collaborator' => $request->money_amount_collaborator ?? 0,

                    'json_list_promotion' =>  json_encode($json_list_promotion),
                    'seo_title' => $request->seo_title == null ? "" :  $request->seo_title,
                    'seo_description' => $request->seo_description == null ? "" : $request->seo_description,
                    'check_inventory' =>  $request->check_inventory,
                    'point_for_agency' => $request->point_for_agency,
                    'weight' => $request->weight,
                    'is_medicine' => $request->is_medicine,
                    'is_product_retail_step' => filter_var($request->is_product_retail_step, FILTER_VALIDATE_BOOLEAN) ?? false,
                    'canonical_url' => $request->canonical_url,
                    'meta_robots_index' => $request->meta_robots_index,
                    'meta_robots_follow' => $request->meta_robots_follow,
                    'product_url' => $request->product_url ? $request->product_url : Str::slug($request->name),
                ]
            );

            $productExists->update(

                [
                    'weight' => $request->weight,
                    'shelf_position' => $request->shelf_position,

                ]

            );


            if ($has_change_image == true) {

                ProductImage::where('product_id', $productExists->id)->delete();

                foreach ($images_request as $image) {
                    if (isset($image)) {
                        ProductImage::create(
                            [
                                'image_url' => $image,
                                'product_id' => $productExists->id,
                            ]
                        );
                    }
                }
            }

            if ($has_change_category == true) {

                ProductCategory::where('product_id', $productExists->id)->delete();
                ProductCategoryChild::where('product_id', $productExists->id)->delete();

                foreach ($categories_request as $categoryId) {

                    if (ProductCategory::where('product_id', $productExists->id)->where('category_id', $categoryId)->first() == null) {
                        ProductCategory::create(
                            [
                                'product_id' => $productExists->id,
                                'category_id' => $categoryId
                            ]
                        );
                    }
                }


                if ($category_children_ids !== null && count($category_children_ids) > 0) {

                    foreach ($category_children_ids as $categoryChildId) {

                        $checkCategoryExists = CategoryChild::where(
                            'id',
                            $categoryChildId
                        )->where(
                            'store_id',
                            $request->store->id
                        )->first();

                        if (ProductCategory::where('product_id', $productExists->id)->where('category_id', $checkCategoryExists->category_id)->first() == null) {
                            ProductCategory::create(
                                [
                                    'product_id' => $productExists->id,
                                    'category_id' => $checkCategoryExists->category_id
                                ]
                            );
                        }


                        ProductCategoryChild::create(
                            [
                                'product_id' => $productExists->id,
                                'category_children_id' => $categoryChildId
                            ]
                        );
                    }
                }
            }



            if ($list_distribute_request  != null && count($list_distribute_request) > 0) {

                //Xóa những cái không cần thiết
                foreach ($list_distribute_request as $distribute) {

                    if (isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) == 0) {
                        Distribute::where('product_id', $productExists->id)->delete();
                    }

                    if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {
                        // Distribute::where('product_id', $productExists->id)->where('name',)->where('sub_element_distribute_name')->delete();

                        $arrEle = [];
                        foreach ($distribute["element_distributes"] as $eleR) {
                            array_push($arrEle, $eleR['name']);
                        }

                        $arrSub = [];
                        //kiểm số lượng sub element chuẩn và định hình phân loại cộng thêm 
                        if (isset($distribute["element_distributes"][0]) && isset($distribute["element_distributes"][0]["sub_element_distributes"]) && count($distribute["element_distributes"][0]["sub_element_distributes"]) > 0) {

                            $dis_not =  Distribute::where('product_id', $productExists->id)
                                ->where('name', '<>', $distribute["name"])
                                ->whereOr('sub_element_distribute_name', '<>', $distribute["sub_element_distribute_name"] ?? null)
                                ->delete();


                            $dis_has = Distribute::where('product_id', $productExists->id)
                                ->where('name', $distribute["name"])
                                ->where('sub_element_distribute_name', $distribute["sub_element_distribute_name"] ?? null)
                                ->first();

                            if ($dis_has != null) {
                                $ele_not =  ElementDistribute::where('product_id', $productExists->id)

                                    ->where('distribute_id', $dis_has->id)
                                    ->whereNotIn('name', $arrEle)
                                    ->delete();
                            }

                            $list_subs = [];

                            foreach ($distribute["element_distributes"][0]["sub_element_distributes"] as $subElement) {
                                array_push($list_subs, $subElement["name"]);
                            }



                            $sub_not =  SubElementDistribute::where('product_id', $productExists->id)
                                ->whereNotIn('name', $list_subs)
                                ->delete();
                        }
                    }
                }

                //THêm hoặc cập nhật
                if (true == true) {



                    Distribute::where('product_id', $productExists->id)->delete();
                    ElementDistribute::where('product_id', $productExists->id)->delete();
                    ProductDistribute::where('product_id', $productExists->id)->delete();
                    SubElementDistribute::where('product_id', $productExists->id)->delete();

                    //Check sku distribute
                    foreach ($list_distribute_request as $distribute) {
                        if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {
                            $arrSub = [];
                            //kiểm số lượng sub element chuẩn và định hình phân loại cộng thêm 
                            if (isset($distribute["element_distributes"][0]) && isset($distribute["element_distributes"][0]["sub_element_distributes"]) && count($distribute["element_distributes"][0]["sub_element_distributes"]) > 0) {

                                foreach ($distribute["element_distributes"][0]["sub_element_distributes"] as $subElement) {
                                    if (isset($subElement['name'])) {
                                        array_push($arrSub, $subElement['name']);
                                    }
                                }
                            }
                            $arrSub = array_unique($arrSub);

                            foreach ($distribute["element_distributes"] as $element_distribute) {
                                if (isset($element_distribute["sku"])) {
                                    $elementDistributeSkuExists = ElementDistribute::where('sku', $element_distribute["sku"])
                                        ->where('store_id', $request->store->id)
                                        ->first();

                                    if ($elementDistributeSkuExists != null) {
                                        return response()->json([
                                            'code' => 400,
                                            'success' => false,
                                            'msg_code' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[0],
                                            'msg' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[1],
                                        ], 400);
                                    }
                                }

                                if (count($arrSub) > 0 && isset($element_distribute['sub_element_distributes']) && count($element_distribute['sub_element_distributes']) > 0 && count($element_distribute['sub_element_distributes']) == count($arrSub)) {
                                    foreach ($element_distribute['sub_element_distributes'] as $itemSub) {
                                        if (isset($itemSub["sku"])) {
                                            $subElementDistributeSkuExists = SubElementDistribute::where('sku', $itemSub["sku"])
                                                ->where('store_id', $request->store->id)
                                                ->first();

                                            if ($subElementDistributeSkuExists != null) {
                                                return response()->json([
                                                    'code' => 400,
                                                    'success' => false,
                                                    'msg_code' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[0],
                                                    'msg' => MsgCode::PRODUCT_SKU_ALREADY_EXISTS[1],
                                                ], 400);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    }

                    foreach ($list_distribute_request as $distribute) {
                        if (isset($distribute["name"]) && isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {


                            $distributeCreated = Distribute::where('product_id', $productExists->id)->where('store_id', $request->store->id)
                                ->where('name', $distribute["name"])->where('sub_element_distribute_name',  $distribute["sub_element_distribute_name"] ?? null)->first();

                            if ($distributeCreated == null) {
                                Distribute::where('product_id', $productExists->id)->delete();
                                $distributeCreated = Distribute::create(
                                    [
                                        'product_id' => $productExists->id,
                                        'store_id' => $request->store->id,
                                        'name' => $distribute["name"],
                                        'sub_element_distribute_name' => $distribute["sub_element_distribute_name"] ?? null,
                                    ]
                                );
                            } else {

                                $distributeCreated->update(
                                    [
                                        'product_id' => $productExists->id,
                                        'store_id' => $request->store->id,
                                        'name' => $distribute["name"],
                                        'sub_element_distribute_name' => $distribute["sub_element_distribute_name"] ?? null,
                                    ]
                                );
                            }




                            $arrSubIn = [];
                            //kiểm số lượng sub element chuẩn và định hình phân loại cộng thêm 
                            if (isset($distribute["element_distributes"][0]) && isset($distribute["element_distributes"][0]["sub_element_distributes"]) && count($distribute["element_distributes"][0]["sub_element_distributes"]) > 0) {

                                foreach ($distribute["element_distributes"][0]["sub_element_distributes"] as $subElement) {
                                    if (isset($subElement['name'])) {
                                        array_push($arrSubIn, $subElement['name']);
                                    }
                                }
                            }

                            $arrSubIn = array_unique($arrSubIn);

                            foreach ($distribute["element_distributes"] as $element_distribute) {

                                $element_distribute_created = ElementDistribute::where('product_id', $productExists->id)->where('store_id', $request->store->id)
                                    ->where('name', $element_distribute['name'])
                                    ->where('distribute_id', $distributeCreated->id)->first();

                                $quantity_in_stock =  (int)($element_distribute['quantity_in_stock'] ?? 0);

                                if ($element_distribute_created == null) {
                                    $element_distribute_created = ElementDistribute::create(
                                        [
                                            'product_id' => $productExists->id,
                                            'store_id' => $request->store->id,
                                            'name' => $element_distribute['name'],
                                            'image_url' => $element_distribute["image_url"],
                                            'distribute_id' => $distributeCreated->id,
                                            'import_price' => $element_distribute['import_price'] ?? 0,
                                            'price' => $element_distribute["price"] ?? 0,
                                            'quantity_in_stock' => $quantity_in_stock,
                                            'barcode' => $element_distribute["barcode"] ?? null,
                                            'sku' => $element_distribute["sku"] ?? null,
                                        ]
                                    );
                                } else {
                                    $element_distribute_created->update(
                                        [
                                            'product_id' => $productExists->id,
                                            'store_id' => $request->store->id,
                                            'name' => $element_distribute['name'],
                                            'image_url' => $element_distribute["image_url"],
                                            'distribute_id' => $distributeCreated->id,
                                            'import_price' => $element_distribute['import_price'] ?? 0,
                                            'price' => $element_distribute["price"] ?? 0,
                                            'barcode' => $element_distribute["barcode"] ?? null,
                                            'quantity_in_stock' =>  $quantity_in_stock,
                                            'sku' => $element_distribute["sku"] ?? null,
                                        ]
                                    );
                                }



                                if (count($arrSubIn) > 0 && isset($element_distribute['sub_element_distributes'])) {
                                    $indexSub = 0;
                                    foreach ($element_distribute['sub_element_distributes'] as $itemSub) {
                                        $nameSub = "";
                                        $quantity_in_stock = null;
                                        $price = null;

                                        $nameSub = $itemSub['name'] ?? null;

                                        if ($nameSub == "" || $nameSub == null) {
                                            $nameSub = isset($arrSubIn[$indexSub]) ?  $arrSubIn[$indexSub] : null;
                                        }
                                        if ($nameSub == "" || $nameSub == null) {
                                            continue;
                                        };

                                        $quantity_in_stock = (int)($itemSub['quantity_in_stock'] ?? 0);
                                        $price = $itemSub['price'] ?? 0;
                                        $barcode = $itemSub['barcode'] ?? null;
                                        $sku = $itemSub['sku'] ?? null;


                                        $sub = SubElementDistribute::where('product_id', $productExists->id)
                                            ->where('store_id', $request->store->id)
                                            ->where('distribute_id', $distributeCreated->id)
                                            ->where('element_distribute_id', $element_distribute_created->id)
                                            ->where('name', $nameSub)
                                            ->first();

                                        if ($sub == null) {
                                            $sub =   SubElementDistribute::create(
                                                [
                                                    'product_id' => $productExists->id,
                                                    'store_id' => $request->store->id,
                                                    'distribute_id' => $distributeCreated->id,
                                                    'element_distribute_id' =>  $element_distribute_created->id,
                                                    'name' => $nameSub,
                                                    'price' => $price,
                                                    'import_price' => $itemSub['import_price'] ?? 0,
                                                    'quantity_in_stock' => $quantity_in_stock,
                                                    'barcode' => $barcode,
                                                    'sku' =>  $sku,
                                                ]
                                            );
                                        } else {

                                            $sub->update([
                                                'product_id' => $productExists->id,
                                                'store_id' => $request->store->id,
                                                'distribute_id' => $distributeCreated->id,
                                                'element_distribute_id' =>  $element_distribute_created->id,
                                                'name' => $nameSub,
                                                'price' => $price,
                                                'import_price' => $itemSub['import_price'] ?? 0,
                                                'quantity_in_stock' => $quantity_in_stock,
                                                'barcode' => $barcode,
                                                'sku' =>  $sku,
                                            ]);
                                        }
                                        $indexSub++;
                                    }
                                }
                            }
                            $productDistribute = ProductDistribute::where('store_id', $request->store->id)
                                ->where('product_id', $productExists->id)
                                ->where('distribute_id', $distributeCreated->id)
                                ->first();

                            if ($productDistribute  == null) {
                                $productDistribute = ProductDistribute::create(
                                    [
                                        'store_id' => $request->store->id,
                                        'product_id' => $productExists->id,
                                        'distribute_id' => $distributeCreated->id
                                    ]
                                );
                            }
                        }
                        break;
                    }
                }
            }

            if ($has_change_attributes) {
                Attribute::where('product_id', $productExists->id)->delete();

                foreach ($list_attributes_request as $attribute) {
                    if (isset($attribute["name"]) && isset($attribute["value"]) != null) {
                        $distributeCreated = Attribute::create(
                            [
                                'store_id' => $request->store->id,
                                'product_id' => $productExists->id,
                                'name' => $attribute["name"],
                                'value' => $attribute["value"],
                            ]
                        );
                    }
                }
            }

            if (ProductUtils::check_type_distribute($productExists) == ProductUtils::NO_ELE_SUB) {
                if ($request->branch != null) {

                    InventoryUtils::update_cost_of_capital_or_stock_by_id(
                        $request->store->id,
                        $request->branch->id,
                        $productExists->id,
                        null,
                        null,
                        null,
                        $request->import_price ?? 0,
                    );
                }
            }
            ProductController::update_min_max_product($productExists);

            if ($productExists->check_inventory == true) {
                InventoryUtils::update_total_stock_all_branch_to_quantity_in_stock_by_id($request->store->id, $productExists->id);
            }
        }

        try {
            if (!empty($arrProductRetailStep)) {
                ProductRetailStep::where('product_id', $productExists->id)->delete();
                foreach ($arrProductRetailStep as &$productRetailStep) {
                    $productRetailStep['product_id'] = $productExists->id;
                }
                ProductRetailStep::insert($arrProductRetailStep);
            }
        } catch (\Throwable $th) {
        }


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_PRODUCT,
            "Sửa sản phẩm " . $productExists->name,
            $productExists->id,
            $productExists->name
        );

        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_PRODUCT,   $productExists);

        $up_speed = request('up_speed_image', $default = null);
        Cache::forget(json_encode(["getImagesAttribute2", $productExists->id, $up_speed]));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Product::where('id', $productExists->id)->first()
        ], 200);
    }


    /**
     * Danh sách sản phẩm
     * 
     * status": 0 hiển thị - số còn lại là ẩn
     * has_in_discount: boolean (sp có chuẩn bị và đang diễn ra trong discount)
     * has_in_combo: boolean (sp có chuẩn bị và đang diễn ra trong combo)
     * total_stoking còn hàng
     * total_out_of_stock' hết hàng
     * total_hide' ẩn
     * 
     * @urlParam  store_code required Store code
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: samsung
     * @queryParam  sort_by Sắp xếp theo VD: price,views, sales
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  category_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  category_children_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  details Filter theo thuộc tính VD: Màu:Đỏ|Size:XL
     * @queryParam  status (0 -1) còn hàng hay không! không truyền lấy cả 2
     * @queryParam  filter_by Chọn trường nào để lấy
     * @queryParam  filter_option Kiểu filter ( > = <)
     * @queryParam  filter_by_value Giá trị trường đó
     * @queryParam  is_get_all boolean Lấy tất cá hay không 
     * @queryParam  limit int Số item 1 trangơ
     * @queryParam  agency_type_id int id Kiểu đại lý
     * @queryParam  is_show_description bool Cho phép trả về mô tả
     * @queryParam  is_near_out_of_stock boolean Gần hết kho
     * @queryParam  check_inventory boolean lấy danh sách theo trạng thái
     * 
     * 
     */
    public function getAll(Request $request, $id)
    {

        $is_show_description = filter_var($request->is_show_description, FILTER_VALIDATE_BOOLEAN); //
        $is_get_all = filter_var($request->is_get_all, FILTER_VALIDATE_BOOLEAN); //
        $is_near_out_of_stock = filter_var($request->is_near_out_of_stock, FILTER_VALIDATE_BOOLEAN); //
        $check_inventory = filter_var(request('check_inventory'), FILTER_VALIDATE_BOOLEAN); //


        $categoryIds = request("category_ids") == null ? [] : explode(',', request("category_ids"));
        $categoryChildrenIds = request("category_children_ids") == null ? [] : explode(',', request("category_children_ids"));
        $requestDetails = request("details") == null ? [] : explode('|', request("details"));

        $details = array();
        $distributes = array();


        foreach ($requestDetails as $requestDetail) {

            $requestDetailSplit = explode(':', $requestDetail);

            if ($requestDetailSplit[0] != null &&  $requestDetailSplit[1]) {
                $name = $requestDetailSplit[0];
                $atrribute = explode(',', $requestDetailSplit[1]);

                $details[$name] =  $atrribute;

                $distributes += $atrribute;
            }
        }
        $status = request('status') != null ? (int)request('status') : null;
        $filter_by = request('filter_by') ?: null;
        $filter_option = request('filter_option');
        $filter_by_value = request('filter_by_value') ?: null;

        if ($filter_by != null) {
            $filter_option = DefineCompare::getOperator($filter_option);
        }


        $search = StringUtils::convert_name_lowcase(request('search'));
        $searchArr = explode(' ', $search);

        $sort_by = request('sort_by');
        $after_res = Product::with('product_retail_steps')
            ->where(
                'store_id',
                $request->store->id
            )
            ->where(
                'status',
                '<>',
                1
            )
            ->when(empty($search) && ($sort_by == 'sales'  || $sort_by == 'views'), function ($query) use ($sort_by) {

                if ($sort_by == 'views') {
                    $sort_by = 'view';
                }
                if ($sort_by == 'sales') {
                    $sort_by = 'sold';
                }
                $query->when(!empty($sort_by), function ($query) use ($sort_by) {
                    $query->orderBy($sort_by, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
                });
            })

            ->when(count($categoryIds) > 0, function ($query) use ($categoryIds) {
                $query->whereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                });
            })

            ->when(count(
                $categoryChildrenIds
            ) > 0, function ($query) use ($categoryChildrenIds) {
                $query->whereHas('category_children', function ($query) use ($categoryChildrenIds) {
                    $query->whereIn('category_children.id',  $categoryChildrenIds);
                });
            })
            ->when(request('sort_by') == null && empty($search), function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->where(function ($query) use ($search, $searchArr) {
                $query->where(function ($q) use ($searchArr) {
                    foreach ($searchArr as $word) {
                        $q->where('name_str_filter', 'like', '%' . $word . '%');
                    }
                })->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            })
            ->when(!empty($search), function ($query) use ($search, $request) {
                $query->search($search, null, true);
            })
            ->when(request('check_inventory') != null, function ($query) use ($check_inventory) {
                $query->where('check_inventory', $check_inventory);
            })


            ->when(count($details) > 0, function ($query) use ($details, $distributes) {
                $query->whereHas('details', function ($query) use ($details, $distributes) {
                    $query->whereIn('product_details.name', array_keys($details))
                        ->when(count($distributes) > 0, function ($query) use ($distributes) {
                            $query->whereHas('distributes', function ($query) use ($distributes) {
                                $query->whereIn('distributes.name',  $distributes);
                            });
                        });
                });
            });



        $arr_out_stock = [];
        if ($is_near_out_of_stock == true) {
            $arr_out_stock =  ProductUtils::arr_list_product_out_of_stock($request->store->id, $after_res);
        }

        $r =  clone $after_res;
        $res_products = $r
            ->when($filter_by != null && $filter_by_value != null, function ($query) use ($filter_by, $filter_by_value, $filter_option) {
                $query->where($filter_by,  $filter_option, $filter_by_value);
            })
            ->when($is_near_out_of_stock == true, function ($query) use ($arr_out_stock) {
                $query->whereIn('id', $arr_out_stock)->where('check_inventory', true);
            })

            ->when($status !== null, function ($query) use ($status) {
                $query->where('status', $status);
            });



        $r =  clone $after_res;
        $total_stoking = $r
            ->where('quantity_in_stock', '!=', 0)
            ->where('status', 0)->count();

        $r =  clone $after_res;
        $total_out_of_stock = $r
            ->where('quantity_in_stock', '==', 0)
            ->where('status', 0)->count();

        $r =  clone $after_res;
        $total_hide = $r
            ->where('status', '!=', 0)->count();

        //get tat ca

        if ($is_get_all == true) {


            $res_products =  $res_products->with('categories');
            $res_products =  $res_products->with('category_children');
            $res_products =  $res_products->with('attribute_search_children');
            $res_products =  $res_products->paginate(100000);
        } else {
            $res_products =  $res_products

                ->paginate(request('limit') == null ? 20 : request('limit'))
                // ->sortBy(function ($query) use ($search) {
                //     return (strpos($query->name_str_filter, $search) == 0) ? 10 : 0;
                // })
            ;
        }



        foreach ($res_products as $product) {
            if ($is_get_all == true) {
                $product->attribute_searches =  $product->attribute_searches();
            }
            $product->has_in_discount = $product->hasInDiscount();
            $product->has_in_combo = $product->hasInCombo();
            $product->has_in_bonus_product = $product->hasInBonusProduct();

            $product->description = null;

            $agency_type_id = (int) $request->input('agency_type_id');
            if ($agency_type_id  != null) {

                $data =       [
                    "percent_agency" => ProductUtils::get_percent_agency_with_agency_type($product->id,  $agency_type_id),
                    "main_price" => ProductUtils::get_main_price_with_agency_type($product->id,  $agency_type_id, $product->price),
                    "distributes" => ProductUtils::get_price_distributes_with_agency_type($product->id,  $agency_type_id, null, $request->store->id)
                ];

                $data["min_price"] = ProductUtils::get_min_price_with_agency_price($data);
                $data["max_price"] = ProductUtils::get_max_price_with_agency_price($data);

                $product->agency_price =  $data;
            }
        }

        $custom = collect(
            [
                'total_stoking' => $total_stoking,
                'total_out_of_stock' => $total_out_of_stock,
                'total_hide' => $total_hide
            ]
        );


        if ($is_show_description) {
            $productDB = DB::table('products')->where('store_id', $request->store->id)->where('status', '<>', 1)
                ->get();

            foreach ($res_products as $product) {


                if ($is_show_description) {
                    $des = null;
                    foreach ($productDB  as $pro) {
                        if ($pro->id == $product->id) {
                            $des = $pro->description;
                            break;
                        }
                    }
                    $product->full_description = $des;
                }
            }
        }



        $data = $custom->merge($res_products);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $data,
        ], 200);
    }


    /**
     * Thông tin một sản phẩm
     * @urlParam  store_code required Store code cần lấy.
     * @urlParam  id required ID product cần lấy thông tin.
     */
    public function getOneProduct(Request $request, $id)
    {
        $productExists = $request->product;
        $des = DB::table('products')
            ->where('id', $productExists->id)->orWhere('product_url', $productExists->id)
            ->select(
                'description',
                'content_for_collaborator',
                'is_product_retail_step'
            )->get();
        $proRes =  $productExists->toArray();

        $proRes["description"] = $des[0]->description;
        $proRes["content_for_collaborator"] = $des[0]->content_for_collaborator;

        $proRes["search_children"] = ProAttSearchChild::where('product_id', $productExists->id)->get();

        $proRes["product_retail_steps"] = $des[0]->is_product_retail_step ? ProductRetailStep::where('product_id', $productExists->id)->get() : null;

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $proRes,
        ], 200);
    }

    /**
     * xóa một sản phẩm
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID product cần xóa thông tin.
     */
    public function deleteOneProduct(Request $request, $id)
    {
        $idDeleted = $request->product->id;
        $name = $request->product->name;

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_DELETE,
            TypeAction::FUNCTION_TYPE_PRODUCT,
            "Xóa sản phẩm " . $request->product->name,
            $request->product->id,
            $request->product->name
        );



        // $request->product->update([
        //     'status' => 1
        // ]);

        SendToWebHookUtils::sendToWebHook(
            $request,
            SendToWebHookUtils::DELETE_PRODUCT,
            [
                'name' =>  $name,
                'id' =>  $request->product->id,
                'sku' => $request->product->sku,
            ]
        );

        $request->product->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }

    /**
     * xóa nhiều sản phẩm
     * @urlParam  store_code required Store code cần xóa.
     * @bodyParam  list_id danh sách id cần xóa
     */
    public function deleteManyProduct(Request $request, $id)
    {
        $idsDeleted = array();

        if (is_array($request->list_id) && count($request->list_id) > 0) {
            foreach ($request->list_id as $id) {

                array_push($idsDeleted, $id);

                $product = Product::where(
                    'store_id',
                    $request->store->id
                )->where("id", $id)->first();
                if ($product != null) {
                    // $product->update([
                    //     'status' => 1
                    // ]);

                    SendToWebHookUtils::sendToWebHook(
                        $request,
                        SendToWebHookUtils::DELETE_PRODUCT,
                        [
                            'name' =>  $product->name,
                            'id' =>   $product->id,
                            'sku' =>   $product->sku,
                        ]
                    );

                    $product->delete();
                }
            }
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_DELETE,
            TypeAction::FUNCTION_TYPE_PRODUCT,
            "Xóa nhiều sản phẩm",
            0,
            0
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idsDeleted' =>  $idsDeleted],
        ], 200);
    }



    /**
     * Cài đặt thuộc tính tìm kiếm cho sản phẩm
     * 
     * @urlParam  store_code required Store code cần xóa.
     * 
     * @urlParam  product_id Id san pham
     * @bodyParam  list_attribute_search_childs danh sách id tìm kiếm con
     * 
     */
    public function set_up_attribute_search(Request $request)
    {

        $product_id = request('product_id');

        ProAttSearchChild::where('product_id', $product_id)->delete();
        //Thêm con
        if ($request->list_attribute_search_childs != null && is_array($request->list_attribute_search_childs)) {

            foreach ($request->list_attribute_search_childs as $cateChild) {

                $trr =  AttributeSearchChild::where('id', $cateChild)->where('store_id', $request->store->id)->first();
                if ($trr != null) {
                    $lkPro = ProAttSearchChild::where('product_id', $product_id)
                        ->where('attribute_search_child_id', $cateChild)->first();


                    if ($lkPro == null) {
                        ProAttSearchChild::create(
                            [
                                'product_id' => $product_id,
                                'attribute_search_child_id' => $cateChild
                            ]
                        );
                    }
                }
            }
        }

        $ProductController = new ProductController();
        return  $ProductController->getOneProduct($request, null);
    }

    /**
     * Danh sách Attribute child search của sản phẩm
     * 
     */
    public function getAllChildSearchOfProduct(Request $request)
    {


        $ids  =  ProAttSearchChild::where('product_id', $request->product->id)
            ->pluck('attribute_search_child_id')->toArray();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>    $ids,
        ], 200);
    }


    /**
     * Cập nhật tên 1 sản phẩm
     * @urlParam  store_code required Store code
     * 
     * @bodyParam price string required Giá sản phẩm
     * 
     * 
     */
    public function updateNameOneProduct(Request $request)
    {


        $product_id = request("product_id");
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


        if (empty($request->name)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $productDupExists = Product::where(
            'name',
            $request->name
        )->where(
            'id',
            '!=',
            $product_id
        )->where('status', '!=', 1)
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if ($productDupExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PRODUCT_NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::PRODUCT_NAME_ALREADY_EXISTS[1],
            ], 400);
        }


        $productExists->update(
            Helper::sahaRemoveItemArrayIfNullValue(
                [
                    'name' => $request->name ?? "",
                    'name_str_filter' => $request->name ?? "",
                ]
            )
        );


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_PRODUCT,
            "Sửa tên sản phẩm " . $productExists->name,
            $productExists->id,
            $productExists->name
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Product::where('id', $productExists->id)->first()
        ], 200);
    }

    /**
     * Cập nhật giá 1 sản phẩm
     * @urlParam  store_code required Store code
     * 
     * @bodyParam price string required Giá sản phẩm
     * 
     * 
     */
    public function updatePriceOneProduct(Request $request)
    {


        $product_id = request("product_id");
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


        $productExists->update(
            Helper::sahaRemoveItemArrayIfNullValue(
                [
                    'price' => $request->price ?? 0,
                ]
            )
        );


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_PRODUCT,
            "Sửa giá sản phẩm " . $productExists->name,
            $productExists->id,
            $productExists->name
        );

        ProductController::update_min_max_product(Product::where('id', $productExists->id)->first());

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Product::where('id', $productExists->id)->first()
        ], 200);
    }

    public function getProductRetailStep(Request $request)
    {
        $product_id = request("product_id");
        $productExists = Product::where('id', $product_id)
            ->where('status', '<>', 1)
            ->where('store_id', $request->store->id)
            ->first();

        if ($productExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        }

        $productRetailSteps = ProductRetailStep::where('store_id', $request->store->id)
            ->where('product_id', $product_id)
            ->orderBy('from_quantity', 'asc')
            ->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $productRetailSteps
        ], 200);
    }
}
