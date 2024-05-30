<?php

namespace App\Http\Controllers;

use App\Events\RedisChatEvent;
use App\Events\RedisRealtimeBadgesEvent;
use App\Helper\Helper;
use App\Helper\TypeFCM;
use App\Jobs\PushNotificationCustomerJob;
use App\Jobs\PushNotificationJob;
use App\Jobs\PushNotificationStaffJob;
use App\Jobs\PushNotificationUserJob;
use App\Models\Customer;
use App\Models\Messages;
use App\Models\MsgCode;
use App\Models\RoomChat;
use App\Models\UserDeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Chat
 */
class RedisRealtimeController extends Controller
{


    /**
     * Chat đến khách hàng
     * Khách nhận tin nhắn khai báo io socket port 6441 nhận 
     * var socket = io("http://localhost:6441")
     * socket.on("chat:message_from_user:1", function(data) {
     *   console.log(data)
     *   })
     * chat:message:1   với 1 là customer_id

     */
    public function sendMessageCustomer(Request $request)
    {

        $customer_id = $request->route()->parameter('customer_id');

        $customer = Customer::where('id', $customer_id)->first();
        if (empty($customer)) {
            return response()->json([
                'code' => 400,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
                'success' => false,
            ]);
        }

        if ($request->content == null && $request->link_images == null) {
            return response()->json([
                'code' => 400,
                'msg_code' => MsgCode::CONTENT_IS_REQUIRED[0],
                'msg' => MsgCode::CONTENT_IS_REQUIRED[1],
                'success' => false,
            ]);
        }

        $messages = Messages::create([
            'store_id' => $request->store->id,
            'customer_id' => $customer_id,
            'content' => $request->content,
            'link_images' => $request->link_images,
            'device_id' => $request->device_id,
            'product_id' => $request->product_id,
            'is_user' => true,
        ]);

        $lastRoom = RoomChat::where('customer_id', $customer_id)->where('store_id', $request->store->id)
            ->first();
        if (!empty($lastRoom)) {
            $lastRoom->update([
                'messages_id' => $messages->id,
                'updated_at' => Helper::getTimeNowDateTime(),
                'customer_unread' => $lastRoom->customer_unread + 1,
            ]);
        } else {
            $lastRoom = RoomChat::create([
                'store_id' => $request->store->id,
                'customer_id' => $customer_id,
                'messages_id' => $messages->id,
                'updated_at' => Helper::getTimeNowDateTime(),
                'created_at' => Helper::getTimeNowDateTime(),
                'customer_unread' => 1,
            ]);
        }

        PushNotificationCustomerJob::dispatch(
            $request->store->id,
            $customer_id,
            "Bạn có tin nhắn mới",
            $request->content,
            TypeFCM::NEW_MESSAGE,
            null
        );

        event($e = new RedisChatEvent($messages, $lastRoom->customer_unread));

        return response()->json([
            'code' => 200,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => Messages::where('id', $messages->id)->first(),
        ]);
    }


    /**
     * Khách hàng chat cho user
     * Khách nhận tin nhắn reatime khai báo io socket port 6441 nhận 
     * var socket = io("http://localhost:6441")
     * socket.on("chat:message_from:customer:1", function(data) {
     *   console.log(data)
     *   })
     * chat:message:1   với 1 là customer_id
     * Lấy tin nhắn chưa đọc realtime
     *  socket.on("chat:message_from_customer",    
     */
    public function customerSendToUser(Request $request)
    {


        if ($request->content == null  && $request->link_images == null) {
            return response()->json([
                'code' => 400,
                'msg_code' => MsgCode::CONTENT_IS_REQUIRED[0],
                'msg' => MsgCode::CONTENT_IS_REQUIRED[1],
                'success' => false,
            ]);
        }

        $messages = Messages::create([
            'store_id' => $request->store->id,
            'customer_id' => $request->customer->id,
            'content' => $request->content,
            'link_images' => $request->link_images,
            'device_id' => $request->device_id,
            'product_id' => $request->product_id,
            'is_user' => false,
        ]);




        $name = $request->customer->name == null ? $request->customer->phone_number : $request->customer->name;


        PushNotificationUserJob::dispatch(
            $request->store->id,
            $request->store->user_id,
            'Shop ' . $request->store->name . ' tin nhắn từ ' . $name,
            substr($messages->content, 0, 80),
            TypeFCM::NEW_MESSAGE,
            $request->customer->id,
            null
        );
        PushNotificationStaffJob::dispatch(
            $request->store->id,
            'Shop ' . $request->store->name . ' tin nhắn từ ' . $name,
            substr($messages->content, 0, 80),
            TypeFCM::NEW_MESSAGE,
            $request->customer->id,
            null,
            null,
        );


        $lastRoom = RoomChat::where('customer_id', $request->customer->id)->where('store_id', $request->store->id)
            ->first();
        if (!empty($lastRoom)) {
            $lastRoom->update([
                'messages_id' => $messages->id,
                'updated_at' => Helper::getTimeNowDateTime(),
                'user_unread' => $lastRoom->user_unread + 1,
            ]);
        } else {
            $lastRoom = RoomChat::create([
                'store_id' => $request->store->id,
                'customer_id' => $request->customer->id,
                'messages_id' => $messages->id,
                'updated_at' => Helper::getTimeNowDateTime(),
                'created_at' => Helper::getTimeNowDateTime(),
                'user_unread' => 1,
            ]);
        }

        $unread = RoomChat::where('store_id', $request->store->id)->sum('user_unread');
        event($e = new RedisChatEvent($messages, $unread));


        event($e = new RedisRealtimeBadgesEvent($request->store->id, $request->store->user_id, $request->staff == null ? null : $request->staff->id, null));

        return response()->json([
            'code' => 200,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => Messages::where('id', $messages->id)->first(),
        ]);
    }

    /**
     * Danh sách tổng quan tin nhắn
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     **/

    public function getAll(Request $request, $id)
    {

        $posts = RoomChat::where(
            'store_id',
            $request->store->id
        )
            ->orderBy('updated_at', 'desc')
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
     * Danh sách tin nhắn với 1 khách
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     **/

    public function getAllOneCustomer(Request $request, $id)
    {
        $customer_id = $request->route()->parameter('customer_id');

        $customer = Customer::where('id', $customer_id)->first();
        if (empty($customer)) {
            return response()->json([
                'code' => 400,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
                'success' => false,
            ]);
        }

        $messages = Messages::where(
            'store_id',
            $request->store->id
        )->where(
            'customer_id',
            $customer_id
        )->orderBy('created_at', 'desc')
            ->paginate(20);

        $lastRoom = RoomChat::where('customer_id', $customer_id)->where('store_id', $request->store->id)
            ->first();
        if (!empty($lastRoom)) {
            $lastRoom->update([
                'user_unread' => 0,
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $messages,
        ], 200);
    }

    /**
     * Danh sách tin nhắn với user
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     **/

    public function getAllMessageOfCustomer(Request $request, $id)
    {

        $messages = Messages::where(
            'store_id',
            $request->store->id
        )->where(
            'customer_id',
            $request->customer->id
        )->orderBy('created_at', 'desc')
            ->paginate(20);

        $lastRoom = RoomChat::where('customer_id', $request->customer->id)->where('store_id', $request->store->id)
            ->first();
        if (!empty($lastRoom)) {
            $lastRoom->update([
                'customer_unread' => 0,
            ]);
        }
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $messages,
        ], 200);
    }
}
