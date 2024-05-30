<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CustomerUtils;
use App\Helper\Helper;
use App\Helper\StatusGuessNumberDefineCode;
use App\Helper\StatusSpinWheelDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\GuessNumber;
use App\Models\MsgCode;
use App\Models\SpinWheel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SpinWheelController extends Controller
{
    /**
     * 
     * Danh sách vòng quay
     * 
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam time_start datetime thời gian bắt đầu
     * @urlParam time_end datetime thời gian kết thúc
     * @urlParam status int trạng thái mini game vòng quay
     * @urlParam search string tìm kiếm
     * 
     */
    public function getAll(Request $request)
    {

        $search = request('search');

        $timeStart = Helper::createAndValidateFormatDate($request->time_start, 'y-m-d H:i:s');
        $timeEnd = Helper::createAndValidateFormatDate($request->time_end, 'y-m-d H:i:s');

        $spinWheelExists = SpinWheel::where([
            ['store_id', $request->store->id]
            // ['user_id', $request->user->id]
        ])
            ->when($timeStart != false || $timeEnd != false, function ($query) use ($timeStart, $timeEnd) {
                if ($timeStart) {
                    $query->where('time_start', '<=', $timeStart);
                }
                if ($timeEnd) {
                    $query->where('time_end', '>=', $timeEnd);
                }
            })
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->is_shake != null, function ($query) use ($request) {
                $query->where('is_shake', filter_var($request->is_shake, FILTER_VALIDATE_BOOLEAN));
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
            'data' => $spinWheelExists,
        ], 200);
    }


    /**
     * xóa một vòng quay mini game
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  spin_wheel_id required ID mã vòng xoay cần cần xóa
     */
    public function delete(Request $request)
    {
        $checkSpinWheelExists = SpinWheel::where(
            [
                ['id', $request->spin_wheel_id],
                ['store_id', $request->store->id]
                // ['user_id', $request->user->id]
            ]
        )
            ->first();

        if (empty($checkSpinWheelExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_SPIN_WHEEL_EXISTS[1],
            ], 404);
        }

        $idDeleted = $checkSpinWheelExists->id;
        $checkSpinWheelExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }

    /**
     * xem 1 một vòng quay mini game
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  spin_wheel_id required ID mã vòng xoay cần cần xóa
     */
    public function getOne(Request $request)
    {
        $checkSpinWheelExists = SpinWheel::where(
            [
                ['id', $request->spin_wheel_id],
                ['store_id', $request->store->id]
                // ['user_id', $request->user->id]
            ]
        )
            ->first();

        SpinWheel::where([
            ['time_start', '<=', Helper::getTimeNowCarbon()],
            ['time_end', '>=', Helper::getTimeNowCarbon()],
            ['status', StatusSpinWheelDefineCode::PROGRESSING]
        ])
            ->update([
                'status' => StatusSpinWheelDefineCode::COMPLETED
            ]);

        if (empty($checkSpinWheelExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_SPIN_WHEEL_EXISTS[1],
            ], 404);
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $checkSpinWheelExists,
        ], 200);
    }

    /**
     * Tạo một vòng quay mini game
     * @urlParam  store_code required Store code cần update
     * @bodyParam name String Tên vòng quay
     * @bodyParam images Array danh sách ảnh
     * @bodyParam turn_in_day int lượt chơi trong ngày
     * @bodyParam time_start Datetime thời gian bắt đầu
     * @bodyParam time_end Datetime thời gian kết thúc
     * @bodyParam status Int trạng thái [0: chờ xử lý, 2: hoàn tất]
     * @bodyParam is_shake Boolean 
     * @bodyParam group_customer_id Int mã nhóm khách hàng
     * @bodyParam agency_type_id Int mã cấp đại lý
     * @bodyParam apply_for int chương trình áp dụng cho [0: all, 1: group customer]
     * @bodyParam is_limit_people Boolean Giới hạn người chơi
     * @bodyParam number_limit_people Int  
     * @bodyParam max_amount_coin_per_player Array 
     * @bodyParam max_amount_gift_per_player Array 
     * @bodyParam type_background_image Int Loại ảnh nền
     * @bodyParam background_image_url String url ảnh nền
     * @bodyParam apply_fors array required danh sách id của nhóm áp dụng VD: [0,1,2]
     * @bodyParam group_types array required VD: group_types => [{id: 1, name: Sỉ lẻ}]
     * @bodyParam agency_types array required VD: agency_types => [{id: 1, name: Cấp 1}]
     */
    public function create(Request $request)
    {
        $images = [];
        $status = StatusSpinWheelDefineCode::PROGRESSING;
        $now = Helper::getTimeNowCarbon();
        try {
            $this->validate($request, [
                'time_start' => 'required|date',
                'time_end' => 'required|date|after:time_start',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME_START_AND_END[0],
                'msg' => MsgCode::INVALID_TIME_START_AND_END[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->validate($request, [
                'turn_in_day' => 'numeric|min:0',
                'number_limit_people' => 'numeric|min:0',
                'max_amount_coin_per_player' => 'numeric|min:0',
                'max_amount_gift_per_player' => 'numeric|min:0',
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

        if (isset($request->images)) {
            if (!is_array($request->images)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LIST_IMAGE[0],
                    'msg' => MsgCode::INVALID_LIST_IMAGE[1],
                ], 404);
            }

            if (!Helper::checkListImage($request->images)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LIST_IMAGE[0],
                    'msg' => MsgCode::INVALID_LIST_IMAGE[1],
                ], 404);
            }

            $images = $request->images;
        }

        // $spinWheelNameExists = SpinWheel::where('name', $request->name)
        //     ->where('store_id', $request->store->id)
        //     ->first();

        // if ($spinWheelNameExists  != null) {
        //     return response()->json([
        //         'code' => 404,
        //         'success' => false,
        //         'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
        //     ], 404);
        // }

        if (StatusSpinWheelDefineCode::getSpinWheelApplyForCode($request->apply_for) == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TYPE_APPLY_FOR[0],
                'msg' => MsgCode::INVALID_TYPE_APPLY_FOR[1],
            ], 404);
        }

        if ($request->apply_for == StatusSpinWheelDefineCode::GROUP_CUSTOMER_AGENCY) {
            $agencyExists = DB::table('agency_types')->where([
                ['store_id', $request->store->id],
                ['id', $request->agency_type_id]
            ])
                ->first();

            if ($agencyExists == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                    'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
                ], 404);
            }
        }

        if ($request->apply_for == StatusSpinWheelDefineCode::GROUP_CUSTOMER_BY_CONDITION) {
            $groupCustomerExists = DB::table('group_customers')
                ->where('store_id', $request->store->id)
                ->where('id', $request->group_customer_id)
                ->first();

            if ($groupCustomerExists == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_GROUP_CUSTOMER[0],
                    'msg' => MsgCode::NO_GROUP_CUSTOMER[1],
                ], 404);
            }
        }

        if (isset($request->list_gift_spin_wheel)) {
            if (!is_array($request->list_gift_spin_wheel)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LIST_GIFT_SPIN_WHEEL[0],
                    'msg' => MsgCode::INVALID_LIST_GIFT_SPIN_WHEEL[1],
                ], 404);
            }

            if (array_sum(array_column($request->list_gift_spin_wheel, 'percent_received')) > 1000000) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_PERCENT_GIFT_OF_MINI_GAME[0],
                    'msg' => MsgCode::INVALID_PERCENT_GIFT_OF_MINI_GAME[1],
                ], 404);
            }
        }

        // handle status
        $timeEnd = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($request->time_end)));
        if ($timeEnd->lte($now)) {
            $status = StatusSpinWheelDefineCode::CANCELED;
        } else if ($timeEnd->gte($now)) {
            $status = StatusSpinWheelDefineCode::COMPLETED;
        } else {
            $status = StatusSpinWheelDefineCode::PROGRESSING;
        }

        $group_types = null;
        $agency_types = null;
        $apply_fors = null;

        if ($request->apply_fors && is_array($request->apply_fors) === false) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TYPE_APPLY_FOR[0],
                'msg' => MsgCode::INVALID_TYPE_APPLY_FOR[1],
            ], 400);
        }

        if ($request->apply_fors && is_array($request->apply_fors)) {

            $apply_fors = $request->apply_fors;
        }

        if (is_array($request->group_types) && in_array(CustomerUtils::GROUP_CUSTOMER_BY_CONDITION, $request->apply_fors)) {

            $group_types = $request->group_types;
        }

        if (is_array($request->agency_types) && in_array(CustomerUtils::GROUP_CUSTOMER_AGENCY, $request->apply_fors)) {

            $agency_types = $request->agency_types;
        }

        $spinWheelCreate = SpinWheel::create([
            'store_id' => $request->store->id,
            'name' => $request->name,
            'images' => json_encode($images),
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
            'status' => $status,
            'is_shake' => $request->is_shake,
            'group_customer_id' => $request->group_customer_id,
            'agency_type_id' => $request->agency_type_id,
            'apply_for' => $request->apply_for,
            'note' => $request->note,
            'icon' => $request->icon,
            'turn_in_day' => $request->turn_in_day,
            'description' => $request->description,
            'is_limit_people' => filter_var($request->is_limit_people, FILTER_VALIDATE_BOOLEAN),
            'number_limit_people' => $request->number_limit_people,
            'max_amount_coin_per_player' => $request->max_amount_coin_per_player,
            'max_amount_gift_per_player' => $request->max_amount_gift_per_player,
            'type_background_image' => $request->type_background_image,
            'background_image_url' => $request->background_image_url,

            'apply_fors' => $apply_fors,
            'group_types' => $group_types,
            'agency_types' => $agency_types,
        ]);

        if (isset($request->list_gift_spin_wheel)) {
            $arrTemp = [];
            foreach ($request->list_gift_spin_wheel as $giftSpinWheelItem) {
                array_push($arrTemp, [
                    'store_id' => $request->store->id,
                    'spin_wheel_id' => $spinWheelCreate->id,
                    // 'user_id' => $request->user->id,
                    'name' => $giftSpinWheelItem['name'] ?? '',
                    'image_url' => $giftSpinWheelItem['image_url'] ?? '',
                    'type_gift' => $giftSpinWheelItem['type_gift'],
                    'amount_coin' => $giftSpinWheelItem['amount_coin'] ?? 0,
                    'amount_gift' => $giftSpinWheelItem['amount_gift'] ?? 0,
                    'percent_received' => $giftSpinWheelItem['percent_received'] ?? 0,
                    'amount_gift' => $giftSpinWheelItem['amount_gift'] ?? 0,
                    'value_gift' => $giftSpinWheelItem['value_gift'] ?? '',
                    'text' => $giftSpinWheelItem['text'] ?? '',
                    'created_at' => helper::getTimeNowDateTime(),
                    'updated_at' => helper::getTimeNowDateTime()
                ]);
            }

            DB::table('gift_spin_wheels')->insert($arrTemp);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $spinWheelCreate,
        ], 200);
    }


    /**
     * update một vòng quay mini game
     * @bodyParam name String Tên vòng quay
     * @bodyParam images Array danh sách ảnh
     * @bodyParam turn_in_day int lượt chơi trong ngày
     * @bodyParam time_start Datetime thời gian bắt đầu
     * @bodyParam time_end Datetime thời gian kết thúc
     * @bodyParam status Int trạng thái [0: chờ xử lý, 2: hoàn tất]
     * @bodyParam is_shake Boolean 
     * @bodyParam group_customer_id Int mã nhóm khách hàng
     * @bodyParam agency_type_id Int mã cấp đại lý
     * @bodyParam apply_for int chương trình áp dụng cho [0: all, 1: group customer]
     * @bodyParam type_background_image Int Loại ảnh nền
     * @bodyParam background_image_url String url ảnh nền
     * 
     */
    public function update(Request $request)
    {
        $images = [];
        $applyFor = null;
        $status = StatusSpinWheelDefineCode::PROGRESSING;
        $now = Helper::getTimeNowCarbon();

        $spinWheelExists = SpinWheel::where([
            ['store_id', $request->store->id],
            ['id', $request->spin_wheel_id]
        ])
            ->first();
        $applyFor = $spinWheelExists->apply_for;

        if ($spinWheelExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_SPIN_WHEEL_EXISTS[1],
            ], 404);
        }

        try {
            $this->validate($request, [
                'time_start' => 'date',
                'time_end' => 'date|after:time_start',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME_START_AND_END[0],
                'msg' => MsgCode::INVALID_TIME_START_AND_END[1],
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

        if (isset($request->images)) {
            if (!is_array($request->images)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LIST_IMAGE[0],
                    'msg' => MsgCode::INVALID_LIST_IMAGE[1],
                ], 404);
            }

            if (!Helper::checkListImage($request->images)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LIST_IMAGE[0],
                    'msg' => MsgCode::INVALID_LIST_IMAGE[1],
                ], 404);
            }

            $images = json_encode($request->images);
        } else {
            $images = $spinWheelExists->images;
        }

        if (isset($request->apply_for)) {
            if (StatusSpinWheelDefineCode::getSpinWheelApplyForCode($request->apply_for) == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_TYPE_APPLY_FOR[0],
                    'msg' => MsgCode::INVALID_TYPE_APPLY_FOR[1],
                ], 404);
            }

            if ($request->apply_for == StatusSpinWheelDefineCode::GROUP_CUSTOMER_AGENCY) {
                $agencyExists = DB::table('agency_types')->where([
                    ['store_id', $request->store->id],
                    ['id', $request->agency_type_id]
                ])
                    ->first();

                if ($agencyExists == null) {
                    return response()->json([
                        'code' => 404,
                        'success' => false,
                        'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                        'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
                    ], 404);
                }
            }


            if ($request->apply_for == StatusSpinWheelDefineCode::GROUP_CUSTOMER_BY_CONDITION) {
                $groupCustomerExists = DB::table('group_customers')
                    ->where('store_id', $request->store->id)
                    ->where('id', $request->group_customer_id)
                    ->first();

                if ($groupCustomerExists == null) {
                    return response()->json([
                        'code' => 404,
                        'success' => false,
                        'msg_code' => MsgCode::NO_GROUP_CUSTOMER[0],
                        'msg' => MsgCode::NO_GROUP_CUSTOMER[1],
                    ], 404);
                }
            }

            $applyFor = $request->apply_for;
        } else {
            $applyFor = $spinWheelExists->apply_for;
        }

        // handle status
        if ($request->time_end != null) {
            $timeEnd = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($request->time_end)));
        } else {
            $timeEnd = $spinWheelExists->time_end;
        }
        if ($timeEnd->lte($now)) {
            $status = StatusSpinWheelDefineCode::CANCELED;
        } else if ($timeEnd->gte($now)) {
            $status = StatusSpinWheelDefineCode::COMPLETED;
        } else {
            $status = StatusSpinWheelDefineCode::PROGRESSING;
        }


        if (isset($request->list_gift_spin_wheel)) {
            if (!is_array($request->list_gift_spin_wheel)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LIST_GIFT_SPIN_WHEEL[0],
                    'msg' => MsgCode::INVALID_LIST_GIFT_SPIN_WHEEL[1],
                ], 404);
            }
            // if (array_sum(array_column($request->list_gift_spin_wheel, 'percent_received')) > 100) {
            //     return response()->json([
            //         'code' => 404,
            //         'success' => false,
            //         'msg_code' => MsgCode::INVALID_PERCENT_GIFT_OF_MINI_GAME[0],
            //         'msg' => MsgCode::INVALID_PERCENT_GIFT_OF_MINI_GAME[1],
            //     ], 404);
            // }
        }

        $group_types = $spinWheelExists->group_types;
        $agency_types = $spinWheelExists->agency_types;
        $apply_fors = $spinWheelExists->apply_fors;

        if ($request->apply_fors && is_array($request->apply_fors) === false) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TYPE_APPLY_FOR[0],
                'msg' => MsgCode::INVALID_TYPE_APPLY_FOR[1],
            ], 400);
        }

        if ($request->apply_fors && is_array($request->apply_fors)) {

            $apply_fors = $request->apply_fors;
        }

        if (is_array($request->group_types) && in_array(CustomerUtils::GROUP_CUSTOMER_BY_CONDITION, $request->apply_fors)) {

            $group_types = $request->group_types;
        } else {

            $group_types = null;
        }

        if (is_array($request->agency_types) && in_array(CustomerUtils::GROUP_CUSTOMER_AGENCY, $request->apply_fors)) {

            $agency_types = $request->agency_types;
        } else {

            $agency_types = null;
        }

        $spinWheelExists->update([
            'name' => $request->name ?? $spinWheelExists->name,
            'images' => $images,
            'icon' => $request->icon ?? $spinWheelExists->icon,
            'description' => $request->description ?? $spinWheelExists->description,
            'turn_in_day' => $request->turn_in_day ?? $spinWheelExists->turn_in_day,
            'time_start' => $request->time_start ?? $spinWheelExists->time_start,
            'time_end' => $request->time_end ?? $spinWheelExists->time_end,
            'status' => $status,
            'is_shake' => $request->is_shake ?? $spinWheelExists->is_shake,
            'group_customer_id' => $request->group_customer_id ?? $spinWheelExists->group_customer_id,
            'agency_type_id' => $request->agency_type_id ?? $spinWheelExists->agency_type_id,
            'apply_for' => $applyFor,
            'note' => $request->note ?? $spinWheelExists->note,
            'is_limit_people' => $request->is_limit_people ?? $spinWheelExists->is_limit_people,
            'number_limit_people' => $request->number_limit_people ?? $spinWheelExists->number_limit_people,
            'max_amount_coin_per_player' =>  $request->max_amount_coin_per_player ?? $spinWheelExists->max_amount_coin_per_player,
            'max_amount_gift_per_player' =>  $request->max_amount_gift_per_player ?? $spinWheelExists->max_amount_gift_per_player,
            'type_background_image' => $request->type_background_image ?? $spinWheelExists->type_background_image,
            'background_image_url' => $request->background_image_url ?? $spinWheelExists->background_image_url,

            'apply_fors' => $apply_fors,
            'group_types' => $group_types,
            'agency_types' => $agency_types,
        ]);

        if (isset($request->list_gift_spin_wheel)) {

            $arrTemp = [];
            foreach ($request->list_gift_spin_wheel as $giftSpinWheelItem) {
                array_push($arrTemp, [
                    'store_id' => $request->store->id,
                    'spin_wheel_id' => $spinWheelExists->id,
                    // 'user_id' => $request->user->id,
                    'name' => $giftSpinWheelItem['name'] ?? '',
                    'image_url' => $giftSpinWheelItem['image_url'] ?? '',
                    'type_gift' => $giftSpinWheelItem['type_gift'],
                    'amount_coin' => $giftSpinWheelItem['amount_coin'] ?? 0,
                    'percent_received' => $giftSpinWheelItem['percent_received'] ?? 0,
                    'amount_gift' => $giftSpinWheelItem['amount_gift'] ?? 0,
                    'amount_gift' => $giftSpinWheelItem['amount_gift'] ?? 0,
                    'value_gift' => $giftSpinWheelItem['value_gift'] ?? '',
                    'text' => $giftSpinWheelItem['text'] ?? '',
                    'created_at' => helper::getTimeNowDateTime(),
                    'updated_at' => helper::getTimeNowDateTime()
                ]);
            }
            if (count($arrTemp) > 0) {
                DB::table('gift_spin_wheels')->where([
                    ['store_id', $request->store->id],
                    ['spin_wheel_id', $spinWheelExists->id]
                ])->delete();
                DB::table('gift_spin_wheels')->insert($arrTemp);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $spinWheelExists,
        ], 200);
    }
}
