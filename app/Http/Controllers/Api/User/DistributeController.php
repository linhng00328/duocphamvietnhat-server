<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\ProductUtils;
use App\Http\Controllers\Controller;
use App\Models\Distribute;
use App\Models\ElementDistribute;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\SubElementDistribute;
use Illuminate\Http\Request;


/**
 * @group  User/Phân loại sản phẩm
 */
class DistributeController extends Controller
{

    /**
     * Thông tin 1 phân loại sp
     * @bodyParam product_id product
     * 
     */
    public function get_distribute_product(Request $request)
    {
        $product_id = $request->product_id;
        $productExists = Product::where(
            'id',
            $product_id
        )->where(
            'store_id',
            $request->store->id
        )
            ->first();

        if (empty($productExists)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>    $productExists->distributes
        ], 200);
    }




    /**
     * Sửa phân loại
     * @queryParam product_id int id của id của product
     * 
     * @bodyParam has_distribute boolean Có phân loại không (false đồng nghĩa với xóa)
     * 
     * @bodyParam distribute_name Tên phân loại chính (VD màu sắc)
     * 
     * @bodyParam has_sub boolean Có phân loại phụ không
     * @bodyParam sub_element_distribute_name Tên kiểu phân loại phụ
     * 
     * @bodyParam element_distributes List  danh sách element [ {is_edit,before_name, name,image_url,price,import_price,default_price,barcode, quantity_in_stock, sub_element_distributes:[json phía dưới] }  ]
     * @bodyParam sub_element_distributes List  danh sách element [ {is_edit,before_name, name,image_url,price,import_price,default_price,barcode, quantity_in_stock}  ]
     * 
     * @bodyParam name string tên phân loại
     * @bodyParam image_url string ảnh phân loại 
     * @bodyParam price giá bán
     * @bodyParam import_price giá nhập
     * @bodyParam default_price giá mặc định
     * @bodyParam barcode barcode
     * @bodyParam quantity_in_stock kho
     * 
     */
    public function updateDistribute(Request $request)
    {

        $product_id = $request->product_id;
        $productExists = Product::where(
            'id',
            $product_id
        )->where(
            'store_id',
            $request->store->id
        )
            ->first();

        if (empty($productExists)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }


        if ($request->has_distribute == false) {
            ElementDistribute::where('product_id', $productExists->id)
                ->where('store_id', $request->store->id)
                ->delete();
            SubElementDistribute::where('product_id', $productExists->id)
                ->where('store_id', $request->store->id)
                ->delete();

            Distribute::where('product_id',  $product_id)->delete();

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' =>    $productExists->distributes
            ], 200);
        }

        if (empty($request->distribute_name)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::DISTRIBUTE_NAME_IS_REQUIRED[0],
                'msg' => MsgCode::DISTRIBUTE_NAME_IS_REQUIRED[1],
            ], 400);
        }

        if (empty($request->element_distributes) || count($request->element_distributes) == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                'msg' => "Không có phân loại chính nào",
            ], 400);
        }

        $arr_name_element = [];

        foreach ($request->element_distributes as $element_distribute) {

            if (empty($element_distribute['name'])) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                    'msg' => "Một số tên phân loại chính bị để trống",
                ], 400);
            }
            if (is_null($element_distribute['is_edit'])) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Không tìm thấy trường tùy chọn chỉnh sửa ở phân loại chính",
                ], 400);
            }


            if ($element_distribute['is_edit'] == true) {
                $element_exists_name = ElementDistribute::where('product_id', $productExists->id)
                    ->where('store_id', $request->store->id)
                    ->where('name', $element_distribute['before_name'] ?? "")
                    ->first();
                if ($element_exists_name == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                        'msg' => "Phân loại chính " . ($element_distribute['before_name'] ?? "") . " không tồn tại",
                    ], 400);
                }
            } else {

                $element_exists_name = ElementDistribute::where('product_id', $productExists->id)
                    ->where('store_id', $request->store->id)
                    ->where('name', $element_distribute['name'])
                    ->first();

                $count = collect($request->element_distributes)->filter(function ($element) use ($element_distribute) {
                    return $element['name'] === $element_distribute['name'];
                })->count();

                if ($element_exists_name != null && $count > 1) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                        'msg' => "Phân loại chính " . ($element_distribute['name']) . " đã tồn tại",
                    ], 400);
                }
            }

            if (!in_array($element_distribute['name'],  $arr_name_element)) {
                array_push($arr_name_element, $element_distribute['name']);
            }
        }


        if (count($request->element_distributes) != count($arr_name_element)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                'msg' => "Một số tên phân loại chính bị trùng nhau",
            ], 400);
        }

        $has_sub = $request->has_sub;

        $last_arr_sub = [];
        if ($has_sub == true) {
            if (empty($request->sub_element_distribute_name)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::SUB_DISTRIBUTE_NAME_IS_REQUIRED[0],
                    'msg' => MsgCode::SUB_DISTRIBUTE_NAME_IS_REQUIRED[1],
                ], 400);
            }


            $in_arr_sub = [];
            foreach ($request->element_distributes as $element_distribute) {
                $in_arr_sub = [];
                if (empty($element_distribute['sub_element_distributes']) || count($element_distribute['sub_element_distributes']) == 0) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_SUB_ELEMENT_DISTRIBUTE_EXISTS[0],
                        'msg' => "Không có phân loại phụ đi kèm",
                    ], 400);
                }


                $element_distribute_created = ElementDistribute::where('product_id', $productExists->id)
                    ->where('store_id', $request->store->id)
                    ->where('name', $element_distribute['name'])
                    ->first();

                //Thêm vào arr hiện tại
                foreach ($element_distribute['sub_element_distributes'] as $sub_element_distribute) {

                    if (is_null($sub_element_distribute['is_edit'])) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::ERROR[0],
                            'msg' => "Không tìm thấy trường tùy chọn chỉnh sửa ở phân loại phụ",
                        ], 400);
                    }

                    if (empty($sub_element_distribute['name'])) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                            'msg' => "Một số tên phân loại phụ bị để trống",
                        ], 400);
                    }

                    if ($sub_element_distribute['is_edit'] == true) {
                        $sub_element_exists_name = SubElementDistribute::where('product_id', $productExists->id)
                            ->where('store_id', $request->store->id)
                            ->where('name', $sub_element_distribute['before_name'] ?? "")
                            ->first();
                        if ($sub_element_exists_name == null) {
                            return response()->json([
                                'code' => 400,
                                'success' => false,
                                'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                                'msg' => "Phân loại phụ " . ($sub_element_distribute['before_name'] ?? "") . " không tồn tại",
                            ], 400);
                        }
                    } else {
                        $sub_element_exists_name = SubElementDistribute::where('product_id', $productExists->id)
                            ->where('store_id', $request->store->id)
                            ->where('name', $sub_element_distribute['name'])
                            ->first();
                        if ($sub_element_exists_name != null &&  $element_distribute_created != null) {
                            return response()->json([
                                'code' => 400,
                                'success' => false,
                                'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                                'msg' => "Phân loại phụ " . ($sub_element_distribute['name']) . " đã tồn tại",
                            ], 400);
                        }
                    }

                    if (in_array($sub_element_distribute['name'], $in_arr_sub)) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                            'msg' => "Một số tên phân loại phụ bị trùng nhau",
                        ], 400);
                    } else {
                        array_push($in_arr_sub, $sub_element_distribute['name']);
                    }
                }


                if (count($last_arr_sub) != 0) {

                    if (count($last_arr_sub) != count($in_arr_sub)) {

                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::ERROR[0],
                            'msg' => "Số lượng phân loại phụ không giống nhau",
                        ], 400);
                    }

                    foreach ($last_arr_sub as $last_sub) {
                        if (!in_array($last_sub, $in_arr_sub)) {
                            return response()->json([
                                'code' => 400,
                                'success' => false,
                                'msg_code' => MsgCode::ERROR[0],
                                'msg' => "Cấu trúc phân loại phụ trong mỗi phân loại chính không giống nhau",
                            ], 400);
                        }
                    }
                }

                $last_arr_sub = $in_arr_sub;
            }
        }


        $distributeExists = Distribute::where('product_id',  $product_id)->first();
        if ($distributeExists  != null) {

            $distributeExists->update(Helper::sahaRemoveItemArrayIfNullValue(
                [
                    'name' => $request->distribute_name,
                    'sub_element_distribute_name' => $request->sub_element_distribute_name
                ]
            ));
        } else {
            $distributeExists = Distribute::create(
                [
                    'product_id' => $productExists->id,
                    'store_id' => $request->store->id,
                    'name' => $request->distribute_name,
                    'sub_element_distribute_name' => $request->sub_element_distribute_name
                ]
            );
        }


        if ($has_sub == true) {
            foreach ($request->element_distributes as $element_distribute) {

                if ($element_distribute['is_edit'] == true) {
                    $element_distribute_created = ElementDistribute::where('product_id', $productExists->id)
                        ->where('store_id', $request->store->id)
                        ->where('name', $element_distribute['before_name'])
                        ->first();

                    $element_distribute_created->update(Helper::sahaRemoveItemArrayIfNullValue(
                        [
                            "name" => $element_distribute['name'] ?? null,
                            "image_url" => $element_distribute['image_url'] ?? "",
                            "price" =>  $element_distribute['price'] ?? null,
                            "import_price" => $element_distribute['import_price'] ?? null,
                            "default_price" => $element_distribute['default_price'] ?? null,
                            "barcode" =>  $element_distribute['barcode'] ?? "",
                            "position" => $element_distribute['position'] ?? null,
                            "sku" =>  $element_distribute['sku'] ?? null,
                        ]
                    ));
                } else {
                    $element_distribute_created =
                        ElementDistribute::create([
                            "store_id" => $request->store->id,
                            "distribute_id" => $distributeExists->id,
                            "product_id" => $productExists->id,
                            "name" => $element_distribute['name'] ?? "",
                            "image_url" => $element_distribute['image_url'] ?? "",
                            "price" =>  $element_distribute['price'] ?? 0,
                            "import_price" => $element_distribute['import_price'] ?? 0,
                            "default_price" => $element_distribute['default_price'] ?? 0,
                            "barcode" =>  $element_distribute['barcode'] ?? "",
                            "sku" =>  $element_distribute['sku'] ?? null,
                            "position" => $element_distribute['position'] ?? 0,
                        ]);


                    if ($request->branch != null) {
                        InventoryUtils::update_cost_of_capital_or_stock_by_id(
                            $request->store->id,
                            $request->branch->id,
                            $productExists->id,
                            $element_distribute_created->id,
                            null,
                            $element_distribute['import_price'] ?? 0,
                            0,
                            InventoryUtils::TYPE_INIT_STOCK,
                            null,
                            null
                        );
                    }
                }



                //Thêm vào arr hiện tại
                foreach ($element_distribute['sub_element_distributes'] as $sub_element_distribute) {

                    if ($sub_element_distribute['is_edit'] == true) {
                        $sub_element_exists_name = SubElementDistribute::where('product_id', $productExists->id)
                            ->where('store_id', $request->store->id)
                            ->where('distribute_id',  $distributeExists->id)
                            ->where('element_distribute_id',  $element_distribute_created->id)
                            ->where('name', $sub_element_distribute['before_name'])
                            ->first();

                        if ($sub_element_exists_name != null) {
                            $sub_element_exists_name->update(Helper::sahaRemoveItemArrayIfNullValue([
                                "element_distribute_id" => $element_distribute_created->id,
                                "name" => $sub_element_distribute['name'] ?? null,
                                "price" => $sub_element_distribute['price'] ?? null,
                                "import_price" => $sub_element_distribute['import_price'] ?? null,
                                "default_price"  => $sub_element_distribute['default_price'] ?? null,
                                "quantity_in_stock" => $sub_element_distribute['quantity_in_stock'] ?? null,
                                "barcode"  => $sub_element_distribute['barcode'] ?? "",
                                "position" => $sub_element_distribute['position'] ?? null,
                                "sku"  => $sub_element_distribute['sku'] ?? null,
                            ]));
                        } else {
                            SubElementDistribute::create([
                                "store_id" => $request->store->id,
                                "distribute_id" => $distributeExists->id,
                                "product_id" => $productExists->id,
                                "element_distribute_id" => $element_distribute_created->id,
                                "name" => $sub_element_distribute['name'],
                                "price" => $sub_element_distribute['price'] ?? 0,
                                "import_price" => $sub_element_distribute['import_price'] ?? 0,
                                "default_price" => $sub_element_distribute['default_price'] ?? 0,
                                "quantity_in_stock" => $sub_element_distribute['quantity_in_stock'] ?? 0,
                                "position" => $sub_element_distribute['position'] ?? 0,
                                "sku" => $sub_element_distribute['sku'] ?? null,
                            ]);
                        }
                    } else {

                        $sub_element_exists_name = SubElementDistribute::where('product_id', $productExists->id)
                            ->where('store_id', $request->store->id)
                            ->where('distribute_id',  $distributeExists->id)
                            ->where('element_distribute_id',  $element_distribute_created->id)
                            ->where('name', $sub_element_distribute['name'])
                            ->first();

                        if ($sub_element_exists_name == null) {
                            $sub_element_exists_name  =      SubElementDistribute::create([
                                "store_id" => $request->store->id,
                                "distribute_id" => $distributeExists->id,
                                "product_id" => $productExists->id,
                                "element_distribute_id" => $element_distribute_created->id,
                                "name" => $sub_element_distribute['name'],
                                "price" => $sub_element_distribute['price'] ?? 0,
                                "import_price" => $sub_element_distribute['import_price'] ?? 0,
                                "default_price" => $sub_element_distribute['default_price'] ?? 0,
                                "quantity_in_stock" => $sub_element_distribute['quantity_in_stock'] ?? 0,
                                "position" => $sub_element_distribute['position'] ?? 0,
                                "sku"  => $sub_element_distribute['sku'] ?? null,
                            ]);
                        } else {
                            $sub_element_exists_name->update(Helper::sahaRemoveItemArrayIfNullValue([
                                "element_distribute_id" => $element_distribute_created->id,
                                "name" => $sub_element_distribute['name'] ?? null,
                                "price" => $sub_element_distribute['price'] ?? null,
                                "import_price" => $sub_element_distribute['import_price'] ?? null,
                                "default_price"  => $sub_element_distribute['default_price'] ?? null,
                                "quantity_in_stock" => $sub_element_distribute['quantity_in_stock'] ?? null,
                                "barcode"  => $sub_element_distribute['barcode'] ?? "",
                                "position" => $sub_element_distribute['position'] ?? null,
                                "sku"  => $sub_element_distribute['sku'] ?? null,
                            ]));
                        }


                        if ($request->branch != null) {
                            InventoryUtils::update_cost_of_capital_or_stock_by_id(
                                $request->store->id,
                                $request->branch->id,
                                $productExists->id,
                                $element_distribute_created->id,
                                $sub_element_exists_name->id,
                                $element_distribute['import_price'] ?? 0,
                                0,
                                InventoryUtils::TYPE_INIT_STOCK,
                                null,
                                null
                            );
                        }
                    }
                }
            }

            ElementDistribute::where('product_id', $productExists->id)
                ->where('store_id', $request->store->id)
                ->whereNotIn('name', $arr_name_element)
                ->delete();
            SubElementDistribute::where('product_id', $productExists->id)
                ->where('store_id', $request->store->id)
                ->whereNotIn('name', $last_arr_sub)
                ->delete();
        } else {
            //Xóa hết sub luôn
            SubElementDistribute::where('product_id', $productExists->id)
                ->where('store_id', $request->store->id)
                ->delete();


            $distributeExists = Distribute::where('product_id',  $product_id)->first();
            if ($distributeExists  != null) {

                $distributeExists->update(Helper::sahaRemoveItemArrayIfNullValue(
                    [
                        'name' => $request->distribute_name,
                        'sub_element_distribute_name' => null
                    ]
                ));
                $distributeExists->update(
                    [
                        'name' => $request->distribute_name,
                        'sub_element_distribute_name' => null
                    ]
                );
            } else {
                $distributeExists = Distribute::create(
                    [
                        'product_id' => $productExists->id,
                        'store_id' => $request->store->id,
                        'name' => $request->distribute_name,
                        'sub_element_distribute_name' => null
                    ]
                );
            }

            foreach ($request->element_distributes as $element_distribute) {

                if ($element_distribute['is_edit'] == true) {
                    $element_distribute_created = ElementDistribute::where('product_id', $productExists->id)
                        ->where('store_id', $request->store->id)
                        ->where('name', $element_distribute['before_name'])
                        ->first();

                    $element_distribute_created->update(Helper::sahaRemoveItemArrayIfNullValue(
                        [
                            "name" => $element_distribute['name'] ?? null,
                            "image_url" => $element_distribute['image_url'] ?? "",
                            "price" =>  $element_distribute['price'] ?? null,
                            "import_price" => $element_distribute['import_price'] ?? null,
                            "default_price" => $element_distribute['default_price'] ?? null,
                            "barcode" =>  $element_distribute['barcode'] ?? "",
                            "sku" =>  $element_distribute['sku'] ?? null,
                            "position" => $sub_element_distribute['position'] ?? null,
                        ]
                    ));
                } else {
                    $element_distribute_created =
                        ElementDistribute::create([
                            "store_id" => $request->store->id,
                            "distribute_id" => $distributeExists->id,
                            "product_id" => $productExists->id,
                            "name" => $element_distribute['name'] ?? "",
                            "image_url" => $element_distribute['image_url'] ?? "",
                            "price" =>  $element_distribute['price'] ?? 0,
                            "import_price" => $element_distribute['import_price'] ?? 0,
                            "default_price" => $element_distribute['default_price'] ?? 0,
                            "barcode" =>  $element_distribute['barcode'] ?? "",
                            "sku" => $element_distribute['sku'] ?? null,
                            "position" => $sub_element_distribute['position'] ?? 0,
                        ]);

                    if ($request->branch != null) {
                        InventoryUtils::update_cost_of_capital_or_stock_by_id(
                            $request->store->id,
                            $request->branch->id,
                            $productExists->id,
                            $element_distribute_created->id,
                            null,
                            $element_distribute['import_price'] ?? 0,
                            0,
                            InventoryUtils::TYPE_INIT_STOCK,
                            null,
                            null
                        );
                    }
                }
            }
        }

        ElementDistribute::where('product_id', $productExists->id)
            ->where('store_id', $request->store->id)
            ->whereNotIn('name', $arr_name_element)
            ->delete();

        ProductController::update_min_max_product($productExists);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>    $productExists->distributes,
            'product' => Product::where('id', $productExists->id)->first()
        ], 200);
    }
}
