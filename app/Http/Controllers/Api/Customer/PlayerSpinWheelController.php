<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\GroupCustomerUtils;
use App\Helper\Helper;
use App\Helper\PointCustomerUtils;
use App\Helper\StatusHistorySpinWheelDefineCode;
use App\Helper\StatusSpinWheelDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GiftSpinWheel;
use App\Models\HistoryGiftSpinWheel;
use App\Models\HistoryTurnReceived;
use App\Models\MsgCode;
use App\Models\PlayerSpinWheel;
use App\Models\SessionCustomer;
use App\Models\SpinWheel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlayerSpinWheelController extends Controller
{
    /**
     * 
     * Tham gia mini game vòng quay
     * 
     * @urlParam store_code required Store code cần xóa.
     * @bodyParam spin_wheel_id required spin_wheel id cần lấy.
     * 
     */
    public function create(Request $request)
    {
        $spinWheelExist = DB::table('spin_wheels')
            ->where([
                ['id', $request->spin_wheel_id],
                ['store_id', $request->store->id]
            ])
            ->first();

        if ($spinWheelExist == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_SPIN_WHEEL_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $playerSpinWheelExists = DB::table('player_spin_wheels')
            ->where([
                ['store_id', $request->store->id],
                ['spin_wheel_id', $request->spin_wheel_id],
                ['customer_id', $request->customer->id]
            ])
            ->first();

        if ($playerSpinWheelExists != null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::YOU_HAVE_JOINED_THIS_GAME[0],
                'msg' => MsgCode::YOU_HAVE_JOINED_THIS_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
            $request,
            $spinWheelExist->apply_for,
            $spinWheelExist->agency_id,
            $spinWheelExist->group_customer_id,
            $request->customer,
            $request->store->id,
            $spinWheelExist->apply_fors,
            $spinWheelExist->agency_types,
            $spinWheelExist->group_types
        );

        if (!$ok_customer) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::YOU_UNABLE_JOIN_THIS_GAME[0],
                'msg' => MsgCode::YOU_UNABLE_JOIN_THIS_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $playerSpinWheelCreate = PlayerSpinWheel::create([
            'store_id' => $request->store->id,
            'spin_wheel_id' => $request->spin_wheel_id,
            'customer_id' => $request->customer->id,
            'total_turn_play' => $spinWheelExist->turn_in_day,
            'total_coin_received' => 0,
            'total_gift_received' => 0,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $playerSpinWheelCreate,
        ], 200);
    }

    /**
     * 
     * Lịch sử lượt chơi mini game vòng quay
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam spin_wheel_id required spin_wheel id cần lấy.
     * @urlParam sortBy string sắp xếp theo cột
     * @urlParam search string tìm kiếm
     * @urlParam limit int giới hạn default 20
     * 
     */
    public function historyTurnPlay(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';


        $historyTurnPlays = HistoryTurnReceived::join('player_spin_wheels', 'history_turn_receiveds.player_spin_wheel_id', '=', 'player_spin_wheels.id')
            ->where([
                ['player_spin_wheels.store_id', $request->store->id],
                ['player_spin_wheels.spin_wheel_id', $request->spin_wheel_id],
                ['player_spin_wheels.customer_id', $request->customer->id],
                ['history_turn_receiveds.store_id', $request->store->id]
            ])
            ->select('history_turn_receiveds.*')
            ->when(!empty($request->type_turn), function ($query) use ($request) {
                $query->where('type_from', $request->type_turn);
            })
            ->when(!empty($sortBy) && Schema::hasColumn('history_turn_receiveds', $sortBy), function ($query) use ($sortBy, $descending) {
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
            'data' => $historyTurnPlays,
        ], 200);
    }

    /**
     * 
     * Lịch sử phần quà mini game vòng quay
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam spin_wheel_id required spin_wheel id cần lấy.
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

        $historyGiftPlays = HistoryGiftSpinWheel::join('player_spin_wheels', 'history_gift_spin_wheels.player_spin_wheel_id', '=', 'player_spin_wheels.id')
            ->where([
                ['player_spin_wheels.store_id', $request->store->id],
                ['player_spin_wheels.spin_wheel_id', $request->spin_wheel_id],
                ['player_spin_wheels.customer_id', $request->customer->id],
                ['history_gift_spin_wheels.store_id', $request->store->id]
            ])
            ->select('history_gift_spin_wheels.*')
            ->when($request->type_gift != null, function ($query) use ($request) {
                $query->where($request->type_gift);
            })
            ->when(!empty($sortBy) && Schema::hasColumn('history_gift_spin_wheels', $sortBy), function ($query) use ($sortBy, $descending) {
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
     * Quay vòng quay mini game vòng quay
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam spin_wheel_id required spin_wheel id cần lấy.
     * 
     */
    public function playSpinWheel(Request $request)
    {
        $giftWinning = null;
        $giftOldWinning = null;
        $nowTime = Helper::getTimeNowCarbon();

        $spinWheelExists = DB::table('spin_wheels')
            ->where([
                ['store_id', $request->store->id],
                ['id', $request->spin_wheel_id]
            ])
            ->first();

        if ($spinWheelExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_SPIN_WHEEL_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $playerSpinWheelExists = PlayerSpinWheel::where([
            ['store_id', $request->store->id],
            ['spin_wheel_id', $request->spin_wheel_id],
            ['customer_id', $request->customer->id]
        ])
            ->first();

        if ($playerSpinWheelExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::YOU_HAVE_NOT_JOIN_THIS_GAME[0],
                'msg' => MsgCode::YOU_HAVE_NOT_JOIN_THIS_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($playerSpinWheelExists->total_turn_play < 1) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::YOU_HAVE_NO_TURN_PLAY_GAME[0],
                'msg' => MsgCode::YOU_HAVE_NO_TURN_PLAY_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $checkPercentSpinWheel = DB::table('gift_spin_wheels')
            ->where([
                ['store_id', $request->store->id],
                ['spin_wheel_id', $request->spin_wheel_id],
            ])
            ->sum('percent_received');

        // if ($checkPercentSpinWheel != 100) {
        //     return response()->json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::ERROR[0],
        //         'msg' => MsgCode::ERROR[1],
        //     ], Response::HTTP_BAD_REQUEST);
        // }

        if ($playerSpinWheelExists != null && $playerSpinWheelExists->check_get_turn != $nowTime->format('d')) {
            // $playerSpinWheelExists->update([
            //     'total_turn_play' => $spinWheelExists->turn_in_day,
            //     'check_get_turn' => $nowTime->format('d')
            // ]);
        }


        if ($playerSpinWheelExists->total_turn_play > 0) {
            $listGiftSpinWheel = GiftSpinWheel::where([
                ['store_id', $request->store->id],
                ['spin_wheel_id', $request->spin_wheel_id],
            ])
                ->get();

            $arrProbabilities = clone $listGiftSpinWheel;
            $arrProbabilities = $arrProbabilities->pluck('percent_received')->toArray();
            $arrGiftId = clone $listGiftSpinWheel;
            $arrGiftId = $arrGiftId->pluck('id')->toArray();

            $giftWinning = GiftSpinWheel::where([
                ['store_id', $request->store->id],
                ['spin_wheel_id', $request->spin_wheel_id],
                ['id', $this->handleGroupPercentage($listGiftSpinWheel)],
            ])->first();

            $giftOldWinning = clone $giftWinning;
            if ($giftWinning == null) {
                return response()->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => MsgCode::ERROR[1],
                ], Response::HTTP_BAD_REQUEST);
            } else {
                if ($giftWinning->amount_gift > 0) {
                    $historyGiftSpinWheel = HistoryGiftSpinWheel::create([
                        'store_id' => $request->store->id,
                        'player_spin_wheel_id' => $playerSpinWheelExists->id,
                        'name_gift' => $giftWinning->name,
                        'image_url_gift' => $giftWinning->image_url,
                        'amount_coin_current' => $playerSpinWheelExists->total_coin_received,
                        'amount_gift' => $playerSpinWheelExists->total_gift_received,
                        'type_gift' => $giftWinning->type_gift,
                        'amount_coin_change' => $giftWinning->type_gift == StatusSpinWheelDefineCode::GIFT_IS_COIN ? $giftWinning->amount_coin : 0,
                        'amount_coin_changed' => $giftWinning->type_gift == StatusSpinWheelDefineCode::GIFT_IS_COIN ? $giftWinning->amount_coin + $playerSpinWheelExists->total_coin_received : $playerSpinWheelExists->total_coin_received,
                        'value_gift' => $giftWinning->type_gift == StatusSpinWheelDefineCode::GIFT_IS_ITEM ? $giftWinning->value_gift : null,
                        'text' =>  $giftWinning->type_gift == StatusSpinWheelDefineCode::GIFT_IS_TEXT ? $giftWinning->text : null
                    ]);


                    if ($giftWinning->type_gift == StatusSpinWheelDefineCode::GIFT_IS_COIN) {
                        $playerSpinWheelExists->update([
                            'total_coin_received' => $giftWinning->amount_coin + $playerSpinWheelExists->total_coin_received,
                            'total_turn_play' => $playerSpinWheelExists->total_turn_play - 1
                        ]);
                    } else {
                        $playerSpinWheelExists->update([
                            'total_gift_received' => 1 + $playerSpinWheelExists->total_gift_received,
                            'total_turn_play' => $playerSpinWheelExists->total_turn_play - 1
                        ]);
                    }


                    $giftWinning->update([
                        'amount_gift' => $giftWinning->amount_gift - 1
                    ]);

                    if ($giftWinning->type_gift == StatusSpinWheelDefineCode::GIFT_IS_COIN) {
                        PointCustomerUtils::add_sub_point(
                            PointCustomerUtils::GIFT_AT_SPIN_WHEEL,
                            $request->store->id,
                            $request->customer->id,
                            $giftWinning->amount_coin,
                            $historyGiftSpinWheel->id,
                            $giftWinning->id
                        );
                    }
                } else {
                    $historyGiftSpinWheel = HistoryGiftSpinWheel::create([
                        'store_id' => $request->store->id,
                        'player_spin_wheel_id' => $playerSpinWheelExists->id,
                        'name_gift' => $giftWinning->name,
                        'image_url_gift' => $giftWinning->image_url,
                        'amount_coin_current' => $playerSpinWheelExists->total_coin_received,
                        'amount_gift' => $playerSpinWheelExists->total_gift_received,
                        'type_gift' => $giftWinning->type_gift,
                        'amount_coin_change' => $giftWinning->type_gift == StatusSpinWheelDefineCode::GIFT_IS_COIN ? $giftWinning->amount_coin : 0,
                        'amount_coin_changed' => $giftWinning->type_gift == StatusSpinWheelDefineCode::GIFT_IS_COIN ? $giftWinning->amount_coin + $playerSpinWheelExists->total_coin_received : $playerSpinWheelExists->total_coin_received,
                        'value_gift' => $giftWinning->type_gift == StatusSpinWheelDefineCode::GIFT_IS_ITEM ? $giftWinning->value_gift : null,
                        'text' =>  $giftWinning->type_gift == StatusSpinWheelDefineCode::GIFT_IS_TEXT ? $giftWinning->text : null
                    ]);


                    $playerSpinWheelExists->update([
                        'total_turn_play' => $playerSpinWheelExists->total_turn_play - 1
                    ]);

                    // return response()->json([
                    //     'code' => Response::HTTP_BAD_REQUEST,
                    //     'success' => false,
                    //     'msg_code' => MsgCode::GIFT_HAS_RUN_OUT[0],
                    //     'msg' => MsgCode::GIFT_HAS_RUN_OUT[1],
                    // ], Response::HTTP_BAD_REQUEST);
                    return response()->json([
                        'code' => 200,
                        'success' => true,
                        'msg_code' => MsgCode::SUCCESS[0],
                        'msg' => MsgCode::SUCCESS[1],
                        'data' => [
                            'gift_winning' => $giftOldWinning
                        ],
                    ], 200);
                }
            }
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                'gift_winning' => $giftOldWinning
            ],
        ], 200);
    }


    /**
     * 
     * Lấy lượt chơi mini game vòng quay
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam spin_wheel_id required spin_wheel id cần lấy.
     * @bodyParam type_from int required loại lượt chơi lấy từ.
     * @bodyParam title string tiêu đề.
     * @bodyParam description string mô tả.
     * 
     */
    public function getTurnPlayMiniGame(Request $request)
    {
        $spinWheelExists = DB::table('spin_wheels')
            ->where([
                ['store_id', $request->store->id],
                ['id', $request->spin_wheel_id]
            ])
            ->first();

        if ($spinWheelExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_SPIN_WHEEL_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $playerSpinWheelExists = PlayerSpinWheel::where([
            ['store_id', $request->store->id],
            ['spin_wheel_id', $request->spin_wheel_id],
            ['customer_id', $request->customer->id]
        ])
            ->first();

        if ($playerSpinWheelExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::YOU_HAVE_NOT_JOIN_THIS_GAME[0],
                'msg' => MsgCode::YOU_HAVE_NOT_JOIN_THIS_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        if (StatusHistorySpinWheelDefineCode::getHistoryTypeTurnSpinWheelCode($request->type_turn) == false) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TYPE_FROM_TURN_MINI_GAME[0],
                'msg' => MsgCode::INVALID_TYPE_FROM_TURN_MINI_GAME[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($request->type_turn == StatusHistorySpinWheelDefineCode::TURN_PER_DAY) {
            $isHasGotTurn = DB::table('history_turn_receiveds')
                ->whereDate('created_at', Helper::getTimeNowCarbon()->format('Y-m-d'))
                ->where([
                    ['store_id', $request->store->id],
                    ['player_spin_wheel_id', $playerSpinWheelExists->id],
                    ['type_from', StatusHistorySpinWheelDefineCode::TURN_PER_DAY]
                ])
                ->first();

            if (!empty($isHasGotTurn)) {
                return response()->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::YOU_HAVE_GOT_TURN_PER_DAY_OF_TODAY[0],
                    'msg' => MsgCode::YOU_HAVE_GOT_TURN_PER_DAY_OF_TODAY[1],
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        HistoryTurnReceived::create([
            'store_id' => $request->store->id,
            'player_spin_wheel_id' => $playerSpinWheelExists->id,
            'amount_turn_current' => $playerSpinWheelExists->total_turn_play,
            'amount_turn_changed' => $playerSpinWheelExists->total_turn_play + 1,
            'type_from' => $request->type_turn,
            'title' => $request->title,
            'description' => $request->description
        ]);

        $playerSpinWheelExists->update([
            'total_turn_play' => $playerSpinWheelExists->total_turn_play + 1
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $playerSpinWheelExists
        ], 200);
    }

    /**
     * 
     * Danh sách mini game vòng quay
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam spin_wheel_id required spin_wheel id cần lấy.
     * @bodyParam type_from int required loại lượt chơi lấy từ.
     * 
     */
    public function getSpinWheels(Request $request)
    {
        $listIdSpinWheel = [];
        $isShake = filter_var($request->is_shake, FILTER_VALIDATE_BOOLEAN);
        if ($isShake == false) {
            $spinWheels = DB::table('spin_wheels')
                ->where([
                    ['spin_wheels.store_id', $request->store->id],
                    ['spin_wheels.time_end', '>=', Helper::getTimeNowCarbon()->format('Y-m-d H:i:m')]
                ])
                ->get();

            foreach ($spinWheels as $spinWheelItem) {
                $numGift = DB::table('gift_spin_wheels')->where('spin_wheel_id', $spinWheelItem->id)->count();
                if ($numGift > 2) {
                    array_push($listIdSpinWheel, $spinWheelItem->id);
                }
            }
        }

        $listMiniGameSpinWheel = SpinWheel::where([
            ['spin_wheels.store_id', $request->store->id],
            ['spin_wheels.time_end', '>=', Helper::getTimeNowCarbon()->format('Y-m-d H:59:59')]
        ])
            ->when($request->is_shake != null, function ($query) use ($request, $isShake, $listIdSpinWheel) {
                $query->where('is_shake', $isShake);

                if (!$isShake) {
                    $query->whereIn('id', $listIdSpinWheel);
                }
            })
            ->paginate(request('limit') ?? 20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listMiniGameSpinWheel
        ], 200);
    }

    /**
     * 
     * Thông tin người chơi
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam spin_wheel_id required spin_wheel id cần lấy.
     * 
     */
    public function getInfoPlayer(Request $request)
    {
        $playerSpinWheelExists = PlayerSpinWheel::where([
            ['store_id', $request->store->id],
            ['spin_wheel_id', $request->spin_wheel_id],
            ['customer_id', $request->customer->id]
        ])
            ->first();

        if ($playerSpinWheelExists == null) {
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
            'data' => $playerSpinWheelExists
        ], 200);
    }

    /**
     * 
     * Lấy 1 mini game vòng quay
     * 
     * @urlParam store_code required Store code cần xóa.
     * @urlParam spin_wheel_id required spin_wheel id cần lấy.
     * 
     */
    public function getASpinWheels(Request $request)
    {
        $customerExists = null;
        $nowTime = Helper::getTimeNowCarbon();

        $miniGameSpinWheelExist = SpinWheel::where([
            ['spin_wheels.id', $request->spin_wheel_id],
            ['spin_wheels.store_id', $request->store->id]
            // ['spin_wheels.time_end', '>=', $nowTime->format('Y-m-d H:i:s')]
        ])
            ->first();

        if ($miniGameSpinWheelExist == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SPIN_WHEEL_EXISTS[0],
                'msg' => MsgCode::NO_SPIN_WHEEL_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $checkTokenIsValid = SessionCustomer::where('token', request()->header('customer-token'))->first();

        if ($checkTokenIsValid != null && $checkTokenIsValid->customer_id != null) {
            $customerExists = Customer::where('id', $checkTokenIsValid->customer_id)
                ->where('official', true)
                ->where('store_id', $request->store->id)->first();
        }

        if ($customerExists != null) {
            $playerSpinWheelExists = PlayerSpinWheel::where([
                ['store_id', $request->store->id],
                ['spin_wheel_id', $request->spin_wheel_id],
                ['customer_id', $customerExists->id]
            ])
                ->first();

            // handle update turn play game
            if ($playerSpinWheelExists != null && $playerSpinWheelExists->check_get_turn != $nowTime->format('d')) {
                $playerSpinWheelExists->update([
                    'total_turn_play' => $miniGameSpinWheelExist->turn_in_day,
                    'check_get_turn' => $nowTime->format('d')
                ]);
            }

            $miniGameSpinWheelExist->is_cus_has_joined = $playerSpinWheelExists != null ? true : false;

            $miniGameSpinWheelExist->info_player = $playerSpinWheelExists;
        } else {
            $miniGameSpinWheelExist->is_cus_has_joined = null;
            $miniGameSpinWheelExist->info_player = null;
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $miniGameSpinWheelExist
        ], 200);
    }

    // func handle group percentage
    static function handleGroupPercentage($probabilities)
    {
        $newProbabilities = [];
        foreach ($probabilities as $keyProbabilityItem => $probabilityItem) {
            for ($i = 0; $i < (int)$probabilityItem->percent_received; $i++) {
                array_push($newProbabilities, $probabilityItem->id);
            }
        }

        return $newProbabilities[rand(0, count($newProbabilities) - 1)];
    }

    // func random probabilities
    static function randomProbability($probabilities, $results)
    {
        $total = array_sum($probabilities);
        $random_num = mt_rand(1, $total);
        $counter = 0;
        foreach ($probabilities as $index => $value) {
            $counter += $value;
            if ($counter > $random_num) {
                return $results[$index];
            }
        }
    }
}
