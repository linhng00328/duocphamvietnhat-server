<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\CommunityPost;
use App\Models\Customer;
use DateTime;
use Illuminate\Http\Request;


/**
 * @group  User/Quản lý bài đăng cộng đồng
 */

class CommunityPostController extends Controller
{


    /**
     * Danh sách bài đăng
     * 
     * @queryParam customer_id required Nếu là user thì tự động lấy ds thuộc customer, còn admin thì truyền lên
     * @queryParam search required Tìm theo tiêu đề
     * @urlParam status integer required trạng thái  (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
     * 
     * 
     */
    public function getAll(Request $request)
    {

        $search = StringUtils::convert_name_lowcase(request('search'));
        $status = request('status'); //
        $customer_id = request('customer_id'); //
        $is_pin = request('is_pin'); //

        $all = CommunityPost::where('store_id', $request->store->id)

            ->when(!is_null($customer_id), function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id);
            })
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!is_null($is_pin), function ($query) use ($is_pin) {
                $is_pin = filter_var($is_pin, FILTER_VALIDATE_BOOLEAN);
                $query->where('is_pin',  $is_pin);
            })
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })

            ->when($status == 1, function ($query) use ($status) {
                $query->orderBy('time_repost', 'asc');
            })
            ->when($status != 1, function ($query) use ($status) {
                $query->orderBy('time_repost', 'desc');
            })

            ->search($search)
            ->paginate(20);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $all
        ], 200);
    }

    /**
     * Thêm bài đăng cộng đồng
     * 
     * @bodyParam name required tên bài đăng
     * @bodyParam content required nội dung
     * @bodyParam status  required  (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
     * @bodyParam images required List danh sách ảnh sp (VD: ["linl1", "link2"])
     * @bodyParam feeling  required Cảm xúc
     * @bodyParam checkin_location  required Vị trí checkin
     * @bodyParam background_color  required Màu nền
     * 
     */
    public function create(Request $request)
    {


        if (empty($request->name)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[1],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }
        $date = Helper::getTimeNowDateTime();


        $postCreated = CommunityPost::create(
            [
                'store_id' => $request->store->id,
                'user_id' => $request->user->id,
                'name' =>  $request->name,
                'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
                'content' => $request->content,
                'images_json' => json_encode($request->images),
                'status' => $request->status ?? 1,
                'time_repost' =>  $date->getTimestamp(),
                'created_at' => Helper::getTimeNowString(),

                'feeling' => $request->feeling,
                'checkin_location' => $request->checkin_location,
                'background_color' => $request->background_color,
            ]
        );


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CommunityPost::where('id', $postCreated->id)->first()
        ], 200);
    }

    /**
     * Cập nhật bài đăng cộng đồng
     * 
     * @bodyParam content required Nội dung
     * @bodyParam status  required  (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
     * @bodyParam images required List danh sách ảnh sp (VD: ["linl1", "link2"])
     * @bodyParam feeling  required Cảm xúc
     * @bodyParam checkin_location  required Vị trí checkin
     * @bodyParam background_color  required Màu nền
     * 
     */
    public function update(Request $request)
    {

        $id = $request->route()->parameter('community_post_id');
        $checkPostExists = CommunityPost::where('store_id', $request->store->id)

            ->where(
                'id',
                $id
            )->first();

        if ($checkPostExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[1],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 400);
        }


        $checkPostExists->update(
            [
                'name' =>  $request->name,
                'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
                'content' => $request->content,
                'images_json' => json_encode($request->images),
                'status' => $request->status ?? 1,
                // 'time_repost' =>  $request->time_repost == null ? $checkCustomerExists->time_repost : Helper::getTimeNowString(),
                'feeling' => $request->feeling,
                'checkin_location' => $request->checkin_location,
                'background_color' => $request->background_color,
            ]
        );


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CommunityPost::where('id', $checkPostExists->id)->first()
        ], 200);
    }

    /**
     * Đăng lại lên top
     * 
     */
    public function reup(Request $request)
    {


        $id = $request->route()->parameter('community_post_id');
        $checkPostExists = CommunityPost::where('store_id', $request->store->id)

            ->where(
                'id',
                $id
            )->first();

        if ($checkPostExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[1],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 400);
        }


        $checkPostExists->update(
            [
                'status' => 0,
                'time_repost' => Helper::getTimeNowString(),
            ]
        );


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CommunityPost::where('id', $checkPostExists->id)->first()
        ], 200);
    }

    /**
     * Xóa Cần mua cần bán
     * 
     * 
     */
    public function delete(Request $request)
    {

        $id = $request->route()->parameter('community_post_id');
        $checkPostExists = CommunityPost::where('store_id', $request->store->id)

            ->where(
                'id',
                $id
            )->first();

        if ($checkPostExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[1],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 400);
        }
        $checkPostExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Lấy 1 bài
     * 
     * 
     */
    public function getOne(Request $request)
    {

        $id = $request->route()->parameter('community_post_id');
        $checkPostExists = CommunityPost::where('store_id', $request->store->id)

            ->where(
                'id',
                $id
            )->first();

        if ($checkPostExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[1],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 400);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $checkPostExists
        ], 200);
    }


    function resetSort()
    {
        $allPost = CommunityPost::get();
        foreach ($allPost as $post) {
            if ($post->is_pin == true) {
            }

            if ($post->is_pin == false) {
                $post->update([
                    'position_pin' =>  null
                ]);
            }
        }
    }

    /**
     * Ghim bài
     * 
     * @bodyParam community_post_id required id bài viết
     * @bodyParam is_pin required is_pin
     */
    public function ghim(Request $request)
    {

        $id = $request->community_post_id;
        $checkPostExists = CommunityPost::where('store_id', $request->store->id)

            ->where(
                'id',
                $id
            )->first();

        if ($checkPostExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[1],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 400);
        }

        $date = new DateTime();

        $timePin = Helper::getTimeNowDateTime();
        $timePin->modify('+3 years');

        $time_repost =  null;
        if ($request->is_pin == true) {
            $time_repost =  $request->is_pin == true ? $timePin : $checkPostExists->created_at;
            if ($checkPostExists->created_at == $request->time_repost) {
                $time_repost =   $request->time_repost;
            } else {
                $time_repost =   $timePin;
            }
        } else {
            $time_repost =  $checkPostExists->created_at;
        }

        $checkPostExists->update(
            [
                'is_pin' =>  $request->is_pin,
                'position_pin' =>   $date->getTimestamp(),
                'status' => $request->is_pin == true ? 0 :  $checkPostExists->status,
                'time_repost' =>  $time_repost->getTimestamp(),
            ]
        );

        $this->resetSort();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CommunityPost::where('id', $checkPostExists->id)->first()
        ], 200);
    }
}
