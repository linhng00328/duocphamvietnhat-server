<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Jobs\PushNotificationCustomerJob;
use App\Models\Comment;
use App\Models\MsgCode;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use Illuminate\Http\Request;

/**
 * @group  User/Comment
 */
class CustomerCommunityCommentController extends Controller
{


    /**
     * Danh sách comment của 1 bài đăng
     * @queryParam community_post_id int id bài viết cần xem
     * @urlParam status integer required trạng thái  (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
     * 
     * 
     */
    public function getAll(Request $request)
    {
        $post_id = request('community_post_id');


        if ($request->user == null) {
            $all =
                CommunityComment::where(function ($query) use ($request) {
                    $query->where('status', '=', 0);
                })->where('community_post_id', $post_id)
                ->where('store_id', $request->store->id)
                ->orderBy('id', 'desc')
                ->paginate(20);;
        } else {
            $all =
                CommunityComment::where(function ($query) use ($request) {
                    $query->where('customer_id', $request->customer->id)
                        ->orWhere('status', '=', 0);
                })->where('community_post_id', $post_id)
                ->where('store_id', $request->store->id)
                ->orderBy('id', 'desc')
                ->paginate(20);;
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $all
        ], 200);
    }
    /**
     * Comment bài đăng
     * 
     * @bodyParam community_post_id integer required id bài viết
     * @BodyParam content required Nội dung
     * @bodyParam images required List danh sách ảnh sp (VD: ["linl1", "link2"])
     * 
     */
    public function create(Request $request)
    {


        $checkPostExists = CommunityPost::where(
            'id',
            $request->community_post_id
        )->first();

        if ($checkPostExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[1],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 400);
        }

        $postCreated = CommunityComment::create(
            [
                'store_id' => $request->store == null ? null : $request->store->id,
                'community_post_id' => $request->community_post_id,
                'user_id' => $request->user == null ? null : $request->user->id,
                'staff_id' => $request->staff == null ? null : $request->staff->id,
                'customer_id' => $request->customer == null ? null : $request->customer->id,
                'status' => 0,
                'content' => $request->content,
                'images_json' => json_encode($request->images),
            ]
        );

        // PushNotificationAdminJob::dispatch(
        //     "Bình luận mới" . ($is_buy ? " cần mua " : " cần bán "),
        //     "Bài đăng: " . ($checkPostExists->name),
        //     $is_buy ?   TypeFCM::NEW_COMMENT_BUY : TypeFCM::NEW_COMMENT_SELL,
        //     $checkPostExists->id,
        //     $postCreated->name,
        // );
        if ($checkPostExists->customer_id !== $request->customer->id) {
            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $checkPostExists->customer_id,
                "Bình luận mới",
                $request->customer->name . " đã bình luận bài viết của bạn.",
                TypeFCM::NEW_COMMENT_POST,
                $checkPostExists->id
            );
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CommunityComment::where('id', $postCreated->id)->first()
        ], 200);
    }



    /**
     * Cập nhật Commet cộng đồng
     * 
     * @bodyParam content required Content
     * @bodyParam images required List danh sách ảnh sp (VD: ["linl1", "link2"])
     * 
     */
    public function update(Request $request)
    {


        $id = $request->route()->parameter('community_comment_id');
        $checkCommentExists = CommunityComment::where(
            'id',
            $id
        )->where('customer_id', $request->customer->id)
            ->where('store_id', $request->store->id)
            ->first();

        if ($checkCommentExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COMMENT_EXISTS[1],
                'msg' => MsgCode::NO_COMMENT_EXISTS[1],
            ], 400);
        }


        $checkCommentExists->update(
            [
                'content' => $request->content,
                'images_json' => json_encode($request->images),
            ]
        );


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CommunityComment::where('id', $checkCommentExists->id)->first()
        ], 200);
    }



    /**
     * Xóa comment
     * 
     * 
     */
    public function delete(Request $request)
    {

        $id = $request->route()->parameter('community_comment_id');
        $checkCommentExists = CommunityComment::where(
            'id',
            $id
        )->where('customer_id', $request->customer->id)
            ->where('store_id', $request->store->id)
            ->first();


        if ($checkCommentExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COMMENT_EXISTS[1],
                'msg' => MsgCode::NO_COMMENT_EXISTS[1],
            ], 400);
        }

        $checkCommentExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
