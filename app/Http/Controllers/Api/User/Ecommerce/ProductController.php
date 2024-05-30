<?php

namespace App\Http\Controllers\Api\User\Ecommerce;

use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\SanCategory;
use App\Models\SanProduct;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group  User/Sản phẩm
 */
class ProductController extends Controller
{

    /**
     * Danh sách sản phẩm
     * 
     * @queryParam on_sale boolean phải onsale ko
     * 
     */
    public function getAll(Request $request, $id)
    {
        $arr_onsale_product_ids = SanProduct::where('store_id', $request->store->id)->where('on_sale', true)->pluck('product_id')->toArray();


        $on_sale = filter_var(request('on_sale'), FILTER_VALIDATE_BOOLEAN);

        $search = StringUtils::convert_name_lowcase(request('search'));
        $products = DB::table('products')
            ->select([
                'products.id',
                'products.name',
                'products.name_str_filter',
                'products.sku',
                'products.store_id',
                'products.video_url',
                'products.price',
                'products.min_price',
                'products.max_price',
                'products.barcode',
            ])
            ->when($on_sale == true, function ($query) use ($arr_onsale_product_ids) {
                $query->whereIn(
                    'id',
                    $arr_onsale_product_ids
                );
            })
            ->when($on_sale == false, function ($query) use ($arr_onsale_product_ids) {
                $query->whereNotIn(
                    'id',
                    $arr_onsale_product_ids
                );
            })
            ->where('products.store_id', $request->store->id)
            ->where('products.status', '<>', 1)
            ->when(!empty($search), function ($query) use ($search, $request) {
                $query->search($search, null, true, true);
            })->paginate(request('limit') == null ? 20 : request('limit'));


        $custom = collect(
            [
                'total_all' => DB::table('products')->select(['id',])->where('store_id', $request->store->id)->where('status', '<>', 1)->count(),
                'total_sale' =>  DB::table('products')->select(['id',])
                    ->whereIn('id',  $arr_onsale_product_ids)->where('store_id', $request->store->id)
                    ->where('status', '<>', 1)->count(),
            ]
        );

        $res = $custom->merge($products);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $res,
        ], 200);
    }

    /**
     * Đẩy sản phẩm lên sàn
     * 
     * @bodyParam is_all boolean Tất cả phải không
     * @bodyParam list_product_ids Danh sách id sản phẩm trường hợp is_all = false
     * @bodyParam category_index_0_id id danh mục chính
     * @bodyParam category_index_1_id id danh mục phụ
     * @bodyParam is_remove boolean có phải gỡ không để trống hoặc true là thêm
     * 
     */
    public function add_remove(Request $request, $id)
    {

        $is_remove =  filter_var($request->is_remove, FILTER_VALIDATE_BOOLEAN);

        $category_index0 = SanCategory::where('category_index', 0)->where('id', $request->category_index_0_id)->first();
        $category_index1 = SanCategory::where('category_index', 1)->where('id', $request->category_index_1_id)->first();

        if ($category_index0  ==  null ||  $category_index1 == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
            ], 400);
        }

        $is_all =  filter_var($request->is_all, FILTER_VALIDATE_BOOLEAN);

        if ($is_all == true) {
            $product_ids = DB::table('products')
                ->where(
                    'store_id',
                    $request->store->id
                )
                ->where(
                    'status',
                    '<>',
                    1
                )
                ->select([
                    'id',
                ])->pluck('id')->toArray();

            foreach ($product_ids as $pro_id) {
                $proExis = SanProduct::where('product_id', $pro_id)->where('store_id', $request->store->id)
                    ->first();

                if ($proExis == null) {
                    SanProduct::create([
                        "store_id" =>  $request->store->id,
                        "product_id" => $pro_id,
                        "on_sale" =>  $is_remove == true ? false : true,
                        "category_index_0" => $request->category_index_0_id,
                        "category_index_1" => $request->category_index_1_id,
                    ]);
                } else {
                    $proExis->update([
                        "store_id" =>  $request->store->id,
                        "product_id" => $pro_id,
                        "on_sale" =>  $is_remove == true ? false : true,
                        "category_index_0" => $request->category_index_0_id,
                        "category_index_1" => $request->category_index_1_id,
                    ]);
                }
            }
        } else {
            if (is_array($request->list_product_ids)) {
                foreach ($request->list_product_ids as $pro_id) {
                    $pro = DB::table('products')->where('store_id', $request->store->id)->where('id', $pro_id)->first();

                    $proExis = SanProduct::where('product_id', $pro_id)->where('store_id', $request->store->id)
                        ->first();

                    if ($pro  != null) {
                        if ($proExis == null) {
                            SanProduct::create([
                                "store_id" =>  $request->store->id,
                                "product_id" => $pro_id,
                                "on_sale" =>  $is_remove == true ? false : true,
                                "category_index_0" => $request->category_index_0_id,
                                "category_index_1" => $request->category_index_1_id,
                            ]);
                        } else {
                            $proExis->update([
                                "store_id" =>  $request->store->id,
                                "product_id" => $pro_id,
                                "on_sale" =>  $is_remove == true ? false : true,
                                "category_index_0" => $request->category_index_0_id,
                                "category_index_1" => $request->category_index_1_id,
                            ]);
                        }
                    }
                }
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
