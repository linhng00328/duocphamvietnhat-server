<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\ProductReviews;
use Illuminate\Http\Request;

/**
 * @group  User/Đánh giá sản phẩm
 */

class ReviewsController extends Controller
{
    /**
     * //status 0 đang chờ duyệt  1 ok -1 hủy  
     * Danh sách đánh giá của sản phẩm
     * averaged_stars trung bình sao 
     * 
     * filter_by  (theo số sao stars hoặc status )
     * filter_by_value (giá trị muốn lấy)
     */

    public function getAll(Request $request, $id)
    {

        $total_reviews = 0;
        $total_cancel = 0;
        $total_pending_approval = 0;
        $total_1_stars = 0;
        $total_2_stars = 0;
        $total_3_stars = 0;
        $total_4_stars = 0;
        $total_5_stars = 0;

        $list = ProductReviews::where('store_id', $request->store->id)->get();

        foreach ($list as $item) {
            $total_reviews++;
            if ($item->status == 0) {
                $total_pending_approval++;
            }
            if ($item->status == -1) {
                $total_cancel++;
            }
            if ($item->status == 1) {
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
        }


        $filter_by =  request('filter_by') ?? null;
        $filter_by_value =  request('filter_by_value') ?? null;

        $reviews = ProductReviews::where(
            'store_id',
            $request->store->id
        )
            ->when($filter_by != null && $filter_by_value !== null && $filter_by_value !== "", function ($query) use ($filter_by, $filter_by_value) {
                $query->where($filter_by,  $filter_by_value);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $list = ProductReviews::where(
            'store_id',
            $request->store->id
        )
            ->where(
                'status',
                1
            )->pluck('stars');

        $custom = collect(
            [
                'averaged_stars' => $list->avg() ?? 0,
                'total_reviews' => count($list),
                'total_pending_approval' => $total_pending_approval,
                'total_cancel' => $total_cancel,
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
     * xóa một đánh giá
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID review cần xóa
     */
    public function deleteOne(Request $request, $id)
    {

        $id = $request->route()->parameter('review_id');
        $checkReviewExists = ProductReviews::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkReviewExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_REVIEW_EXISTS[0],
                'msg' => MsgCode::NO_REVIEW_EXISTS[1],
            ], 404);
        } else {
            $idDeleted = $checkReviewExists->id;
            $checkReviewExists->delete();
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => ['idDeleted' => $idDeleted],
            ], 200);
        }
    }


    /**
     * update một Review
     * @urlParam  store_code required Store code cần update
     * @urlParam  review_id required review_id cần update
     * @bodyParam status int required 0 đang chờ duyệt  1 ok -1 hủy  
     * @bodyParam content required nội dung 
     */
    public function updateOne(Request $request)
    {
        $id = $request->route()->parameter('review_id');
        $reviewExists = ProductReviews::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($reviewExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_REVIEW_EXISTS[0],
                'msg' => MsgCode::NO_REVIEW_EXISTS[1],
            ], 404);
        } else {
            $star = (int)$reviewExists->stars;
            $reviewExists->update(
                Helper::sahaRemoveItemArrayIfNullValue([
                    'status' => $request->status,
                    'stars' => ($star < 0 || $star > 5) == true ? 5 : $star,
                    'content' => $request->content,
                ])
            );

            $reviewExistsProduct = ProductReviews::where('store_id', $request->store->id)
                ->where('product_id', $reviewExists->product_id)
                ->where('status', 1);

            $countReviewExistsProduct =  $reviewExistsProduct->count();
            $sumReviewExistsProduct = $reviewExistsProduct->pluck("stars")->sum();
            $averageReviewExistsProduct = $sumReviewExistsProduct == 0 ? 0 : $sumReviewExistsProduct / $countReviewExistsProduct;

            Product::where("id", $reviewExists->product_id)
                ->where(
                    'store_id',
                    $request->store->id
                )
                ->update([
                    'stars' => $averageReviewExistsProduct,
                    'count_stars' => $countReviewExistsProduct
                ]);

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => ProductReviews::where('id', $id)->first(),
            ], 200);
        }
    }
}
