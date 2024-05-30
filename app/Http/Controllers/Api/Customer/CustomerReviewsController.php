<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\LineItem;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\PointSetting;
use App\Models\Product;
use App\Models\ProductReviews;
use App\Helper\PointCustomerUtils;
use App\Helper\TypeFCM;
use App\Jobs\PushNotificationUserJob;
use Illuminate\Http\Request;

/**
 * @group  Customer/Đánh giá sản phẩm
 */
class CustomerReviewsController extends Controller
{
    /**
     * @group  Đánh giá
     * @bodyParam product_id string required product_id
     * @bodyParam order_id string required order_id
     * @bodyParam stars string required Số sao
     * @bodyParam content string required Họ và tên
     * @bodyParam images string required chuỗi link hình ảnh vd: http://link1.jpg|http://link2.jpg
     */
    public function review(Request $request)
    {

        $product_id = $request->route()->parameter('product_id') ?? null;

        $productExists = Product::where(
            'id',
            $product_id
        )->where(
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
        }


        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $request->order_code)
            ->where('customer_id', $request->customer->id)
            ->first();

        if ($orderExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 404);
        }

        $ids_product = collect($orderExists->line_items_at_time)->pluck('id')->toArray();

        if (!in_array($product_id, $ids_product)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PRODUCT_NOT_EXIST_IN_ORDER[0],
                'msg' => MsgCode::PRODUCT_NOT_EXIST_IN_ORDER[1],
            ], 400);
        }

        if ($orderExists->order_status != 10 || $orderExists->payment_status != 2) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::UNFINISHED_OR_UNPAID_ORDER[0],
                'msg' => MsgCode::UNFINISHED_OR_UNPAID_ORDER[1],
            ], 400);
        }

        $reviewExists = ProductReviews::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)
            ->where('product_id', $product_id)
            ->where('order_id',  $orderExists->id)
            ->first();

        if ($reviewExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::YOU_RATED[0],
                'msg' => MsgCode::YOU_RATED[1],
            ], 400);
        }

        $orderExists->update([
            'reviewed'  => true
        ]);

        $star = (int)$request->stars;

        $created = ProductReviews::create(
            [
                'store_id' => $request->store->id,
                'customer_id' => $request->customer->id,
                'product_id' => $product_id,
                'order_id' => $orderExists->id,
                'stars' => ($star < 0 || $star > 5) ? 5 : $star,
                'content' => $request->content,
                'images' => $request->images,
                'video_url' => $request->video_url,
                'status' => 0
            ]
        );
        PushNotificationUserJob::dispatch(
            $request->store->id,
            $request->store->user_id,
            'Đánh giá sản phẩm',
            'Khách hàng ' . $request->customer->name . ' đã đánh giá sản phẩm ',
            TypeFCM::NEW_REVIEW_PRODUCT,
            $request->customer->id,
            null
        );

        $pointSetting = PointSetting::where(
            'store_id',
            $request->store->id
        )->first();
        //tính điểm cho customer
        if ($pointSetting != null) {
            if ($pointSetting->point_review > 0) {

                PointCustomerUtils::add_sub_point(
                    PointCustomerUtils::REVIEW_PRODUCT,
                    $request->store->id,
                    $request->customer->id,
                    $pointSetting->point_review,
                    $created->id,
                    $productExists->name
                );
            }
        }


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $created
        ], 201);
    }

    /**
     * Danh sách đánh giá của sản phẩm
     * averaged_stars trung bình sao 
     * 
     * filter_by  (theo số sao stars hoặc status )
     * filter_by_value (giá trị muốn lấy)
     * has_image_video có ảnh/video hay không
     */

    public function getInProductAll(Request $request, $id)
    {
        $product_id = $request->route()->parameter('product_id') ?? null;
        $has_image_video =  request('has_image_video') ?? null;
        $productExists = Product::where(
            'id',
            $product_id
        )->where(
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
        }

        $customer_id =  $request->customer != null ? $request->customer->id : -1;

        $total_reviews = 0;
        $total_has_image_video = 0;
        $total_1_stars = 0;
        $total_2_stars = 0;
        $total_3_stars = 0;
        $total_4_stars = 0;
        $total_5_stars = 0;
        $list = ProductReviews::where('store_id', $request->store->id)
            ->where('product_id', $product_id)
            ->where('status', 1)->whereOr('customer_id',  $customer_id)
            ->get();
        foreach ($list as $item) {
            $total_reviews++;

            if (($item->images != null && $item->images != "[]" && strlen($item->images) > 0) || $item->video_url) {
                $total_has_image_video++;
            }

            if ($item->stars == 1) {
                $total_1_stars++;
            }
            if ($item->stars == 2) {
                $total_2_stars++;
            }
            if ($item->stars == 3) {
                $total_3_stars++;
            }
            if ($item->stars == 4) {
                $total_4_stars++;
            }
            if ($item->stars == 5) {
                $total_5_stars++;
            }
        }

        $filter_by =  request('filter_by');
        $filter_by_value =  request('filter_by_value');

        $reviews = ProductReviews::where(
            'store_id',
            $request->store->id
        )
            ->where(
                'product_id',
                $productExists->id
            )
            ->where(
                'status',
                1
            )
            ->when($filter_by && $filter_by_value, function ($query) use ($filter_by, $filter_by_value) {
                $query->where($filter_by,  $filter_by_value);
            })
            ->when($has_image_video, function ($query) use ($has_image_video) {
                $has = filter_var($has_image_video, FILTER_VALIDATE_BOOLEAN);

                $query->where(function ($q) use ($has) {
                    $q->where(function ($q) use ($has) {
                        $q->where('images', $has ? "!=" : "==",  "[]")
                            ->where('images', $has ? "!=" : "==",  "null");
                    })->where(function ($q) use ($has) {
                        $q->where('video_url', $has ? "!=" : "==",  "")
                            ->orWhere('video_url', $has ? "!=" : "==",  "null");
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->limit ?: 20);

        $list = ProductReviews::where(
            'store_id',
            $request->store->id
        )
            ->where(
                'product_id',
                $productExists->id
            )
            ->where(
                'status',
                1
            )
            // ->whereOr('customer_id',  $customer_id)
            ->pluck('stars');

        $custom = collect(
            [
                'averaged_stars' => $list->avg() ?? 0,
                'total_has_image_video' => $total_has_image_video,
                'total_has_image' => $total_has_image_video,
                'total_reviews' => $total_reviews,
                'total_1_stars' => $total_1_stars,
                'total_2_stars' => $total_2_stars,
                'total_3_stars' => $total_3_stars,
                'total_4_stars' => $total_4_stars,
                'total_5_stars' => $total_5_stars,
            ]
        );

        $data = $custom->merge($reviews);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data,
        ], 200);
    }

    /**
     * Tất cả đánh giá của tôi
     */

    public function getManagerAll(Request $request, $id)
    {

        $reviews = ProductReviews::where(
            'store_id',
            $request->store->id
        )
            ->where(
                'customer_id',
                $request->customer->id
            )
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $reviews,
        ], 200);
    }


    /**
     * Line item Sản phẩm chưa đánh giá
     */

    public function getAllProductNotRated(Request $request, $id)
    {
        $lines = LineItem::select('line_items.*', 'orders.order_code')
            ->join('orders', 'orders.id', '=', 'line_items.order_id')

            ->where(
                'line_items.store_id',
                $request->store->id
            )
            ->where('orders.order_status', StatusDefineCode::COMPLETED)
            ->where('orders.payment_status', StatusDefineCode::PAID)
            ->where(
                'line_items.customer_id',
                $request->customer->id
            )
            ->where(
                'reviewed',
                false
            )->orderBy('line_items.created_at', 'desc')
            ->paginate(20);

        // ->when(1==1, function ($query) {
        //     dd($query);
        //     // ProductReviews::where(
        //     //     'store_id',
        //     //     $request->store->id
        //     // )
        //     //     ->where(
        //     //         'customer_id',
        //     //         $request->customer->id
        //     //     )->get();
        // })
        // ->whereNotIn('line_items.id', [312])
        // ->join('product_reviews', 'order_id', '=', 'line_items.order_id')
        // ->where('countries.country_name', $country)





        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $lines
        ], 200);
    }
}
