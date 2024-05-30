<?php

namespace App\Http\Controllers\Api;

use App\Helper\AgencyUtils;
use App\Helper\CollaboratorUtils;
use App\Helper\Helper;
use App\Helper\StatusGuessNumberDefineCode;
use App\Helper\StatusSpinWheelDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Jobs\PushNotificationUserJob;
use App\Jobs\PushNotificationUserJobEndDay;
use App\Jobs\PushNotificationUserJobGoodNight;
use App\Models\CollaboratorsConfig;
use App\Models\Customer;
use App\Models\GuessNumber;
use App\Models\SpinWheel;
use App\Models\TaskNoti;
use App\Services\SettlementCollaboratorService;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group  All/Thông tin server
 */
class EveryMinuteController extends Controller
{
    /**
     * Chạy mỗi phút một lần
     */
    public function runEveryMinute(Request $request)
    {
        $this->handleSettlement();
        $this->handleNotiToCustomer();
        $this->handleEndDay();

        try {
            $this->handleMiniGame();
        } catch (Exception $ex) {
        }
    }


    public function handleNotiToCustomer()
    {
        $date = Helper::getTimeNowDateTime();
        $dateC =  Carbon::parse(Helper::getTimeNowString());
        $time1 = $date->format('H:i:00');
        $time2 = $date->format('H:i:59');

        $dayNow = (int)$date->format('d');
        $monthNow =  (int)$date->format('m');
        $dayOfWeek =    (int)$dateC->dayOfWeek;

        //Xử lý 1 lần
        $timeOnce1 = $date->format('Y-m-d H:i:00');
        $timeOnce2 = $date->format('Y-m-d H:i:59');

        $listCanOnce = TaskNoti::where('status', 0)
            ->where('type_schedule', 0)
            ->whereBetween('time_run', [$timeOnce1,  $timeOnce2])
            ->get();


        foreach ($listCanOnce  as $itemTask) {
            if ($itemTask->type_schedule === 0) {

                if ($itemTask->group_customer == 1) {
                    $listCustomer = Customer::where(
                        'store_id',
                        $itemTask->store_id
                    );

                    $dayBirth1 = $date->format('Y-m-d 00:00:00');
                    $dayBirth2 = $date->format('Y-m-d 23:59:59');

                    $listCustomer =  $listCustomer
                        ->where('day_of_birth', '>=',  $dayBirth1)
                        ->where('day_of_birth', '<',   $dayBirth2);

                    $listCustomer =  $listCustomer->get();

                    foreach ($listCustomer as $customer) {
                        PushNotificationCustomerJob::dispatch(
                            $itemTask->store_id,
                            $customer->id,
                            $itemTask->title,
                            $itemTask->description,

                            TypeFCM::SEND_ALL,
                            null,
                            $itemTask->type_action,
                            $itemTask->value_action,
                        )->onConnection('sync');
                    }
                }

                if ($itemTask->group_customer == 2) {
                    $listCustomer = Customer::where(
                        'store_id',
                        $itemTask->store_id
                    );
                    $listCustomer =  $listCustomer->get();

                    foreach ($listCustomer as $customer) {

                        if (AgencyUtils::isAgencyByIdAndLever($customer->id, $itemTask->agency_type_id)) {
                            PushNotificationCustomerJob::dispatch(
                                $itemTask->store_id,
                                $customer->id,
                                $itemTask->title,
                                $itemTask->description,
                                TypeFCM::SEND_ALL,
                                null,
                                $itemTask->type_action,
                                $itemTask->value_action,
                            )->onConnection('sync');
                        }
                    }
                }

                if ($itemTask->group_customer == 3) {
                    $listCustomer = Customer::where(
                        'store_id',
                        $itemTask->store_id
                    );
                    $listCustomer =  $listCustomer->get();

                    foreach ($listCustomer as $customer) {
                        if (CollaboratorUtils::isCollaborator($customer->id, $itemTask->store_id,)) {
                            PushNotificationCustomerJob::dispatch(
                                $itemTask->store_id,
                                $customer->id,
                                $itemTask->title,
                                $itemTask->description,
                                TypeFCM::SEND_ALL,
                                null,
                                $itemTask->type_action,
                                $itemTask->value_action,
                            )->onConnection('sync');
                        }
                    }
                }
                if ($itemTask->group_customer == 0) {
                    PushNotificationCustomerJob::dispatch(
                        $itemTask->store_id,
                        null,
                        $itemTask->title,
                        $itemTask->description,
                        TypeFCM::SEND_ALL,
                        $itemTask->group_customer,
                        $itemTask->type_action,
                        $itemTask->value_action,
                    )->onConnection('sync');
                }




                $task = TaskNoti::where(
                    'id',
                    $itemTask->id
                )->first();

                $task->update([
                    'status' => 2,
                    'time_run_near' => $dateC
                ]);
            }
        }


        //Xử lý noti lịch trình lặp lại
        $listCanHandle = TaskNoti::where('status', 0)
            ->where('type_schedule', '<>', 0)
            ->whereTime('time_of_day', '>=', $time1)
            ->whereTime('time_of_day', '<', $time2)
            ->get();

        $countListTasks = count($listCanHandle);
        echo "Count task: " . $countListTasks;

        foreach ($listCanHandle as $itemTask) {

            $allowSend = false;
            if ($itemTask->type_schedule === 1) {
                $allowSend = true;
            }

            if ($itemTask->type_schedule === 2) {
                if ($itemTask->day_of_week ==  $dayOfWeek) {
                    $allowSend = true;
                }
            }

            if ($itemTask->type_schedule === 3) {
                if ($itemTask->day_of_month ==   $dayNow) {
                    $allowSend = true;
                }
            }

            if ($allowSend === true) {



                if ($itemTask->group_customer == 1) {
                    $listCustomer = Customer::where(
                        'store_id',
                        $itemTask->store_id
                    );

                    $dayBirth1 = $date->format('Y-m-d 00:00:00');
                    $dayBirth2 = $date->format('Y-m-d 23:59:59');

                    $listCustomer =  $listCustomer
                        ->where('day_of_birth', '>=',  $dayBirth1)
                        ->where('day_of_birth', '<',   $dayBirth2);

                    $listCustomer =  $listCustomer->get();

                    foreach ($listCustomer as $customer) {
                        PushNotificationCustomerJob::dispatch(
                            $itemTask->store_id,
                            $customer->id,
                            $itemTask->title,
                            $itemTask->description,

                            TypeFCM::SEND_ALL,
                            null,
                            $itemTask->type_action,
                            $itemTask->value_action,
                        )->onConnection('sync');
                    }
                } else if ($itemTask->group_customer == 3) {
                    $listCustomer = Customer::where(
                        'store_id',
                        $itemTask->store_id
                    );
                    $listCustomer =  $listCustomer->get();

                    foreach ($listCustomer as $customer) {

                        if (CollaboratorUtils::isCollaborator($customer->id, $itemTask->store_id,)) {
                            echo "sendto: " .   $customer->id . ",";
                            PushNotificationCustomerJob::dispatch(
                                $itemTask->store_id,
                                $customer->id,
                                $itemTask->title,
                                $itemTask->description,
                                TypeFCM::SEND_ALL,
                                null,
                                $itemTask->type_action,
                                $itemTask->value_action,
                            )->onConnection('sync');
                        }
                    }
                } else if ($itemTask->group_customer == 2) {
                    $listCustomer = Customer::where(
                        'store_id',
                        $itemTask->store_id
                    );
                    $listCustomer =  $listCustomer->get();

                    foreach ($listCustomer as $customer) {

                        if (AgencyUtils::isAgencyByIdAndLever($customer->id, $itemTask->agency_type_id)) {

                            echo "sendto: " .   $customer->id . ",";

                            PushNotificationCustomerJob::dispatch(
                                $itemTask->store_id,
                                $customer->id,
                                $itemTask->title,
                                $itemTask->description,
                                TypeFCM::SEND_ALL,
                                null,
                                $itemTask->type_action,
                                $itemTask->value_action,
                            )->onConnection('sync');
                        }
                    }
                } else {

                    echo "type: " .  TypeFCM::SEND_ALL;

                    PushNotificationCustomerJob::dispatch(
                        $itemTask->store_id,
                        null,
                        $itemTask->title,
                        $itemTask->description,
                        TypeFCM::SEND_ALL,
                        $itemTask->group_customer,
                        $itemTask->type_action,
                        $itemTask->value_action,
                    )->onConnection('sync');
                }



                $task = TaskNoti::where(
                    'id',
                    $itemTask->id
                )->first();

                $task->update([
                    'time_run_near' => $dateC
                ]);

                echo "ran task: " .  $itemTask->id;
            }
        }
    }

