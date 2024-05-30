<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\NotificationUser;
use Illuminate\Http\Request;

/**
 * @group  User/Thông báo
 */

class NotificationController extends Controller
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

        $notis = NotificationUser::where(
            'store_id',
            $request->store->id
        )->orderBy('created_at', 'desc')
            ->where(function ($query) use ($request) {
                $query->when($request->branch != null, function ($query) use ($request) {
                    $query->where(
                        'branch_id',
                        $request->branch->id
                    );
                })
                    ->orWhere(
                        'branch_id',
                        null
                    );
            })
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
        NotificationUser::where('store_id', $request->store->id)
        ->where(function ($query) use ($request) {
            $query->when($request->branch != null, function ($query) use ($request) {
                $query->where(
                    'branch_id',
                    $request->branch->id
                );
            })
                ->orWhere(
                    'branch_id',
                    null
                );
        })
            ->update(['unread' => false]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
