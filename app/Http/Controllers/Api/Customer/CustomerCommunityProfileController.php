<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\MsgCode;
use App\Models\Customer;
use App\Models\CustomerFriend;
use App\Models\FriendRequest;
use Illuminate\Http\Request;


/**
 * @group  Customer/Thông tin 1 người trong cộng đồng
 */

class CustomerCommunityProfileController extends Controller
{

    /**
     * Thông tin tổng quan
     * 
     * @urlParam customer_id required Nếu là customer id
     * 
     */
    public function getInfoOverview(Request $request)
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

        $total_images = 0;
        $total_friends = CustomerFriend::where('customer_id',  $request->customer->id)
        ->where('store_id', $request->store->id)
        ->count();

        $sent_friend_request = FriendRequest::where('customer_id',  $request->customer->id)
            ->where('store_id', $request->store->id)
            ->where('to_customer_id',    $customer_id)
            ->first() != null;

        $is_friend =  CustomerFriend::where('customer_id',  $request->customer->id)
            ->where('store_id', $request->store->id)
            ->where('friend_customer_id',    $customer_id)
            ->first() != null;

        $data = [
            'total_friends' => $total_friends,
            'total_images' => $total_images,
            'sent_friend_request' =>  $sent_friend_request,
            'is_friend' =>  $is_friend,
            'customer' =>  $customerExists
        ];


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $data
        ], 200);
    }
}