    public function handleSettlement()
    {
        $now = Helper::getTimeNowDateTime();
        $timeNow = $now->format('H:i');

        $isDay1 = (int)$now->format('d') == 1;
        $isDay16 = (int)$now->format('d') == 16;

        if ($isDay1 || $isDay16) {
            if ($timeNow == "00:00") {
                $callaboratorExists = CollaboratorsConfig::get();
                foreach ($callaboratorExists as $callaboratorConfig) {
                    if (($callaboratorConfig->payment_1_of_month === true &&  $isDay1) || ($callaboratorConfig->payment_16_of_month === true &&  $isDay16)) {
                        SettlementCollaboratorService::settlement($callaboratorConfig->store_id);
                    }
                }
            }
        }
    }


    public function handleEndDay()
    {

        $dateC =  Carbon::parse(Helper::getTimeNowString());

        if ($dateC->hour == 19 && $dateC->minute == 45) {
            PushNotificationUserJobEndDay::dispatch();
        }

        if ($dateC->hour == 22 && $dateC->minute == 0) {
            PushNotificationUserJobGoodNight::dispatch();
        }
    }

    public function handleMiniGame()
    {
        // handle game guess number
        // case User didn't set result
        $listGuessNumberHasNotResult = DB::table('guess_numbers')
            ->where([
                ['is_initial_result', false],
                ['time_end', date('Y-m-d H:i:s', mktime(date('H') + 6, 00, 00, date('m'), date('d'), date('Y')))]
            ])
            ->get();

        foreach ($listGuessNumberHasNotResult as $guessNumberHasNotResult) {
            PushNotificationUserJob::dispatch(
                $guessNumberHasNotResult->store_id,
                $guessNumberHasNotResult->user_id,
                'Yêu cầu nhập kết quả mini game đoán số',
                'Mini game ' . $guessNumberHasNotResult->name . ' chưa nhập kết quả ',
                TypeFCM::TYPE_RESULT_MINI_GAME_GUESS_NUMBER,
                $guessNumberHasNotResult->id,
                null
            );
        }

        $listHistoryGiftGuessNumber = DB::table('history_gift_guess_numbers')
            ->join('guess_number_results', 'history_gift_guess_numbers.guess_number_result_id', '=', 'guess_number_results.id')
            ->join('guess_numbers', 'guess_number_results.guess_number_id', '=', 'guess_numbers.id')
            ->join('player_guess_numbers', 'guess_numbers.id', '=', 'player_guess_numbers.guess_number_id')
            ->whereNotNull('is_correct')
            ->where([
                ['guess_numbers.is_initial_result', true],
                ['guess_numbers.time_end', '<=', helper::getTimeNowDateTime()],
                ['is_correct', true]
            ])
            ->select('guess_number_results.*', 'customer_id')
            ->get();

        foreach ($listHistoryGiftGuessNumber as $historyGiftGuessNumber) {
            PushNotificationCustomerJob::dispatch(
                $historyGiftGuessNumber->store_id,
                $historyGiftGuessNumber->customer_id,
                "Kết quả mini game đoán số",
                "Chúc mừng bạn đã đoán trúng",
                TypeFCM::CUSTOMER_WIN_MINI_GAME_GUESS_NUMBER,
                $historyGiftGuessNumber->guess_number_result_id,
            );
        }


        // handle status game

        // spin wheel   
        // update game upcoming
        SpinWheel::where([
            ['time_start', '<=', Helper::getTimeNowCarbon()],
            ['time_end', '>=', Helper::getTimeNowCarbon()],
            ['status', StatusSpinWheelDefineCode::PROGRESSING]
        ])
            ->update([
                'status' => StatusSpinWheelDefineCode::COMPLETED
            ]);
        // update game is in progress
        SpinWheel::where([
            ['time_end', '<=', Helper::getTimeNowCarbon()],
            ['status', StatusSpinWheelDefineCode::COMPLETED]
        ])
            ->update([
                'status' => StatusSpinWheelDefineCode::CANCELED
            ]);
        // guess number   
        // update game upcoming
        GuessNumber::where([
            ['time_start', '<=', Helper::getTimeNowCarbon()],
            ['time_end', '>=', Helper::getTimeNowCarbon()],
            ['status', StatusGuessNumberDefineCode::PROGRESSING]
        ])
            ->update([
                'status' => StatusGuessNumberDefineCode::COMPLETED
            ]);
        // update game is in progress
        // GuessNumber::where([
        //     ['time_end', '<=', Helper::getTimeNowCarbon()],
        //     ['status', StatusGuessNumberDefineCode::COMPLETED]
        // ])
        //     ->update([
        //         'status' => StatusGuessNumberDefineCode::CANCELED
        //     ]);
    }
}
