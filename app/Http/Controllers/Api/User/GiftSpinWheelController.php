<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\GiftSpinWheel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GiftSpinWheelController extends Controller
{
    /**
     * 
     * Danh sách gói quà
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam spin_wheel_id required spin_wheel id cần lấy.
     * @urlParam search string tìm kiếm
     * 
     */
    public function getAll(Request $request)
    {

        $search = request('search');

        $spinWheelExists = DB::table('spin_wheels')->where([
            ['store_id', $request->store->id],
            ['id', $request->spin_wheel_id]
            // ['user_id', $request->user->id]
        ])
            ->first();

        if ($spinWheelExists == null) {
            return response()->json([
                'code' => 401,
                'success' => true,
                'msg_code' => MsgCode::NO_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_SPIN_WHEEL_EXISTS[1],
            ], 401);
        }


        $listGiftSpinWheelExists = GiftSpinWheel::where([
            ['store_id', $request->store->id],
            ['spin_wheel_id', $request->spin_wheel_id]
            // ['user_id', $request->user->id]
        ])
            ->when($request->type_gift != null, function ($query) use ($request) {
                $query->where('type_gift', $request->type_gift);
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->search($search);
            })
            ->paginate(request('limit') == null ? 20 : request('limit'));


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listGiftSpinWheelExists,
        ], 200);
    }


    /**
     * xóa một quà trong 1 vòng quay
     * @urlParam store_code required Store code cần xóa.
     * @urlParam gift_spin_wheel_id required ID mã giải thưởng cần cần xóa
     */
    public function delete(Request $request)
    {
        $giftSpinWheelExists = GiftSpinWheel::where(
            [
                ['id', $request->gift_spin_wheel_id],
                // ['user_id', $request->user->id],
                ['store_id', $request->store->id]
            ]
        )
            ->first();

        if (empty($giftSpinWheelExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_GIFT_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_GIFT_SPIN_WHEEL_EXISTS[1],
            ], 404);
        }

        $idDeleted = $giftSpinWheelExists->id;
        $giftSpinWheelExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }

    /**
     * xem 1 một quà trong 1 vòng quay
     * @urlParam store_code required Store code cần xóa.
     * @urlParam gift_spin_wheel_id required ID mã giải thưởng cần cần xóa
     */
    public function getOne(Request $request)
    {
        $giftSpinWheelExists = GiftSpinWheel::where(
            [
                ['id', $request->gift_spin_wheel_id],
                // ['user_id', $request->user->id],
                ['store_id', $request->store->id]
            ]
        )
            ->first();

        if (empty($giftSpinWheelExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_GIFT_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_GIFT_SPIN_WHEEL_EXISTS[1],
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $giftSpinWheelExists,
        ], 200);
    }

    /**
     * Tạo một quà trong 1 vòng quay
     * @urlParam store_code required Store code cần update
     * @bodyParam spin_wheel_id int mã vòng quay
     * @bodyParam name String Tên quà
     * @bodyParam image_url String link ảnh
     * @bodyParam type_gift int loại quà
     * @bodyParam amount_coin double số xu
     * @bodyParam percent_received int tỉ lệ ăn quà
     * @bodyParam amount_gift int số lượng quà
     * @bodyParam value_gift string giá trị gói quà (mã sp hoặc else)
     * @bodyParam text string
     *
     */
    public function create(Request $request)
    {
        $spinWheelExists = DB::table('spin_wheels')->where([
            ['store_id', $request->store->id],
            ['id', $request->spin_wheel_id]
            // ['user_id', $request->user->id]
        ])
            ->first();

        if ($spinWheelExists == null) {
            return response()->json([
                'code' => 401,
                'success' => true,
                'msg_code' => MsgCode::NO_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_SPIN_WHEEL_EXISTS[1],
            ], 401);
        }

        try {
            $this->validate($request, [
                'percent_received' => 'integer|min:0|max:10000',
                'amount_coin' => 'integer|min:0',
                'type_gift' => 'integer|min:0',
                'amount_gift' => 'integer|min:0',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DATA[0],
                'msg' => MsgCode::INVALID_DATA[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        if (empty($request->name)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 404);
        }

        // $giftSpinWheelNameExists = DB::table('gift_spin_wheels')
        //     ->where([
        //         ['name', $request->name],
        //         ['store_id', $request->store->id]
        //     ])
        //     ->first();

        // if ($giftSpinWheelNameExists  != null) {
        //     return response()->json([
        //         'code' => 404,
        //         'success' => false,
        //         'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
        //     ], 404);
        // }

        // $totalPercentHasCreate = DB::table('gift_spin_wheels')
        //     ->where([
        //         ['store_id', $request->store->id],
        //         ['spin_wheel_id', $request->spin_wheel_id]
        //     ])
        //     ->sum('percent_received');

        // if ($totalPercentHasCreate + $request->percent_received > 100) {
        //     return response()->json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::INVALID_PERCENT_GIFT_OF_MINI_GAME[0],
        //         'msg' => MsgCode::INVALID_PERCENT_GIFT_OF_MINI_GAME[1],
        //     ], Response::HTTP_BAD_REQUEST);
        // }

        $giftSpinWheelCreate = GiftSpinWheel::create([
            'store_id' => $request->store->id,
            'spin_wheel_id' => $request->spin_wheel_id,
            // 'user_id' => $request->user->id,
            'name' => $request->name,
            'image_url' => $request->image_url,
            'type_gift' => $request->type_gift,
            'amount_coin' => $request->amount_coin,
            'percent_received' => $request->percent_received,
            'amount_gift' => $request->amount_gift,
            'value_gift' => $request->value_gift,
            'text' => $request->text
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $giftSpinWheelCreate,
        ], 200);
    }


    /**
     * update một quà trong 1 vòng quay
     * @urlParam store_code required Store code cần update
     * @urlParam gift_spin_wheel_id int mã vòng quay
     * @bodyParam name String Tên quà
     * @bodyParam image_url String link ảnh
     * @bodyParam type_gift int loại quà
     * @bodyParam amount_coin double số xu
     * @bodyParam percent_received int tỉ lệ ăn quà
     * @bodyParam amount_gift int số lượng quà
     * @bodyParam value_gift string giá trị gói quà (mã sp hoặc else)
     * @bodyParam text string
     * 
     */
    public function update(Request $request)
    {
        $giftSpinWheelExists = GiftSpinWheel::where([
            ['store_id', $request->store->id],
            ['id', $request->gift_spin_wheel_id]
            // ['user_id', $request->user->id]
        ])
            ->first();

        if ($giftSpinWheelExists == null) {
            return response()->json([
                'code' => 401,
                'success' => true,
                'msg_code' => MsgCode::NO_GIFT_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_GIFT_SPIN_WHEEL_EXISTS[1],
            ], 401);
        }

        try {
            $this->validate($request, [
                'percent_received' => 'integer|min:0|max:10000',
                'amount_coin' => 'integer|min:0',
                'type_gift' => 'integer|min:0',
                'amount_gift' => 'integer|min:0',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DATA[0],
                'msg' => MsgCode::INVALID_DATA[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        if (empty($request->name)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 404);
        }

        // $giftSpinWheelNameExists = DB::table('gift_spin_wheels')
        //     ->where([
        //         ['name', $request->name],
        //         ['store_id', $request->store->id],
        //         ['user_id', $request->user->id]
        //     ])
        //     ->first();

        // if ($giftSpinWheelNameExists  != null) {
        //     return response()->json([
        //         'code' => 404,
        //         'success' => false,
        //         'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
        //     ], 404);
        // }

        // $totalPercentHasCreate = DB::table('gift_spin_wheels')
        //     ->where([
        //         ['store_id', $request->store->id],
        //         // ['user_id', $request->user->id],
        //         ['spin_wheel_id', $request->spin_wheel_id],
        //         ['id', '<>', $request->gift_spin_wheel_id]
        //     ])
        //     ->sum('percent_received');

        // $totalPercentHasCreate = $totalPercentHasCreate + $request->percent_received;

        // if ($totalPercentHasCreate > 100) {
        //     return response()->json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::INVALID_PERCENT_GIFT_OF_MINI_GAME[0],
        //         'msg' => MsgCode::INVALID_PERCENT_GIFT_OF_MINI_GAME[1],
        //     ], Response::HTTP_BAD_REQUEST);
        // }

        $giftSpinWheelExists->update([
            'name' => $request->name,
            'image_url' => $request->image_url,
            'type_gift' => $request->type_gift,
            'amount_coin' => $request->amount_coin,
            'percent_received' => $request->percent_received,
            'amount_gift' => $request->amount_gift,
            'value_gift' => $request->value_gift,
            // 'apply_for' => $request->apply_for,
            'text' => $request->text,
            'is_lost_turn' => $request->is_lost_turn
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $giftSpinWheelExists,
        ], 200);
    }
}
