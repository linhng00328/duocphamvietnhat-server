<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\GroupCustomerUtils;
use App\Helper\Helper;
use App\Helper\StatusGuessNumberDefineCode;
use App\Helper\StatusHistoryGuessNumberDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GuessNumber;
use App\Models\HistoryGiftGuessNumber;
use App\Models\MsgCode;
use App\Models\player_guess_number;
use App\Models\PlayerGuessNumber;
use App\Models\SessionCustomer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GuessNumberController extends Controller
{
    /**
     * 
     * Tham gia mini game đoán số
     * 
     * @urlParam store_code required Store code cần xóa.
     * @bodyParam guess_number_id required spin_wheel id cần lấy.
     * 
     */
    public function joinGuessNumber(Request $request)
    {
        $guessNumberExist = DB::table('guess_numbers')
            ->where([
                ['id', $request->guess_number_id],
                ['store_id', $request->store->id]
            ])
            ->first();

        if ($guessNumberExist == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_GUESS_NUMBER_EXISTS[0],
                'msg' => MsgCode::NO_GUESS_NUMBER_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $playerGuessNumberExists = DB::table('player_guess_numbers')
            ->where([
                ['store_id', $request->store->id],
                ['guess_number_id', $request->guess_number_id],
                ['customer_id', $request->customer->id]
            ])
            ->first();

        if ($playerGuessNumberExists != null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::YOU_HAVE_JOINED_THIS_GAME[0],
                'msg' => MsgCode::YOU_HAVE_JOINED_THIS_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
            $request,
            $guessNumberExist->apply_for,
            $guessNumberExist->agency_type_id,
            $guessNumberExist->group_customer_id,
            $request->customer,
            $request->store->id,
            $guessNumberExist->apply_fors,
            $guessNumberExist->agency_types,
            $guessNumberExist->group_types
        );

        if (!$ok_customer) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::YOU_UNABLE_JOIN_THIS_GAME[0],
                'msg' => MsgCode::YOU_UNABLE_JOIN_THIS_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $playerGuessNumberCreate = PlayerGuessNumber::create([
            'store_id' => $request->store->id,
            'guess_number_id' => $request->guess_number_id,
            'customer_id' => $request->customer->id,
            'total_turn_play' => $guessNumberExist->turn_in_day,
            'total_win' => 0,
            'total_missed' => 0
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $playerGuessNumberCreate,
        ], 200);
    }

    /**
     * 
     * Danh sách mini game đoán số
     * 
     * @urlParam store_code required Store code cần xóa.
     * 
     */
    public function getGuessNumbers(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        $listMiniGameGuessNumber = GuessNumber::where([
            ['guess_numbers.store_id', $request->store->id],
            ['guess_numbers.time_end', '>=', Helper::getTimeNowCarbon()->format('Y-m-d H:i:m')]
        ])
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
            'data' => $listMiniGameGuessNumber
        ], 200);
    }

    /**
     * 
     * Thông tin người chơi
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam guess_number_id required spin_wheel id cần lấy.
     * 
     */
    public function getInfoPlayer(Request $request)
    {
        $playerGuessNumberExists = PlayerGuessNumber::where([
            ['store_id', $request->store->id],
            ['guess_number_id', $request->guess_number_id],
            ['customer_id', $request->customer->id]
        ])
            ->first();

        if ($playerGuessNumberExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::YOU_HAVE_NOT_JOIN_THIS_GAME[0],
                'msg' => MsgCode::YOU_HAVE_NOT_JOIN_THIS_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $playerGuessNumberExists
        ], 200);
    }

    /**
     * 
     * Lấy 1 mini game đoán số
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam guess_number_id required spin_wheel id cần lấy.
     * 
     */
    public function getAGuessNumber(Request $request)
    {
        $customerExists = null;
        $nowTime = Helper::getTimeNowCarbon();
        $miniGameGuessNumberExist = GuessNumber::where([
            ['guess_numbers.id', $request->guess_number_id],
            ['guess_numbers.store_id', $request->store->id]
            // ['guess_numbers.time_end', '>=', Helper::getTimeNowCarbon()->format('Y-m-d H:59:59')]
        ])
            ->first();

        if ($miniGameGuessNumberExist == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_GUESS_NUMBER_EXISTS[0],
                'msg' => MsgCode::NO_GUESS_NUMBER_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $checkTokenIsValid = SessionCustomer::where('token', request()->header('customer-token'))->first();

        if ($checkTokenIsValid != null && $checkTokenIsValid->customer_id != null) {
            $customerExists = Customer::where('id', $checkTokenIsValid->customer_id)
                ->where('official', true)
                ->where('store_id', $request->store->id)->first();
        }

        if ($customerExists != null) {

            $playerGuessNumberExists = PlayerGuessNumber::where([
                ['store_id', $request->store->id],
                ['guess_number_id', $request->guess_number_id],
                ['customer_id', $customerExists->id]
            ])
                ->first();

            // handle update turn play game
            if ($playerGuessNumberExists != null && $playerGuessNumberExists->check_get_turn != $nowTime->format('d')) {
                $playerGuessNumberExists->update([
                    'total_turn_play' => $miniGameGuessNumberExist->turn_in_day,
                    'check_get_turn' => $nowTime->format('d')
                ]);
            }

            $miniGameGuessNumberExist->is_cus_has_joined = $playerGuessNumberExists != null ? true : false;
            $miniGameGuessNumberExist->info_player = $playerGuessNumberExists;
        } else {
            $miniGameGuessNumberExist->is_cus_has_joined = null;
            $miniGameGuessNumberExist->info_player = null;
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $miniGameGuessNumberExist
        ], 200);
    }

    /**
     * 
     * Lịch sử phần quà mini game vòng quay
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam guess_number_id  required spin_wheel id cần lấy.
     * @urlParam sortBy string sắp xếp theo cột
     * @urlParam search string tìm kiếm
     * @urlParam limit int giới hạn default 20
     * 
     */
    public function historyGift(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        $historyGiftPlays = HistoryGiftGuessNumber::join('player_guess_numbers', 'history_gift_guess_numbers.player_guess_number_id', '=', 'player_guess_numbers.id')
            ->where([
                ['player_guess_numbers.store_id', $request->store->id],
                ['player_guess_numbers.guess_number_id', $request->guess_number_id],
                ['player_guess_numbers.customer_id', $request->customer->id],
                ['history_gift_guess_numbers.store_id', $request->store->id]
            ])
            ->select('history_gift_guess_numbers.*')
            ->when($request->type_gift != null, function ($query) use ($request) {
                $query->where($request->type_gift);
            })
            ->when(!empty($sortBy) && Schema::hasColumn('history_gift_guess_numbers', $sortBy), function ($query) use ($sortBy, $descending) {
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
            'data' => $historyGiftPlays,
        ], 200);
    }

    /**
     * 
     * Dự đoán số
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam guess_number_id required spin_wheel id cần lấy.
     * 
     */
    public function predictGuessNumber(Request $request)
    {
        $giftWinning = null;

        $guessNumberExists = DB::table('guess_numbers')
            ->where([
                ['store_id', $request->store->id],
                ['id', $request->guess_number_id]
            ])
            ->first();


        if ($guessNumberExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_GUESS_NUMBER_EXISTS[0],
                'msg' => MsgCode::NO_GUESS_NUMBER_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($guessNumberExists->is_guess_number == false) {
            $guessNumberResultExists = DB::table('guess_number_results')
                ->where([
                    ['store_id', $request->store->id],
                    ['id', $request->guess_number_result_id]
                ])
                ->first();


            if ($guessNumberResultExists == null) {
                return response()->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_GUESS_NUMBER_RESULT_EXISTS[0],
                    'msg' => MsgCode::NO_GUESS_NUMBER_RESULT_EXISTS[1],
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($guessNumberExists->is_guess_number == true) {
            if ($request->value_predict == null) {
                return response()->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::VALUE_PREDICT_IS_REQUIRED[0],
                    'msg' => MsgCode::VALUE_PREDICT_IS_REQUIRED[1],
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $playerGuessNumberExists = PlayerGuessNumber::where([
            ['store_id', $request->store->id],
            ['guess_number_id', $request->guess_number_id],
            ['customer_id', $request->customer->id]
        ])
            ->first();

        if ($playerGuessNumberExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::YOU_HAVE_NOT_JOIN_THIS_GAME[0],
                'msg' => MsgCode::YOU_HAVE_NOT_JOIN_THIS_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($playerGuessNumberExists->total_turn_play < 1) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::YOU_HAVE_NO_TURN_PLAY_GAME[0],
                'msg' => MsgCode::YOU_HAVE_NO_TURN_PLAY_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($playerGuessNumberExists->total_turn_play > 0) {

            if ($guessNumberExists->is_guess_number == true) {
                $historyGiftGuessNumber = HistoryGiftGuessNumber::create([
                    'store_id' => $request->store->id,
                    'guess_number_id' => $request->guess_number_id,
                    'player_guess_number_id' => $playerGuessNumberExists->id,
                    'guess_number_result_id' => null,
                    'value_predict' => $request->value_predict
                ]);
            } else {
                $historyGiftGuessNumber = HistoryGiftGuessNumber::create([
                    'store_id' => $request->store->id,
                    'guess_number_id' => $request->guess_number_id,
                    'player_guess_number_id' => $playerGuessNumberExists->id,
                    'guess_number_result_id' => $request->guess_number_result_id,
                    'value_predict' => $request->value_predict
                ]);
            }

            $playerGuessNumberExists->update([
                'total_turn_play' => $playerGuessNumberExists->total_turn_play - 1
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $historyGiftGuessNumber
        ], 200);
    }
}
