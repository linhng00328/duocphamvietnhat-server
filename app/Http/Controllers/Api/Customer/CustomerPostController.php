<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Post;
use App\Models\UnreadPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group  Customer/Bài viết
 */

class CustomerPostController extends Controller
{
    /**
     * Danh sách bài viết
     * customer/{{store_code}}/posts?page=1&search=name&sort_by=id&descending=false&category_ids=1,2,3
     * @urlParam  store_code required Store code cần lấy
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: samsung
     * @queryParam  sort_by Sắp xếp theo VD: price
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  category_ids Thuộc category id nào VD: 1,2,3
     */

    public function getAll(Request $request, $id)
    {
        if ($request->store != null && $request->store->user != null && $request->store->user->is_block == true) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Cửa hàng đã bị khóa, vui lòng liên hệ chăm sóc khách hàng!",
            ], 400);
        }

        $categoryIds = request("category_ids") == null ? [] : explode(',', request("category_ids"));


        $posts = Post::where(
            'store_id',
            $request->store->id
        )->where(
            'published',
            1
        )
            ->when(request('sort_by'), function ($query) {
                $query->orderBy(request('sort_by'), filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when(count($categoryIds) > 0, function ($query) use ($categoryIds) {
                $query->whereHas('category_posts', function ($query) use ($categoryIds) {
                    $query->whereIn('category_posts.id', $categoryIds);
                });
            })
            ->when(request('sort_by') == null, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->search(request('search'))
            ->paginate(20);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $posts,
        ], 200);
    }

    /**
     * Thông tin bài viết
     * @urlParam  store_code required Store code cần lấy.
     * @urlParam  id required ID post cần lấy thông tin.
     */
    public function getOnePost(Request $request, $id)
    {
        $postExists = Post::where(
            'id',
            $request->id
        )->orWhere(
            'post_url',
            $request->id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($postExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        } else {


            $postExists->update([
                'count_view' => $postExists->count_view + 1
            ]);

            if ($request->customer != null) {
                $postMax = Post::where('store_id', $request->store->id,)->orderBy('id', 'desc')->first();

                $unreadpost = UnreadPost::where('store_id', $request->store->id,)
                    ->where('customer_id', $request->customer->id)->first();
                if ($unreadpost  == null) {

                    UnreadPost::create(
                        [
                            'store_id' => $request->store->id,
                            'customer_id' => $request->customer->id,
                            'id_read_max' => $postMax->id
                        ]
                    );
                } else {


                    $unreadpost->update([
                        'id_read_max' => $postMax->id
                    ]);
                }
            }


            $des = DB::table('posts')
                ->where('id',  $postExists->id)
                ->select(
                    'content',
                )->get();
            $proRes =   $postExists->toArray();
            $proRes["content"] = $des[0]->content;

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' =>  $proRes,
            ], 200);
        }
    }
}
