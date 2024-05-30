<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Customer;
use App\Models\CustomerFriend;
use App\Models\FriendRequest;
use Illuminate\Http\Request;


/**
 * @group  User/Danh sách bạn bè
 */

class CustomerCommunityFriendController extends Controller
{



    /**
     * Danh sách bạn bè của 1 người
     * 
     * @queryParam customer_id required Nếu là customer id
     * @queryParam search required Tìm tên sdt
     * 
     */
    public function getAllFriendOfCustomer(Request $request)
    {
        $customer_id = request('customer_id');

        $customerExists = Customer::where('id',   $customer_id)->where('store_id', $request->store->id)->first();
        if ($customerExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[1],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 400);
        }

        $search = StringUtils::convert_name_lowcase(request('search'));
        $list_friends_ids =  CustomerFriend::where('customer_id',   $customer_id)
            ->where('store_id', $request->store->id)->pluck('friend_customer_id');

        $all = Customer::where('store_id', $request->store->id)

            ->whereIn('id',  $list_friends_ids)
            ->orderBy('created_at', 'desc')
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
     * Danh sách bạn bè
     * 
     * @queryParam customer_id required Nếu là customer id
     * @queryParam search required Tìm tên sdt
     * 
     */
    public function getAll(Request $request)
    {

        $search = StringUtils::convert_name_lowcase(request('search'));
        $list_friends_ids =  CustomerFriend::where('customer_id', $request->customer->id)
            ->where('store_id', $request->store->id)->pluck('friend_customer_id');

        $all = Customer::where('store_id', $request->store->id)

            ->whereIn('id',  $list_friends_ids)
            ->orderBy('created_at', 'desc')
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
     * Danh sách bạn bè
     * 
     * @queryParam customer_id required Nếu là customer id
     * 
     */
    public function cancelFriend(Request $request)
    {

        $customer_id = request('customer_id');

        CustomerFriend::where('customer_id', $request->customer->id)->where('friend_customer_id',   $customer_id)->delete();
        CustomerFriend::where('customer_id', $customer_id)->where('friend_customer_id',   $request->customer->id)->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Hủy yêu cầu kết bạn
     * 
     * @bodyParam customer_id required Nếu là customer id
     * 
     */
    public function deleteRequestFriend(Request $request)
    {
        $customer_id = request('customer_id');


        $checkHasRequest = FriendRequest::where('customer_id', $request->customer->id)
            ->where('to_customer_id', $customer_id)->where('store_id', $request->store->id)->first();

        if ($checkHasRequest  != null)  $checkHasRequest->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Gửi yêu cầu kết bạn
     * 
     * @bodyParam to_customer_id required Nếu là customer id
     * @bodyParam content required Nội dung yêu cầu
     * 
     */
    public function requestFriend(Request $request)
    {
        $to_customer_id = request('to_customer_id');

        $customerExists = Customer::where('id',   $to_customer_id)->where('store_id', $request->store->id)->first();
        if ($customerExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[1],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 400);
        }

        if ($to_customer_id == $request->customer->id) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[1],
                'msg' => "Không thể gửi kết bạn cho bản thân",
            ], 400);
        }

        $isFriend = CustomerFriend::where('customer_id',  $request->customer->id)
            ->where('store_id', $request->store->id)
            ->where('friend_customer_id',   $to_customer_id)
            ->first() != null;
        if ($isFriend  == true) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[1],
                'msg' => "Đã là bạn bè từ trước",
            ], 400);
        }

        $checkHasRequest = FriendRequest::where('customer_id', $request->customer->id)
            ->where('to_customer_id', $to_customer_id)->where('store_id', $request->store->id)->first();

        if ($checkHasRequest == null) {
            FriendRequest::create([
                'store_id' => $request->store->id,
                'to_customer_id' =>  $to_customer_id,
                'customer_id' => $request->customer->id,
                'content' => $request->content,
            ]);
        } else {
            $checkHasRequest->update([
                'store_id' => $request->store->id,
                'to_customer_id' =>  $to_customer_id,
                'customer_id' => $request->customer->id,
                'content' => $request->content,
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Danh sách yêu cầu kết bạn
     * 
     * @urlParam request_id required Request id
     * @bodyParam status int Hành động 0 xóa 1 đồng ý kết bạn
     * 
     */
    public function getAllRequestFriend(Request $request)
    {

        $all = FriendRequest::where('to_customer_id',  $request->customer->id)
            ->where('store_id', $request->store->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $all
        ], 200);
    }

    /**
     * Xử lý yêu cầu bạn bè
     * 
     * @urlParam request_id required Request id
     * @bodyParam status int Hành động 0 xóa 1 đồng ý kết bạn
     * 
     */
    public function handleRequest(Request $request)
    {
        $request_id = request('request_id');

        $requestExists = FriendRequest::where('id',  $request_id)
            ->where('to_customer_id',  $request->customer->id)
            ->where('store_id', $request->store->id)->first();

        if ($requestExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_REQUEST_FRIEND_EXISTS[1],
                'msg' => MsgCode::NO_REQUEST_FRIEND_EXISTS[1],
            ], 400);
        }


        if ($request->status == 1) {
            $isFriend = CustomerFriend::where('customer_id',  $requestExists->customer_id)
                ->where('store_id', $request->store->id)
                ->where('friend_customer_id',    $requestExists->to_customer_id)
                ->first() != null;
            if ($isFriend  == true) {
            } else {
                CustomerFriend::create([
                    'store_id' => $request->store->id,
                    'friend_customer_id' =>  $requestExists->to_customer_id,
                    'customer_id' => $requestExists->customer_id,
                ]);

                $isFriend2 = CustomerFriend::where('customer_id', $requestExists->to_customer_id)
                    ->where('friend_customer_id',   $requestExists->customer_id)
                    ->where('store_id', $request->store->id)
                    ->first() != null;

                if ($isFriend2  == false) {
                    CustomerFriend::create([
                        'store_id' => $request->store->id,
                        'friend_customer_id' => $requestExists->customer_id,
                        'customer_id' => $requestExists->to_customer_id,
                    ]);
                }
            }
            $requestExists->delete();
        }

        if ($request->status == 0) {

            $requestExists->delete();
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
