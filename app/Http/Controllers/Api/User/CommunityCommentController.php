<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Models\CommunityComment;
use App\Models\MsgCode;
use App\Models\CommunityPost;
use Illuminate\Http\Request;



/**
 * @group  User/Comment cong dong
 */
class CommunityCommentController extends Controller
{


    /**
     * Danh sách comment của 1 bài đăng
     * @queryParam community_post_id int id bài viết cần xem
     * @urlParam status integer required trạng thái  (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
     * 
     */
    public function getAll(Request $request)
    {
        $community_post_id = request('community_post_id');
        $status = request('status'); //

        if (empty($community_post_id)) {
            $all = CommunityComment::orderBy('created_at', 'desc')
                ->when(!is_null($status), function ($query) use ($status) {
                    $query->where('status', $status);
                })
                ->paginate(20);
        } else {
            $all = CommunityComment::where('community_post_id', $community_post_id)
                ->when(!is_null($status), function ($query) use ($status) {
                    $query->where('status', $status);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);
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
     * Comment bài đăng cộng đồng
     * 
     * @bodyParam community_post_id integer required id bài viết
     * @bodyParam content required Nội dung
     * 
     * 
     */
    public function create(Request $request)
    {

        $community_post_id = $request->community_post_id;

        $post = CommunityPost::where('store_id', $request->store->id)->where('id', $community_post_id)->first();
        if ($post == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[1],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        }


        $postCreated = CommunityComment::create(
            [
                'store_id' => $request->store->id,
                'community_post_id' => $request->community_post_id,
                'user_id' => $request->user == null ? null : $request->user->id,
                'staff_id' => $request->staff == null ? null : $request->staff->id,
                'content' => $request->content,
                'images_json' => json_encode($request->images),
            ]
        );
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CommunityPost::where('id', $post->id)->first()
        ], 200);
    }





    /**
     * Cập nhật Commet
     * 
     * @BodyParam content required Nội dung comment
     * @bodyParam images required List danh sách ảnh sp (VD: ["linl1", "link2"])
     * 
     */
    public function update(Request $request)
    {


        $id = $request->route()->parameter('community_comment_id');
        $checkCommentExists = CommunityComment::where('store_id', $request->store->id)->where(
            'id',
            $id
        )->first();

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
                'status' => $request->status,
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
        $checkCommentExists = CommunityComment::where('store_id', $request->store->id)->where(
            'id',
            $id
        )->first();


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
