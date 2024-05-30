<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\NotificationCustomer;
use App\Models\NotificationUser;
use Illuminate\Http\Request;

/**
 * @group  Customer/Thông báo
 */

class CustomerNotificationController extends Controller
{
    /**
     * Danh sách thông báo
     * 
     * total_unread số chưa đọc
     * 
     * page số trang

     */
    public function getAll(Request $request)
    {

        if ($request->customer == null) {
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => [
                    "total_unread" => 0,
                    "list_notification" => [
                        "current_page" => 1,
                        "data" => []
                    ]
                ],
            ], 200);
        }

        $notis = NotificationCustomer::where('customer_id', $request->customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "total_unread" => 0,
                "list_notification" => $notis
            ],
        ], 200);
    }

    /**
     * Đã đọc tất cả
     * 

     */
    public function readAll(Request $request)
    {
        if ($request->customer != null) {
            NotificationCustomer::where('store_id', $request->store->id)
                ->where('customer_id', $request->customer->id)
                ->update([
                    'unread' => false,
                ]);
            $request->customer->update([
                'notifications_count' => 0
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
