<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentMethod\PayController;
use App\Jobs\PushNotificationUserJob;
use App\Models\Collaborator;
use App\Models\CollaboratorsConfig;
use App\Models\MsgCode;
use App\Models\PayCollaborator;
use Illuminate\Http\Request;

/**
 * @group  Customer/Thanh toán tiền hoa hồng
 */
class CustomerCollaboratorPayController extends Controller
{
    /**
     * Yêu cầu thanh toán
     */
    public function request_payment(Request $request)
    {

        $collaborator = Collaborator::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($collaborator == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NOT_REGISTERED_COLLABORATOR[0],
                'msg' => MsgCode::NOT_REGISTERED_COLLABORATOR[1],
            ], 400);
        }

        $configExists = CollaboratorsConfig::where(
            'store_id',
            $request->store->id
        )->first();

        if ($configExists  == null || $configExists->payment_limit == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::STORE_HAS_NOT_CONFIGURED[0],
                'msg' => MsgCode::STORE_HAS_NOT_CONFIGURED[1],
            ], 400);
        }

        if ($configExists->allow_payment_request == false) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::STORE_NOT_ALLOW_YOU_TO_MAKE_PAYMENTS[0],
                'msg' => MsgCode::STORE_NOT_ALLOW_YOU_TO_MAKE_PAYMENTS[1],
            ], 400);
        }

        if ($collaborator->balance < $configExists->payment_limit) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::LIMIT_NOT_REACHED[0],
                'msg' => MsgCode::LIMIT_NOT_REACHED[1],
            ], 400);
        }

        $payAfter = PayCollaborator::where('store_id', $request->store->id)
            ->where('collaborator_id',  $collaborator->id)->where('status', 0)->first();

        if ($payAfter  != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::HAVE_AN_UNPAID_REQUEST[0],
                'msg' => MsgCode::HAVE_AN_UNPAID_REQUEST[1],
            ], 400);
        }

        if ($collaborator->balance  <= 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::HAVE_AN_UNPAID_REQUEST[0],
                'msg' =>  'Số dư không có để yêu cầu thanh toán'
            ], 400);
        }



        PushNotificationUserJob::dispatch(
            $request->store->id,
            $request->store->user_id,
            'Yêu cầu thanh toán',
            'CTV ' . $request->customer->name . ' vừa gửi yêu cầu thanh toán ',
            TypeFCM::REQUEST_PAY_CTV,
            $request->customer->id,
            null
        );

        PayCollaborator::create([
            "store_id" => $request->store->id,
            "collaborator_id"  =>  $collaborator->id,
            "money"  =>  $collaborator->balance,
            "status"  => 0,
            "from"  => 0,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
