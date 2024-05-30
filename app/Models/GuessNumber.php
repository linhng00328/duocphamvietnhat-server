<?php

namespace App\Models;

use App\Helper\Helper;
use App\Models\Base\BaseModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GuessNumber extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_show_game' => 'boolean',
        'is_guess_number' => 'boolean',
        'is_limit_people' => 'boolean',
        'is_initial_result' => 'boolean',
        'is_show_all_prizer' => 'boolean',
        'apply_fors' => 'array',
        'agency_types' => 'array',
        'group_types' => 'array',
    ];

    protected $appends = [
        'list_result',
        'group_customer',
        'agency_type',
        'list_predict',
        'final_result_announced'
    ];

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    public function getListResultAttribute()
    {
        $listResult = GuessNumberResult::where([
            ['store_id', $this->store_id],
            ['guess_number_id', $this->id]
        ])->get();

        return $listResult;
    }

    public function getGroupCustomerAttribute()
    {
        if (!empty($this->group_customer_id) && $this->group_customer_id != null) {
            $groupCustomerExists = GroupCustomer::where([
                ['store_id', $this->store_id],
                ['id', $this->group_customer_id]
            ])
                ->select('store_id', 'name', 'note', 'group_type')
                ->first();

            return $groupCustomerExists ?? json_decode('{}');
        }
        return json_decode('{}');
    }

    public function getAgencyTypeAttribute()
    {
        if (!empty($this->agency_type_id) && $this->agency_type_id != null) {
            $agencyExists = AgencyType::where([
                ['store_id', $this->store_id],
                ['id', $this->agency_type_id]
            ])
                ->first();

            return $agencyExists ?? json_decode('{}');
        }
        return json_decode('{}');
    }

    public function getListPredictAttribute()
    {
        $listPredictExists = [];

        if (request('customer') == null) {
            $listPredictExists = HistoryGiftGuessNumber::where([
                ['store_id', $this->store_id],
                ['guess_number_id', $this->id]
            ])
                ->first();
        }

        return $listPredictExists;
    }

    public function getFinalResultAnnouncedAttribute()
    {
        try {
            $parseDatetime = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($this->time_end)));
            if ($parseDatetime->lte(Helper::getTimeNowCarbon())) {
                $historyWinPrize = null;
                $giftGuessNumber = null;
                $customer = [];
                $numTakeCus = ($this->is_show_all_prizer ? 20 : 1) ?? 1;

                if ($this->is_guess_number) {
                    $giftGuessNumberRes = [
                        'text_result' => $this->text_result,
                        'value_gift' => $this->value_gift
                    ];
                } else {
                    $giftGuessNumber = DB::table('guess_number_results')
                        ->where([
                            ['store_id', $this->store_id],
                            ['guess_number_id', $this->id],
                            ['is_correct', true]
                        ])
                        ->first();

                    if ($giftGuessNumber == null) {
                        return null;
                    }

                    $historyWinPrize = DB::table('history_gift_guess_numbers')
                        ->join('guess_number_results', 'history_gift_guess_numbers.guess_number_result_id', '=', 'guess_number_results.id')
                        ->where([
                            ['history_gift_guess_numbers.store_id', $this->store_id],
                            ['history_gift_guess_numbers.guess_number_id', $this->id],
                            ['history_gift_guess_numbers.is_correct', true],
                            ['history_gift_guess_numbers.guess_number_result_id', $giftGuessNumber->id]
                        ])
                        ->first();

                    $giftGuessNumberRes = [
                        'text_result' => (string)$giftGuessNumber->text_result,
                        'value_gift' => $giftGuessNumber->value_gift
                    ];
                }

                $customer = DB::table('player_guess_numbers')
                    ->join('customers', 'player_guess_numbers.customer_id', '=', 'customers.id')
                    ->join('history_gift_guess_numbers', 'player_guess_numbers.id', '=', 'history_gift_guess_numbers.player_guess_number_id')
                    ->where([
                        ['player_guess_numbers.store_id', $this->store_id],
                        ['player_guess_numbers.guess_number_id', $this->id]
                    ])
                    // ->when($historyWinPrize != null, function ($query) use ($historyWinPrize) {
                    //     $query->where('player_guess_numbers.id', $historyWinPrize->player_guess_number_id);
                    // })
                    ->when($giftGuessNumber != null, function ($query) use ($giftGuessNumber) {
                        $query->where('value_predict', $giftGuessNumber->text_result);
                    })
                    ->when($this->is_guess_number != null, function ($query) {
                        // $query->where('value_predict', 'LIKE', '%' . $this->text_result . '%');
                        $query->where('value_predict', $this->text_result);
                    })
                    ->select('customers.*', "history_gift_guess_numbers.created_at")
                    ->orderBy("history_gift_guess_numbers.created_at", 'asc')
                    ->distinct()
                    ->take($numTakeCus)
                    ->get();

                return [
                    'customer_win' => $customer,
                    'game_resulted' => $giftGuessNumberRes
                ];
            }
        } catch (Exception $ex) {
            return null;
        }
        return null;
    }
}
