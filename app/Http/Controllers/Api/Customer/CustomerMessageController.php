<?php

namespace App\Http\Controllers\Api\Customer;

use App\Events\RedisChatEventCustomerToCustomer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CToCMessage;
use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\PersonChat;

/**
 * @group  Customer/Chat
 */

class CustomerMessageController extends Controller
{

    /**
     * Danh sách người chat với customer
     * 
     */
    public function getAllPerson(Request $request)
    {

        $all = PersonChat::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)
            ->orderBy('updated_at', 'desc')
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
     * Danh sách tin nhắn với 1 người
     * @bodyParam content required Nội dung
     * @bodyParam images required List danh sách ảnh sp (VD: ["linl1", "link2"])
     * 
     * 
     */
    public function getAllMessage(Request $request)
    {
        $to_customer_id = $request->route()->parameter('to_customer_id');

        $customer = Customer::where('id',   $to_customer_id)->where('store_id', $request->store->id)->first();

        if ($customer == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[1],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 400);
        }

        $all = CToCMessage::where('store_id', $request->store->id)
            ->where('vs_customer_id', $to_customer_id)
            ->orderBy('created_at', 'desc')
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
     * Gửi tin nhắn
     * 
     * @bodyParam content required Nội dung
     * @bodyParam images required List danh sách ảnh sp (VD: ["linl1", "link2"])
     * Khách nhận tin nhắn reatime khai báo io socket port 6441 nhận 
     * var socket = io("http://localhost:6441")
     * socket.on("chat:message_from_customer_to_customer:1:2", function(data) {   (1:2   1 là từ customer nào gửi tới cusotmer nào nếu đang cần nhận thì 1 là người cần nhận 2 là id của bạn)
     *   console.log(data)
     *   })
     * chat:message:1   với 1 là customer_id
     * 
     * 
     * 
     */
    public function sendMessage(Request $request)
    {
        $to_customer_id = $request->route()->parameter('to_customer_id');

        $customer = Customer::where('id',   $to_customer_id)->where('store_id', $request->store->id)->first();

        if ($customer == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[1],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 400);
        }


        if ($request->images == null && empty($request->content)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CONTENT_IS_REQUIRED[1],
                'msg' => MsgCode::CONTENT_IS_REQUIRED[1],
            ], 400);
        }


        //thêm mess cho người gửi
        $mess = CToCMessage::create([
            "store_id" => $request->store->id,
            "customer_id" => $request->customer->id,
            "vs_customer_id" =>  $to_customer_id,
            "content" => $request->content,
            'is_sender' => true,
            'images_json' => json_encode($request->images),
        ]);

        event($e = new RedisChatEventCustomerToCustomer($mess, 1));

        $personChat =  PersonChat::where('customer_id', $request->customer->id)->where('to_customer_id', $to_customer_id)->first();
        if ($personChat  != null) {
            $personChat->update([
                "last_mess" => $request->content,
                'seen' => true,
            ]);
        } else {
            PersonChat::create([
                'store_id' => $request->store->id,
                "customer_id" => $request->customer->id,
                "to_customer_id" => $to_customer_id,
                "last_mess" => $request->content,
                'seen' => true,
            ]);
        }

        //thêm mess cho người nhận
        $mess2 = CToCMessage::create([
            "store_id" => $request->store->id,
            "vs_customer_id" => $request->customer->id,
            "customer_id" =>  $to_customer_id,
            "content" => $request->content,
            'is_sender' => false,
            'images_json' => json_encode($request->images),
        ]);

        $personChat2 =  PersonChat::where('customer_id', $to_customer_id)->where('to_customer_id', $request->customer->id)->first();
        if ($personChat2  != null) {
            $personChat2->update([
                "last_mess" => $request->content,
                'seen' => true,
            ]);
        } else {
            PersonChat::create([
                'store_id' => $request->store->id,
                "customer_id" =>  $to_customer_id,
                "to_customer_id" => $request->customer->id,
                "last_mess" => $request->content,
                'seen' => false,
            ]);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $mess
        ], 200);
    }
}
