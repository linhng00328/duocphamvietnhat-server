<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\Helper;
use App\Helper\StringUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Models\CommunityPost;
use App\Models\Customer;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  User/Bài đăng cộng đồng
 */
class CustomerCommunityPostController extends Controller
{

    /**
     * Danh sách Bài đăng cộng đồng
     * 
     * @queryParam search required Tìm tên bài đăng
     * @urlParam status integer required  trạng thái 1 chờ duyệt, 0 đã duyệt, 2 đã ẩn
     * @bodyParam privacy required Quyền riêng tư (0 tất cả/ 1 chỉ mình tôi/ 2 bạn bè)
     * 
     */
    public function getAllHome(Request $request)
    {
        $search = StringUtils::convert_name_lowcase(request('search'));
        $is_pin = request('is_pin'); //

        $all = CommunityPost::where(function ($query) use ($request) {
            $query->where('status',  0)
                ->orWhere('customer_id', $request->customer == null ? null : $request->customer->id);
        })
            ->when(!empty($is_pin), function ($query) use ($is_pin) {
                $is_pin = filter_var($is_pin, FILTER_VALIDATE_BOOLEAN);
                $query->where('is_pin',  $is_pin);
            })->where('store_id', $request->store->id)
            // 

            ->orderBy('time_repost', 'desc')

            // ->orderBy('position_pin', 'desc')
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
     * Danh sách Bài đăng của người khác
     * 
     * @queryParam search required Tìm tên bài đăng
     * 
     * 
     */
    public function getAllOfCustomerOther(Request $request)
    {
        $customer_id = $request->route()->parameter('customer_id');

        $customerExists = Customer::where('id', $customer_id)->where('store_id', $request->store->id)->first();
        if ($customerExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[1],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 400);
        }

        $search = StringUtils::convert_name_lowcase(request('search'));
        $status = request('status'); //
        $is_pin = request('is_pin'); //

        $all = CommunityPost::where('store_id', $request->store->id)
            ->where('customer_id', $customer_id)
            ->where(function ($query)  use ($request) {
                $query->where('status', 0)
                    ->orWhere('customer_id', $request->customer->id);
            })
            ->when(!empty($is_pin), function ($query) use ($is_pin) {
                $is_pin = filter_var($is_pin, FILTER_VALIDATE_BOOLEAN);
                $query->where('is_pin',  $is_pin);
            })
            ->orderBy('created_at', 'desc')
            // ->orderBy('is_pin', 'desc')
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
     * Danh sách Bài đăng cộng đồng của tôi
     * 
     * @queryParam search required Tìm tên bài đăng
     * @urlParam status integer required  trạng thái 1 chờ duyệt, 0 đã duyệt, 2 đã ẩn
     * 
     * 
     */
    public function getAll(Request $request)
    {
        $is_buy = filter_var(request('is_buy'), FILTER_VALIDATE_BOOLEAN); //
        $search = StringUtils::convert_name_lowcase(request('search'));
        $status = request('status'); //
        $is_pin = request('is_pin'); //

        $all = CommunityPost::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!empty($is_pin), function ($query) use ($is_pin) {
                $is_pin = filter_var($is_pin, FILTER_VALIDATE_BOOLEAN);
                $query->where('is_pin',  $is_pin);
            })
            ->orderBy('time_repost', 'desc')
            // ->orderBy('is_pin', 'desc')
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
     * @bodyParam name required tên sản phẩm
     * @bodyParam content required nội dung
     * @bodyParam status  required (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
     * @bodyParam images required List danh sách ảnh sp (VD: ["linl1", "link2"])
     * @bodyParam time_repost required thời gian đăng lại
     * @bodyParam privacy required Quyền riêng tư (0 tất cả/ 1 chỉ mình tôi/ 2 bạn bè)
     * 
     * 
     */
    public function create(Request $request)
    {

        if (empty($request->content)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CONTENT_IS_REQUIRED[1],
                'msg' => MsgCode::CONTENT_IS_REQUIRED[1],
            ], 400);
        }


        $images = [];
        if ($request->images != null && is_array($request->images)) {
            foreach ($request->images as $image) {
                array_push($images, $image);
            }
        }

        $date = Helper::getTimeNowDateTime();

        $postCreated = CommunityPost::create(
            [
                'store_id' => $request->store->id,
                'customer_id' => $request->customer->id,
                'name' =>  $request->name,
                'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
                'content' => $request->content,
                'images_json' => json_encode($images),
                'status' => 1,
                'time_repost' =>  $date->getTimestamp(),
                'created_at' => Helper::getTimeNowString(),
                'background_color' => $request->background_color,
                'feeling' => $request->feeling,
                'privacy' => $request->privacy,
            ]
        );


        PushNotificationUserJob::dispatch(
            $request->store->id,
            $request->store->user_id,
            'Bài đăng mới',
            'Khách hàng ' . $request->customer->name . ' đăng một bài viết mới ',
            TypeFCM::NEW_POST_COMMUNITY,
            $request->customer->id,
            null
        );
        // PushNotificationAdminJob::dispatch(
        //     "Bài đăng" . ($is_buy ? " cần mua " : " cần bán ") . "mới",
        //     "Sản phẩm: " . ($request->name),
        //     $is_buy ?   TypeFCM::NEW_POST_BUY : TypeFCM::NEW_POST_SELL,
        //     $postCreated->id,
        //     $postCreated->name,
        // );


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CommunityPost::where('id', $postCreated->id)->first()
        ], 200);
    }



    /**
     * Cập nhật bài đăng
     * 
     * @bodyParam name required tên sản phẩm
     * @bodyParam content required Nội dung
     * @bodyParam status  required (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
     * @bodyParam images required List danh sách ảnh sp (VD: ["linl1", "link2"])
     * @bodyParam is_pin required ghim hay không 
     * @bodyParam time_repost required thời gian đăng lại
     * @bodyParam privacy required Quyền riêng tư (0 tất cả/ 1 chỉ mình tôi/ 2 bạn bè)
     */
    public function update(Request $request)
    {
        $id = $request->route()->parameter('community_post_id');
        $checkPostExists = CommunityPost::where('store_id', $request->store->id)->where(
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

        if (empty($request->content)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CONTENT_IS_REQUIRED[1],
                'msg' => MsgCode::CONTENT_IS_REQUIRED[1],
            ], 400);
        }

        $images = [];


        if ($request->images != null && is_array($request->images)) {
            foreach ($request->images as $image) {
                array_push($images, $image);
            }
        }


        $checkPostExists->update(
            [
                'customer_id' => $request->customer->id,
                'name' =>  $request->name,
                'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
                'content' => $request->content,
                'is_pin' => $request->is_pin,
                'images_json' => json_encode($images),
                'status' => $request->status ?? 1,
                'background_color' => $request->background_color,
                'feeling' => $request->feeling,
                'privacy' => $request->privacy,
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
     * Thông tin 1 bài
     */
    public function getOne(Request $request)
    {


        $id = $request->route()->parameter('community_post_id');
        $checkPostExists = CommunityPost::where('store_id', $request->store->id)->where(
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
        $checkPostExists = CommunityPost::where('store_id', $request->store->id)->where(
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
                'status' => 1,
                'time_repost' => Helper::getTimeNowString(),
            ]
        );

        // $is_buy =  $checkPostExists->is_buy;

        // PushNotificationAdminJob::dispatch(
        //     "Yêu cầu đăng lại bài" . ($is_buy ? " cần mua " : " cần bán "),
        //     "Sản phẩm: " . ($checkPostExists->name),
        //     $is_buy ?   TypeFCM::REUP_BUY : TypeFCM::REUP_SELL,
        //     $checkPostExists->id,
        //     $checkPostExists->name,
        // );

        // TotalPostDay::addTotalCurrent($request->user->id);

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
        $checkPostExists = CommunityPost::where('store_id', $request->store->id)->where(
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
}
