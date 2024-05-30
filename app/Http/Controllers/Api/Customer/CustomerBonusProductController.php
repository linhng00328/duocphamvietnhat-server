<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\GroupCustomerUtils;
use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\BonusProduct;
use App\Models\MsgCode;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @group  Customer/BOnus product
 */
class CustomerBonusProductController extends Controller
{

    /**
     * Lấy danh sách bonus đang phát hành
     * @urlParam  store_code required Store code cần lấy.
     * 
     *  
     */
    public function getAllAvailable(Request $request, $id)
    {
        $now = Helper::getTimeNowString();

        // request('product_id') ==  null

        $BonusProducts = BonusProduct::select(
            'id',
            'name',
            'ladder_reward',
            'end_time',
            'start_time',
            'amount',
            'group_customer',
            'agency_type_id',
            'group_type_id',
            'group_customers',
            'agency_types',
            'group_types',
        )->where('store_id', $request->store->id)
            ->where('is_end', false)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->whereRaw('((bonus_products.amount - bonus_products.used > 0) OR bonus_products.set_limit_amount = false)')
            ->get();

        $request = request();
        $customer = request('customer', $default = null);

        $BonusProductsRes = [];
        foreach ($BonusProducts as  $bonus_product) {
            $bonus_product->ladder_reward = filter_var($bonus_product->ladder_reward, FILTER_VALIDATE_BOOLEAN);

            $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                $request,
                $bonus_product->group_customer,
                $bonus_product->agency_type_id,
                $bonus_product->group_type_id,
                $customer,
                $request->store->id,
                $bonus_product->group_customers,
                $bonus_product->agency_types,
                $bonus_product->group_types
            );

            if ($ok_customer) {
                array_push($BonusProductsRes, $bonus_product);
            }
        }

        // $bonus_product->select_products  = [];
        // $bonus_product->bonus_products  = [];
        // $bonus_product->bonus_products_ladder = [];

        foreach ($BonusProductsRes as  $bonus_product) {


            $select_products = DB::table('bonus_product_items')->where('store_id', $request->store->id)->where('bonus_product_id', $bonus_product->id)->where('is_select_product', 1)->get();
            foreach ($select_products  as  $select_product) {

                $product = Cache::remember(json_encode(["select_product", $select_product->product_id,]), 6, function ()  use ($select_product) {
                    return  DB::table('products')->select('id', 'name', 'price')->where('id', $select_product->product_id)->first();
                });

                $product->images = Cache::remember(json_encode(["getImagesAttribute", $select_product->product_id,]), 6, function () use ($select_product) {
                    return ProductImage::select('image_url')->limit(1)->where('product_id', $select_product->product_id)->get();
                });

                $select_product->product = $product;
            }


            $bonus_product->select_products =  $select_products;
            /////

            $bonus_products  = DB::table('bonus_product_items')->where('store_id', $request->store->id)->where('bonus_product_id', $bonus_product->id)->where('is_select_product', 0)->get();
            foreach ($bonus_products  as  $bonus_productITEM) {

                $product = Cache::remember(json_encode(["bonus_product", $bonus_productITEM->product_id,]), 6, function ()  use ($bonus_productITEM) {
                    return  DB::table('products')->select('id', 'name', 'price')->where('id', $bonus_productITEM->product_id)->first();
                });

                $product->images = Cache::remember(json_encode(["getImagesAttribute", $bonus_productITEM->product_id,]), 6, function () use ($bonus_productITEM) {
                    return ProductImage::select('image_url')->limit(1)->where('product_id', $bonus_productITEM->product_id)->get();
                });

                $bonus_productITEM->product = $product;
            }
            $bonus_product->bonus_products =  $bonus_products;

            /////

            $bonus_products_ladder = DB::table('bonus_product_item_ladders')->select('from_quantity', 'bo_quantity', 'product_id', 'bo_product_id')->where('store_id', $request->store->id)->where('bonus_product_id', $bonus_product->id)->orderBy('from_quantity', 'asc')->get();
            foreach ($bonus_products_ladder  as  $ladder) {

                $product = Cache::remember(json_encode(["ladder_product", $ladder->product_id,]), 6, function ()  use ($ladder) {
                    return  DB::table('products')->select('id', 'name', 'price')->where('id', $ladder->product_id)->first();
                });

                $product->images = Cache::remember(json_encode(["getImagesAttribute", $ladder->product_id,]), 6, function () use ($ladder) {
                    return ProductImage::select('image_url')->limit(1)->where('product_id', $ladder->product_id)->get();
                });

                $product->min_price = $product->price;
                $ladder->product =  $product;

                $bo_product = Cache::remember(json_encode(["ladder_bo_product", $ladder->bo_product_id,]), 6, function ()  use ($ladder) {
                    return  DB::table('products')->select('id', 'name', 'price')->where('id', $ladder->bo_product_id)->first();
                });


                $ladder->bo_product = $bo_product;
            }

            $bonus_product->bonus_products_ladder =  $bonus_products_ladder;
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $BonusProductsRes,
        ], 200);
    }
}
