<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\SearchHistory;
use Illuminate\Http\Request;

/**
 * @group  Customer/Lịch sử tìm kiếm sản phẩm
 */
class CustomerSearchHistoryController extends Controller
{
    /**
     * @group  Thêm vào lịch sử 
     * @bodyParam text string required Nội dung tìm kiếm
     * @bodyParam device_id trường hợp chưa đăng nhập
     */
    public function create(Request $request)
    {

        $device_id = request('device_id') ?? null;


        $created = null;

        if ($request->text == null) {
            return response()->json([
                'code' => 400,
                'success' => true,
                'msg_code' => MsgCode::TEXT_IS_REQUIRED[0],
                'msg' => MsgCode::TEXT_IS_REQUIRED[1],
            ], 400);
        }


        if ($request->customer != null) {
            $created = SearchHistory::create(
                [
                    'store_id' => $request->store->id,
                    'customer_id' => $request->customer->id,
                    'text' => $request->text,
                    'status' => 0
                ]
            );
        } else if ($device_id  != null) {
            $created = SearchHistory::create(
                [
                    'store_id' => $request->store->id,
                    'text' => $request->text,
                    'status' => 0,
                    'device_id' =>  $device_id
                ]
            );
        } else {
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => []
            ], 200);
        }



        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $created
        ], 201);
    }

    /**
     * Danh sách lịch sử tìm kiếm
     * @urlParam device_id trường hợp chưa đăng nhập (có vẫn ưu tiên đã đăng nhập)
     * 
     */

    public function get10Item(Request $request, $id)
    {
        $device_id = request('device_id') ?? null;


        $list = null;

        if ($request->customer  != null) {
            $list = SearchHistory::where('store_id', $request->store->id)
                ->where('customer_id',  $request->customer->id)
                ->where('status', 0)
                ->orderBy('created_at', 'desc')
                ->groupBy('text')
                ->limit(10)
                
                ->get();
        } else if ($device_id  != null) {
            $list = SearchHistory::where('store_id', $request->store->id)
                ->where('device_id',  $device_id)
                ->where('status', 0)
                ->orderBy('created_at', 'desc')
                ->groupBy('text')
                ->limit(10)
                ->get();
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $list,
        ], 200);
    }

    /**
     * Xóa lịch sử tìm kiếm
     * @urlParam device_id trường hợp chưa đăng nhập (có vẫn ưu tiên đã đăng nhập)
     */

    public function hideAll(Request $request, $id)
    {
        $device_id = request('device_id') ?? null;

        $request = request();


        if ($request->customer  != null) {
            SearchHistory::where('store_id', $request->store->id)
                ->where('customer_id', $request->customer->id)
                ->update([
                    'status' => -1
                ]);
        } else if ($device_id  != null) {
            SearchHistory::where('store_id', $request->store->id)
                ->where('device_id',  $device_id)
                ->update([
                    'status' => -1
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
