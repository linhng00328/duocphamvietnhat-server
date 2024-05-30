<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CustomerUtils;
use App\Helper\Helper;
use App\Helper\StatusGuessNumberDefineCode;
use App\Http\Controllers\Controller;
use App\Models\GuessNumber;
use App\Models\GuessNumberResult;
use App\Models\HistoryGiftGuessNumber;
use App\Models\MsgCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GuessNumberController extends Controller
{
    /**
     * 
     * Danh sách game đoán số
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam time_start datetime thời gian bắt đầu
     * @urlParam time_end datetime thời gian kết thúc
     * @urlParam status int trạng thái mini game đoán số
     * @urlParam search string tìm kiếm
     * 
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $now = Helper::getTimeNowCarbon();
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $timeStart = Helper::createAndValidateFormatDate($request->time_start, 'y-m-d H:i:s');
        $timeEnd = Helper::createAndValidateFormatDate($request->time_end, 'y-m-d H:i:s');

        $guessNumberExists = GuessNumber::where([
            ['store_id', $request->store->id],
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
            ->when(!empty($sortBy) && Schema::hasColumn('guess_numbers', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->paginate($limit);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $guessNumberExists,
        ], 200);
    }

    /**
     * 
     * Danh sách kết quả game đoán số
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam guess_number_id required int mã đoán số.
     * @queryParam is_correct boolean lọc kết quả đúng
     * 
     */
    public function getHistoryResult(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $firstResultGuessNumber = null;

        $checkGuessNumberExists = GuessNumber::where(
            [
                ['id', $request->guess_number_id],
                ['store_id', $request->store->id]
                // ['user_id', $request->user->id]
            ]
        )
            ->first();

        if (empty($checkGuessNumberExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_GUESS_NUMBER_EXISTS[0],
                'msg' => MsgCode::NO_GUESS_NUMBER_EXISTS[1],
            ], 404);
        }

        if ($checkGuessNumberExists->is_guess_number == true) {
            $guessNumberResultExists = GuessNumberResult::where(
                [
                    ['id', $request->guess_number_id],
                    ['store_id', $request->store->id]
                ]
            )
                ->first();
        }

        $guessNumberExists = HistoryGiftGuessNumber::where([
            ['store_id', $request->store->id],
            ['guess_number_id', $request->guess_number_id],
        ])
            ->when(filter_var($request->is_correct, FILTER_VALIDATE_BOOLEAN) == true, function ($query) use ($request, $checkGuessNumberExists, $guessNumberResultExists) {
                if ($checkGuessNumberExists->is_guess_number == true && $guessNumberResultExists != null) {
                    $query->where('value_predict', $guessNumberResultExists->text_result);
                } else {
                }
            })
            ->when(!empty($sortBy) && Schema::hasColumn('guess_numbers', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->paginate($limit);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $guessNumberExists,
        ], 200);
    }


    /**
     * xóa một đoán số mini game
     * @urlParam store_code required Store code cần xóa.
     * @urlParam guess_number_id required ID mã đoán số cần cần xóa
     */
    public function delete(Request $request)
    {
        $checkGuessNumberExists = GuessNumber::where(
            [
                ['id', $request->guess_number_id],
                ['store_id', $request->store->id]
                // ['user_id', $request->user->id]
            ]
        )
            ->first();

        if (empty($checkGuessNumberExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_GUESS_NUMBER_EXISTS[0],
                'msg' => MsgCode::NO_GUESS_NUMBER_EXISTS[1],
            ], 404);
        }

        $idDeleted = $checkGuessNumberExists->id;
        $checkGuessNumberExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }

    /**
     * xem 1 một đoán số mini game
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  guess_number_id required ID mã đoán số cần cần xóa
     */
    public function getOne(Request $request)
    {
        // $idGuessNumberResultHasSetResult = DB::table('guess_number_results')
        //     ->selectRaw('guess_number_id,count(*) as count')
        //     ->groupBy('guess_number_id')
        //     ->havingRaw('count(*) >= 1')
        //     ->distinct()
        //     ->pluck('guess_number_id');
        // $idGuessNumberHasSetResult = DB::table('guess_numbers')
        //     ->where('')
        // dd($idSpinWheelMoreThan2Gift);
        $checkGuessNumberExists = GuessNumber::where(
            [
                ['id', $request->guess_number_id],
                ['store_id', $request->store->id]
                // ['user_id', $request->user->id]
            ]
        )
            ->first();

        if (empty($checkGuessNumberExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_GUESS_NUMBER_EXISTS[0],
                'msg' => MsgCode::NO_GUESS_NUMBER_EXISTS[1],
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $checkGuessNumberExists,
        ], 200);
    }

    /**
     * Tạo một đoán số mini game
     * @urlParam  store_code required Store code cần update
     * @bodyParam name String Tên đoán số
     * @bodyParam images Array danh sách ảnh
     * @bodyParam turn_in_day int lượt chơi trong ngày
     * @bodyParam time_start Datetime thời gian bắt đầu
     * @bodyParam time_end Datetime thời gian kết thúc
     * @bodyParam status Int trạng thái [0: chờ xử lý, 2: hoàn tất]
     * @bodyParam group_customer_id Int mã nhóm khách hàng
     * @bodyParam apply_for int chương trình áp dụng cho [0: all, 1: group customer]
     * @bodyParam is_limit_people Boolean Giới hạn người chơi
     * @bodyParam number_limit_people Int giới hạn số lượng người tham gia
     * @bodyParam name_result String Tên kết quả
     * @bodyParam type_result String Loại kết quả
     * @bodyParam number_result Int Kết quả bằng số
     * @bodyParam text_result String Kết quả bằng chữ
     * @bodyParam image_result String ảnh kết quả
     * @bodyParam description_result String mô tả kết quả 
     * @bodyParam name_gift String Tên quà
     * @bodyParam value_gift String Giá trị quà
     * @bodyParam amount_gift int số lượng quà
     * @bodyParam image_gift String ảnh quà
     * @bodyParam description_gift String mô tả quà
     * @bodyParam is_guess_number Boolean Là game đoán số
     * @bodyParam is_show_game Boolean Hiện mini game
     * @bodyParam is_limit_people Boolean Có giới hạn người chơi
     * @bodyParam is_show_all_prizer Boolean Hiện tất cả người trúng
     * @bodyParam list_result Array Danh sách kết quả
     * @bodyParam type_background_image Int Loại ảnh nền
     * @bodyParam background_image_url String url ảnh nền
     * @bodyParam range_number Int url ảnh nền
     * @bodyParam text_result String đáp án game đoán số
     * @bodyParam value_gift String Quà game đoán số
     * @bodyParam apply_fors array required danh sách id của nhóm áp dụng VD: [0,1,2]
     * @bodyParam group_types array required VD: group_types => [{id: 1, name: Sỉ lẻ}]
     * @bodyParam agency_types array required VD: agency_types => [{id: 1, name: Cấp 1}]
     */
    public function create(Request $request)
    {
        $tempArr = [];
        $images = [];
        $status = StatusGuessNumberDefineCode::PROGRESSING;
        $nowTime = Helper::getTimeNowCarbon();
        try {
            $this->validate($request, [
                'time_start' => 'required|date',
                'time_end' => 'required|date|after:time_start',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->validate($request, [
                'turn_in_day' => 'required|numeric|min:0',
                // 'number_limit_people' => 'numeric|min:0',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DATA[0],
                'msg' => MsgCode::INVALID_DATA[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->validate($request, [
                'is_guess_number' => 'required|boolean',
                // 'is_show_all_prizer' => 'required|boolean',
                'is_show_game' => 'required|boolean'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DATA_BOOLEAN[0],
                'msg' => MsgCode::INVALID_DATA_BOOLEAN[1],
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

            $images = $request->images;
        }

        // $guessNumberNameExists = GuessNumber::where('name', $request->name)
        //     ->where('store_id', $request->store->id)
        //     ->first();

        // if ($guessNumberNameExists  != null) {
        //     return response()->json([
        //         'code' => 404,
        //         'success' => false,
        //         'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
        //     ], 404);
        // }

        if (!isset($request->list_result) || !is_array($request->list_result)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_GUESS_NUMBER_RESULT[0],
                'msg' => MsgCode::INVALID_LIST_GUESS_NUMBER_RESULT[1],
            ], 404);
        }

        if ($request->apply_for == StatusGuessNumberDefineCode::GROUP_CUSTOMER_AGENCY) {
            $agencyExists = DB::table('agency_types')->where([
                ['store_id', $request->store->id],
                ['id', $request->agency_type_id]
            ])
                ->first();

            if ($agencyExists == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_AGENCY_EXISTS[0],
                    'msg' => MsgCode::NO_AGENCY_EXISTS[1],
                ], 404);
            }
        }

        if ($request->apply_for == StatusGuessNumberDefineCode::GROUP_CUSTOMER_BY_CONDITION) {
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

        // handle status
        $timeEnd = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($request->time_end)));
        if ($timeEnd->lte($nowTime)) {
            $status = StatusGuessNumberDefineCode::CANCELED;
        } else if ($timeEnd->gte($nowTime)) {
            $status = StatusGuessNumberDefineCode::COMPLETED;
        } else {
            $status = StatusGuessNumberDefineCode::PROGRESSING;
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

        $guessNumberCreate = GuessNumber::create([
            'store_id' => $request->store->id,
            'name' => $request->name,
            'images' => json_encode($images),
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
            'status' => $status,
            'group_customer_id' => $request->group_customer_id,
            'agency_type_id' => $request->agency_type_id,
            'apply_for' => $request->apply_for,
            'note' => $request->note,
            'icon' => $request->icon,
            'description' => $request->description,
            'turn_in_day' => $request->turn_in_day,
            'is_guess_number' => filter_var($request->is_guess_number ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_show_game' => filter_var($request->is_show_game ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_limit_people' => filter_var($request->is_limit_people ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_show_all_prizer' => filter_var($request->is_show_all_prizer ?? false, FILTER_VALIDATE_BOOLEAN),
            'number_limit_people' => $request->number_limit_people,
            'type_background_image' => $request->type_background_image,
            'background_image_url' => $request->background_image_url,
            'range_number' => $request->range_number,
            'text_result' => $request->text_result,
            'value_gift' => $request->value_gift,

            'apply_fors' => $apply_fors,
            'group_types' => $group_types,
            'agency_types' => $agency_types,
        ]);


        foreach ($request->list_result as $guessNumberResultItem) {
            array_push($tempArr, [
                'store_id' => $request->store->id,
                'guess_number_id' => $guessNumberCreate->id,
                'text_result' => $guessNumberResultItem['text_result'] ?? null,
                'image_url_result' => $guessNumberResultItem['image_url_result'] ?? null,
                'description_result' => $guessNumberResultItem['description_result'] ?? null,
                'is_correct' => filter_var($guessNumberResultItem['is_correct'], FILTER_VALIDATE_BOOLEAN) ?? false,
                'value_gift' => $guessNumberResultItem['value_gift'] ?? null,
                'image_url_gift' => $guessNumberResultItem['image_url_gift'] ?? null,
                'description_gift' => $guessNumberResultItem['description_gift'] ?? null,
                'created_at' => $nowTime,
                'updated_at' => $nowTime
            ]);
        }

        if (count($tempArr) > 0) {
            DB::table('guess_number_results')->insert($tempArr);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $guessNumberCreate,
        ], 200);
    }


    /**
     * update một đoán số mini game
     * @urlParam guess_number_id Int Mã game đoán số
     * @bodyParam name String Tên đoán số
     * @bodyParam images Array danh sách ảnh
     * @bodyParam turn_in_day int lượt chơi trong ngày
     * @bodyParam time_start Datetime thời gian bắt đầu
     * @bodyParam time_end Datetime thời gian kết thúc
     * @bodyParam status Int trạng thái [0: chờ xử lý, 2: hoàn tất]
     * @bodyParam group_customer_id Int mã nhóm khách hàng
     * @bodyParam apply_for int chương trình áp dụng cho [0: all, 1: group customer]
     * @bodyParam is_limit_people Boolean Giới hạn người chơi
     * @bodyParam number_limit_people Int giới hạn số lượng người tham gia
     * @bodyParam name_result String Tên kết quả
     * @bodyParam type_result String Loại kết quả
     * @bodyParam number_result Int Kết quả bằng số
     * @bodyParam text_result String Kết quả bằng chữ
     * @bodyParam image_result String ảnh kết quả
     * @bodyParam description_result String mô tả kết quả 
     * @bodyParam name_gift String Tên quà
     * @bodyParam value_gift String Giá trị quà
     * @bodyParam amount_gift int số lượng quà
     * @bodyParam image_gift String ảnh quà
     * @bodyParam description_gift String mô tả quà
     * @bodyParam is_guess_number Boolean Là game đoán số
     * @bodyParam is_show_game Boolean Hiện mini game
     * @bodyParam is_show_all_prizer Boolean Hiện tất cả người trúng
     * @bodyParam list_result Array Danh sách kết quả
     * @bodyParam type_background_image Int Loại ảnh nền
     * @bodyParam background_image_url String url ảnh nền
     * 
     */
    public function update(Request $request)
    {
        $tempArr = [];
        $images = [];
        $now = Helper::getTimeNowCarbon();

        $guessNumberExists = GuessNumber::where([
            ['store_id', $request->store->id],
            ['id', $request->guess_number_id]
        ])
            ->first();

        if ($guessNumberExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_GUESS_NUMBER_EXISTS[0],
                'msg' => MsgCode::NO_GUESS_NUMBER_EXISTS[1],
            ], 404);
        }

        try {
            $this->validate($request, [
                'turn_in_day' => 'numeric|min:0',
                'number_limit_people' => 'numeric|min:0',
                // 'range_number' => 'numeric|min:0|max:10',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DATA[0],
                'msg' => MsgCode::INVALID_DATA[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->validate($request, [
                'time_start' => 'required|date',
                'time_end' => 'required|date|after:time_start',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->validate($request, [
                'is_guess_number' => 'boolean',
                // 'is_show_all_prizer' => 'boolean',
                'is_show_game' => 'boolean'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DATA_BOOLEAN[0],
                'msg' => MsgCode::INVALID_DATA_BOOLEAN[1],
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

            $images = $request->images;
        }

        if (!isset($request->list_result) && !is_array($request->list_result)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_GUESS_NUMBER_RESULT[0],
                'msg' => MsgCode::INVALID_LIST_GUESS_NUMBER_RESULT[1],
            ], 404);
        }

        if ($request->apply_for == StatusGuessNumberDefineCode::GROUP_CUSTOMER_AGENCY) {
            $agencyExists = DB::table('agency_types')->where([
                ['store_id', $request->store->id],
                ['id', $request->agency_type_id]
            ])
                ->first();

            if ($agencyExists == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_AGENCY_EXISTS[0],
                    'msg' => MsgCode::NO_AGENCY_EXISTS[1],
                ], 404);
            }
        }

        if ($request->apply_for == StatusGuessNumberDefineCode::GROUP_CUSTOMER_BY_CONDITION) {
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

        // handle status
        if ($request->time_end != null) {
            $timeEnd = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($request->time_end)));
        } else {
            $timeEnd = $guessNumberExists->time_end;
        }
        if ($request->status != null) {
            if (StatusGuessNumberDefineCode::getStatusGuessNumberCode($request->status) == false) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_STATUS_MINI_GAME[0],
                    'msg' => MsgCode::INVALID_STATUS_MINI_GAME[1],
                ], 404);
            }

            $status = $request->status;
        } else {
            if ($timeEnd->lte($now)) {
                $status = $guessNumberExists->status;
            } else if ($timeEnd->gte($now)) {
                $status = StatusGuessNumberDefineCode::COMPLETED;
            } else {
                $status = StatusGuessNumberDefineCode::PROGRESSING;
            }
        }

        $group_types = $guessNumberExists->group_types;
        $agency_types = $guessNumberExists->agency_types;
        $apply_fors = $guessNumberExists->apply_fors;

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

        $guessNumberExists->update([
            'name' => $request->name ?? $guessNumberExists->name,
            'images' => json_encode($images),
            'time_start' => $request->time_start ?? $guessNumberExists->time_start,
            'time_end' => $request->time_end ?? $guessNumberExists->time_end,
            'icon' => $request->icon ?? $guessNumberExists->icon,
            'description' => $request->description ?? $guessNumberExists->description,
            'turn_in_day' => $request->turn_in_day ?? $guessNumberExists->turn_in_day,
            'status' => $status,
            'group_customer_id' => $request->group_customer_id ?? $guessNumberExists->group_customer_id,
            'apply_for' => $request->apply_for ?? $guessNumberExists->apply_for,
            'agency_type_id' => $request->agency_type_id ?? $guessNumberExists->agency_type_id,
            'note' => $request->note ?? $guessNumberExists->note,
            'is_guess_number' => filter_var($request->is_guess_number, FILTER_VALIDATE_BOOLEAN) ?? $guessNumberExists->is_guess_number,
            'is_show_game' => filter_var($request->is_show_game, FILTER_VALIDATE_BOOLEAN) ?? $guessNumberExists->is_show_game,
            'is_limit_people' => filter_var($request->is_limit_people, FILTER_VALIDATE_BOOLEAN) ?? $guessNumberExists->is_limit_people,
            'is_show_all_prizer' => filter_var($request->is_show_all_prizer, FILTER_VALIDATE_BOOLEAN) ?? $guessNumberExists->is_show_all_prizer,
            'number_limit_people' => $request->number_limit_people ?? $guessNumberExists->number_limit_people,
            'type_background_image' => $request->type_background_image ?? $guessNumberExists->type_background_image,
            'background_image_url' => $request->background_image_url ?? $guessNumberExists->background_image_url,
            'range_number' => $request->range_number ?? $guessNumberExists->range_number,
            'text_result' => $request->text_result ?? $guessNumberExists->text_result,
            'value_gift' => $request->value_gift ?? $guessNumberExists->value_gift,

            'apply_fors' => $apply_fors,
            'group_types' => $group_types,
            'agency_types' => $agency_types,
        ]);


        if (isset($request->list_result)) {
            foreach ($request->list_result as $guessNumberResultItem) {
                array_push($tempArr, [
                    'store_id' => $request->store->id,
                    'guess_number_id' => $guessNumberExists->id,
                    'text_result' => $guessNumberResultItem['text_result'] ?? null,
                    'image_url_result' => $guessNumberResultItem['image_url_result'] ?? null,
                    'description_result' => $guessNumberResultItem['description_result'] ?? null,
                    'value_gift' => $guessNumberResultItem['value_gift'] ?? null,
                    'is_correct' => filter_var($guessNumberResultItem['is_correct'], FILTER_VALIDATE_BOOLEAN) ?? false,
                    'image_url_gift' => $guessNumberResultItem['image_url_gift'] ?? null,
                    'description_gift' => $guessNumberResultItem['description_gift'] ?? null,
                    'created_at' => Helper::getTimeNowCarbon(),
                    'updated_at' => Helper::getTimeNowCarbon()
                ]);
            }

            if (count($tempArr) > 0) {
                DB::table('guess_number_results')->where([
                    ['store_id', $request->store->id],
                    ['guess_number_id', $guessNumberExists->id],
                ])->delete();
                DB::table('guess_number_results')->insert($tempArr);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $guessNumberExists,
        ], 200);
    }
}
