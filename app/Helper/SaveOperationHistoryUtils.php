<?php

namespace App\Helper;

use App\Events\RedisRealtimeOperationHistoryEvent;
use App\Models\OperationHistory;
use App\Services\BalanceCustomerService;

class SaveOperationHistoryUtils
{


    static function save($request, $action_type, $function_type, $content, $references_id, $references_value)
    {
        try {
            $store_id = $request->store == null ? null :  $request->store->id;

            $action_type = $action_type;
            $function_type = $function_type;

            $staff_id = $request->staff == null ? null : $request->staff->id;
            $staff_name = $request->staff == null ? null : $request->staff->name;

            $user_id = $request->user == null ? null :  $request->user->id;
            $user_name = $request->user == null ? null :  $request->user->name;

            $branch_id = $request->branch == null ? null :  $request->branch->id;
            $branch_name = $request->branch == null ? null : $request->branch->name;

            $content = $content;
            $ip = IPUtils::getIP();

            $references_id = $references_id;
            $references_value = $references_value;

            $created = OperationHistory::create([
                "store_id" =>    $store_id,
                "function_type" =>    $function_type,
                "action_type" =>    $action_type,
                "staff_id" =>    $staff_id,
                "staff_name" =>    $staff_name,
                "user_id" =>    $user_id,
                "user_name" =>    $user_name,
                "branch_id" =>    $branch_id,
                "branch_name" =>    $branch_name,
                "content" =>    $content,
                "ip" =>    $ip,
                "references_id" =>    $references_id,
                "references_value" =>    $references_value,
            ]);

            event($e = new RedisRealtimeOperationHistoryEvent($created, $request->store));
        } catch (\Throwable $th) {
        }
    }
}
