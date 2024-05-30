<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;


/**
 * @group  Customer/Phương thức thanh toán
 */

class CustomerPaymentMethodController extends Controller
{
    static function get_payment_method_availible(Request $request)
    {
        $datas = config('saha.payment_method.payment_method');
        $listMethodConfig = [];


        foreach ($datas as $method) {
            $methodExists = PaymentMethod::where('store_id', $request->store->id)
                ->where('method_id', $method['id'])->first();

            $config = null;
            $use = false;


            if ($method['id'] == 1) { //phương thức thanh toán ngân hàng

                if ($methodExists && isset($methodExists->json_data)) {

                    $config = json_decode($methodExists->json_data);
                }

                $otherMethod = PaymentMethod::where("store_id", $request->store->id)
                    ->where("method_id", '!=', 0)->where("use", true)->get();
                if (count($otherMethod) == 0 && filter_var($request->use, FILTER_VALIDATE_BOOLEAN) == false) {


                    if ($methodExists == null) {
                        // $methodExists  = PaymentMethod::create(
                        //     [
                        //         'store_id' => $request->store->id,
                        //         'method_id' => 0,
                        //         'use' => true
                        //     ]
                        // );
                    } else {
                        // $methodExists->update(
                        //     [
                        //         'use' => true,
                        //     ]
                        // );
                    }
                }
            }

            if ($method['id'] == 0) {
                $otherMethod = PaymentMethod::where("store_id", $request->store->id)
                    ->where("method_id", '!=', 0)->where("use", true)->get();
                if (count($otherMethod) == 0 && filter_var($request->use, FILTER_VALIDATE_BOOLEAN) == false) {
                    $use = true;

                    if ($methodExists == null) {
                        PaymentMethod::create(
                            [
                                'store_id' => $request->store->id,
                                'method_id' => 0,
                                'use' => true
                            ]
                        );
                    } else {
                        $methodExists->update(
                            [
                                'use' => true,
                            ]
                        );
                    }
                }
            }

            $use =  $methodExists->use ?? false;

            if ($use == true) {
                array_push($listMethodConfig, [
                    'id' => $method['id'],
                    'name' => $method['name'],
                    'config' => $config,
                    'is_auto' => $method['is_auto'],
                    'description' => $method['description'],
                    'use' => $use,
                    'payment_method_id' => $method['payment_method_id'],
                ]);
            }
        }

        return  $listMethodConfig;
    }
    /**
     * Danh sách phương thức thanh toán
     */
    public function getAll(Request $request)
    {


        $listMethodConfig = CustomerPaymentMethodController::get_payment_method_availible($request);


        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $listMethodConfig,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
