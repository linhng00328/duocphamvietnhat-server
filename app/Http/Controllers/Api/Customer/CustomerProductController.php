<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\GroupCustomerUtils;
use App\Helper\Helper;
use App\Helper\StringUtils;
use App\Http\Controllers\Api\User\GeneralSettingController;
use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCategoryChild;
use App\Models\ProductDiscount;
use App\Models\SessionCustomer;
use App\Models\ViewerProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prophecy\Util\StringUtil;

/**
 * @group  Customer/Sản phẩm
 */

class CustomerProductController extends Controller
{
    public function getSlug(Request $request)
    {
        $slugs = Db::table("slugs")->where('value', $request->slug)->first();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $slugs,
        ], 200);
    }


    /**
     * Danh sách sản phẩm
     * 
     * thêm trường: is_favorite, is_top_sale, is_new
     * 
     * customer/{{store_code}}/products?page=1&search=name&sort_by=id&descending=false&category_ids=1,2,3&details=Màu:Đỏ|Size:XL
     * @urlParam  store_code required Store code cần lấy
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: samsung
     * @queryParam  sort_by Sắp xếp theo VD: (sales theo luot mua,views theo luot xem, created_at)
     * @queryParam  descending Giảm dần không VD: false  (chỉ áp dụng cho giá tiền)
     * @queryParam  category_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  category_children_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  details Filter theo thuộc tính VD: Màu:Đỏ|Size:XL
     * @queryParam  attribute_search_children_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  has_discount có giảm giá hay không
     * 
     */

    public function getAll(Request $request, $id)
    {
        $sort_by = request("sort_by");
        $now = Helper::getTimeNowString();
        $search = StringUtils::convert_name_lowcase(request('search'));
        $searchArr = explode(' ', $search);

        $categoryIds = request("category_ids") == null ? [] : explode(',', request("category_ids"));
        $categoryChildrenIds = request("category_children_ids") == null ? [] : explode(',', request("category_children_ids"));
        $attribute_search_children_ids = request("attribute_search_children_ids") == null ? [] : explode(',', request("attribute_search_children_ids"));

        $has_discount =  filter_var(request("has_discount"), FILTER_VALIDATE_BOOLEAN);
        //product discount
        $product_dis = ProductDiscount::where('product_discounts.store_id', $request->store->id,)
            ->leftJoin('discounts', 'discounts.id', '=', 'product_discounts.discount_id')
            ->where('discounts.is_end', false)
            ->where('discounts.start_time', '<', $now)
            ->where('discounts.end_time', '>', $now)
            ->orderBy('discounts.created_at', 'desc')
            ->whereRaw('(discounts.amount - discounts.used > 0 OR discounts.set_limit_amount = false)')
            ->take(10)->get();

        $product_dis_ids_res = [];
        if ($has_discount  == true) {

            foreach ($product_dis  as  $product_dis_item) {
                $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                    $request,
                    $product_dis_item->group_customer,
                    $product_dis_item->agency_type_id,
                    $product_dis_item->group_type_id,
                    $request->customer,
                    $request->store->id,
                    $product_dis_item->group_customers,
                    $product_dis_item->agency_types,
                    $product_dis_item->group_types
                );
                if ($ok_customer) {
                    array_push($product_dis_ids_res, $product_dis_item->product_id);
                }
            }
        }


        $products =   Product::where(
            'products.store_id',
            $request->store->id
        )->where(
            'products.status',
            0
        )
            ->when($has_discount  == true, function ($query) use ($product_dis_ids_res) {
                $query->whereIn('id', $product_dis_ids_res);
            })
            ->when(request('min_price') != null, function ($query) {
                $query->where('min_price', '>', request('min_price'));
            })
            ->when(request('max_price') != null, function ($query) {
                $query->where('max_price', '<', request('max_price'));
            })
            ->when(count($categoryChildrenIds) > 0, function ($query) use ($categoryChildrenIds) {
                $query->whereHas('category_children', function ($query) use ($categoryChildrenIds) {
                    $query->whereIn('category_children.id',  $categoryChildrenIds);
                });
            })
            ->when(count($attribute_search_children_ids) > 0, function ($query) use ($attribute_search_children_ids) {
                $query->whereHas('attribute_search_children', function ($query) use ($attribute_search_children_ids) {
                    $query->whereIn('attribute_search_children.id',  $attribute_search_children_ids);
                });
            })

            ->when(count($categoryIds) > 0, function ($query) use ($categoryIds) {
                $query->whereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                });
            });


        if ($sort_by == 'sales'  || $sort_by == 'views' || $sort_by == 'created_at') {

            if ($sort_by == 'views') {
                $sort_by = 'view';
            }
            if ($sort_by == 'sales') {
                $sort_by = 'sold';
            }
            $products =  $products->when(!empty($sort_by), function ($query) use ($sort_by) {
                $query->orderBy($sort_by, filter_var((bool) request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            });
        }

        if ($sort_by === 'price') {
            $products =  $products->orderBy('min_price',  filter_var((bool) request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
        }

        $products =  $products->when(empty($sort_by) && empty($search), function ($query) {
            $query->orderBy('created_at', 'desc');
        });


        //  ->search(request('search'))
        $products =   $products
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

            ->paginate($request->limit ?: 20);


        foreach ($products as $product) {
            $product->has_in_discount = $product->hasInDiscount();
            $product->has_in_combo = $product->hasInCombo();
            $product->has_in_bonus_product = $product->hasInBonusProduct();
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $products,
        ], 200);
    }

    /**
     * Thông tin một sản phẩm
     * @urlParam  store_code required Store code cần lấy.
     * @urlParam  id required ID product cần lấy thông tin.
     */
    public function getOneProduct(Request $request, $id)
    {

        $productExists = Product::with('product_retail_steps')->where(
            'id',
            $request->id
        )->orWhere('product_url', $request->id)->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($productExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        } else {

            //Find id customer

            $token = request()->header('customer-token');

            $customer_id = null;
            if (!empty($token)) {

                $checkTokenIsValid = SessionCustomer::where('token', $token)->first();
                if ($checkTokenIsValid  != null) {
                    $customer_id = $checkTokenIsValid->customer_id;
                }
            }

            $viewer = ViewerProduct::create(
                [
                    'store_id' => $request->store->id,
                    'product_id' => $productExists->id,
                    'customer_id' =>  $customer_id,
                ]
            );

            $productExists->update([
                'view' =>    $productExists->view + 1
            ]);
            ////

            $des = DB::table('products')
                ->where('id', $productExists->id)
                ->select(
                    'description',
                    'content_for_collaborator',
                )->get();
            $proRes =  $productExists->toArray();

            $proRes["description"] = $des[0]->description;
            $proRes["content_for_collaborator"] = $des[0]->content_for_collaborator;


            $config = GeneralSettingController::defaultOfStore($request);
            $allow_semi_negative = $config['allow_semi_negative'];
            if ($allow_semi_negative  == true) {
                $proRes["check_inventory"]  = false;
            } else {
                if ($proRes["quantity_in_stock"]  <= 0) {
                    $proRes["quantity_in_stock"]  = 0;
                }
                if ($proRes["quantity_in_stock_with_distribute"]  <= 0) {
                    $proRes["quantity_in_stock_with_distribute"]  = 0;
                }
            }
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' =>  $proRes,
            ], 200);
        }
    }


    /**
     * Danh sách sản phẩm tương tự
     * @urlParam  store_code required Store code cần lấy.
     * @urlParam  id required ID product cần lấy danh sách
     */
    public function getAllSimilar(Request $request, $id)
    {

        $productExists = Product::where(
            'id',
            $request->id
        )->where(
            'products.status',
            0
        )

            ->where(
                'store_id',
                $request->store->id
            )->first();



        if (empty($productExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        } else {
            $arr_cate = $productExists->category_children->pluck("id")->toArray();
            $arr_id_products = ProductCategoryChild::whereIn('category_children_id', $arr_cate)
                ->get()->pluck('product_id')->toArray();

            if (count($arr_id_products) == 0) {
                $arr_cate = $productExists->categories->pluck("id")->toArray();

                $arr_id_products = ProductCategory::whereIn('category_id', $arr_cate)

                    ->get()->pluck('product_id')->toArray();
            }

            $products =   Product::where(
                'products.store_id',
                $request->store->id
            )->whereIn(
                'products.id',
                $arr_id_products
            )
                ->where(
                    'products.status',
                    0
                )
                ->where(
                    'products.id',
                    '!=',
                    $productExists->id
                )
                ->orderBy('id', 'DESC')->take(20)->get();
            // ->orderBy('id', 'DESC')->get();

            if (count($products) == 0) {
                $products =   Product::where(
                    'products.store_id',
                    $request->store->id
                )->where(
                    'products.status',
                    0
                )->where(
                    'products.id',
                    '!=',
                    $productExists->id
                )->orderBy('id', 'DESC')
                    ->search(request(substr($productExists->name, 0, 5)))->take(20)->get();
            }


            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' =>  $products,
            ], 200);
        }
    }

    /**
     * DS sản phẩm đã mua
     * @urlParam  store_code required Store code cần lấy.
     */
    public function purchased_products(Request $request, $id)
    {

        $products =   Product::where(
            'products.store_id',
            $request->store->id
        )->where(
            'products.status',
            0
        );

        $customer_id = $request->customer->id;

        $products =  $products->join('line_items', function ($join) {
            $join->on('products.id', '=', 'line_items.product_id');
        })->where('line_items.customer_id', $customer_id)
            ->selectRaw('products.*,  MAX(line_items.updated_at) as max_time')
            ->groupBy('products.id')

            ->orderBy('max_time', 'desc');

        $products =   $products->paginate(20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $products,
        ], 200);
    }

    /**
     * DS sản phẩm vừa xem
     * @urlParam  store_code required Store code cần lấy.
     */
    public function watched_products(Request $request, $id)
    {

        $products =   Product::where(
            'products.store_id',
            $request->store->id
        )->where(
            'products.status',
            0
        );

        $customer_id = $request->customer->id;

        $products =  $products->join('viewer_products', function ($join) {
            $join->on('products.id', '=', 'viewer_products.product_id');
        })->where('viewer_products.customer_id', $customer_id)
            ->selectRaw('products.*,  MAX(viewer_products.updated_at) as max_time')
            ->groupBy('products.id')
            ->orderBy('max_time', 'desc');

        $products =   $products->paginate(20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $products,
        ], 200);
    }

    /**
     * Danh sách sản phẩm yêu thích
     * @urlParam product_id string required product_id
     * @bodyParam is_favorite yêu thích hay không
     */
    public function getAllFavorite(Request $request)
    {

        $fivoriteIds = Favorite::where(
            'store_id',
            $request->store->id
        )->where(
            'customer_id',
            $request->customer->id
        )->orderBy('updated_at', 'desc')->get()->pluck("product_id");

        $products = Product::whereIn(
            'id',
            $fivoriteIds
        )->where(
            'status',
            0
        );


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $products->paginate(20)
        ], 200);
    }
}
