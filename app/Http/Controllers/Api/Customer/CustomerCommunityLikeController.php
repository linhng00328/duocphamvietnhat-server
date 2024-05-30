<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\CommunityLike;
use App\Models\CommunityPost;
use App\Models\Like;
use App\Models\MsgCode;
use App\Models\Post;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

/**
 * @group  Customer/Like cộng đồng
 */
class CustomerCommunityLikeController extends Controller
{


    /**
     * Like bài đăng
     * 
     * @bodyParam community_post_id integer required id bài viết
     * @bodyParam is_like required boolean
     * 
     * 
     */
    public function create(Request $request)
    {

        $post = CommunityPost::where('id', $request->community_post_id)->where('store_id', $request->store->id)->first();

        if($post == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[1],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 400);
        }

        if($request->customer == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[1],
                'msg' => "Hãy đăng nhập để thích bài đăng",
            ], 400);
        }

        $is_like =  filter_var($request->is_like, FILTER_VALIDATE_BOOLEAN);

        if ($is_like == false) {
            $l = CommunityLike::where('community_post_id', $request->community_post_id)
            ->where('customer_id', $request->customer->id)
            ->where('store_id', $request->store->id)
            ->delete();
        } else {
            $l = CommunityLike::where('community_post_id', $request->community_post_id)
                ->where('customer_id', $request->customer->id)
                ->where('store_id', $request->store->id)
                ->first();
            if ($l != null) {
                $l->update([
                    'created_at' => Helper::getTimeNowString()
                ]);
            } else {
                CommunityLike::create([
                    'store_id' =>  $request->store->id,
                    'community_post_id' => $request->community_post_id,
                    'customer_id' => $request->customer->id
                ]);
            }
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $post 
        ], 200);
    }

}
