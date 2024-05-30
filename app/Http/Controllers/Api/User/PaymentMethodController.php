<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

/**
 * @group  User/Phương thức thanh toán
 */
class PaymentMethodController extends Controller
{
    /**
     * Danh cách phương thức thanh toán
     */
    public function getAll(Request $request)
    {
        $datas = config('saha.payment_method.payment_method');
        $listMethodConfig = [];


        foreach ($datas as $method) {
            $methodExists = PaymentMethod::where('store_id', $request->store->id)
                ->where('method_id', $method['id'])->first();

            $config = null;
            $description = null;
            $use = false;
            if ($methodExists && isset($methodExists->json_data)) {
                $config = json_decode($methodExists->json_data);
                $use =  $methodExists->use;
                $description =  $config->description ?? "";
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

            array_push($listMethodConfig, [
                'id' => $method['id'],
                'name' => $method['name'],
                'field' => $method['field'],
                'define_field' => $method['define_field'],
                'is_auto' => $method['is_auto'],
                'description' => $method['id'] == 0 ?   $description  :  $method['description'],
                'use' => $use,
                'config' => $config
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $listMethodConfig,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Cập nhật thông tin cho 1 phương thức thanh toán
     * 
     * payment_guide trường hợp này gửi danh sách item gồm 
     * 
     * [ {"account_name":"HTS", "account_number":"4445564", "bank":"BIDV", "branch":"gdfg", "qr_code_image_url} ]
     * 
     * @urlParam  method_id required id cần sửa
     * @bodyParam use boolean Có sử dụng hay không
     * bodyParam Phải điền tất cả các field có trong mảng field khi get tất cả
     */
    public function updateOne(Request $request)
    {
        $method_id = $request->route()->parameter('method_id');

        $datas = config('saha.payment_method.payment_method');
        $listIDMethod = [];

        foreach ($datas as $method) {
            array_push($listIDMethod, $method['id']);
        }

        if ($method_id == null || !in_array($method_id, $listIDMethod)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PAYMENT_METHOD[0],
                'msg' => MsgCode::INVALID_PAYMENT_METHOD[1],
            ], 400);
        }


        $jsonData = null;

        foreach ($datas[$method_id]['field'] as $field) {
            if (!isset($request->all()[$field])) {

                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_FIELD[0] . "|" . $field,
                    'msg' => MsgCode::INVALID_FIELD[1],
                ], 400);
            }


            $jsonData[$field] =  $request->all()[$field];
        }

        if ($method_id == 0) {
            $jsonData['description'] = $request->description;
        }

        $saveDB =  json_encode($jsonData);

        $methodExists = PaymentMethod::where('store_id', $request->store->id)
            ->where('method_id', $method_id)->first();


        if ($method_id == 0) {
            $otherMethod = PaymentMethod::where('store_id', $request->store->id)
                ->where("method_id", '!=', $method_id)->where("use", true)->get();

            if (count($otherMethod) == 0 && filter_var($request->use, FILTER_VALIDATE_BOOLEAN) == false) {
                return response()->json([
                    'code' => 400,
                    'success' => true,
                    'msg_code' => MsgCode::THIS_IS_THE_ONLY_PAYMENT_METHOD[0],
                    'msg' => MsgCode::THIS_IS_THE_ONLY_PAYMENT_METHOD[1],
                ], 400);
            }
        }

        if ($methodExists == null) {

            $data2 = [
                'store_id' => $request->store->id,
                'method_id' => $method_id,
                'json_data' => $saveDB,
                'use' => filter_var($request->use, FILTER_VALIDATE_BOOLEAN),
            ];

            PaymentMethod::create(
                $data2
            );

            return response()->json([
                'code' => 201,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
            ], 201);
        } else {

            $data2 =  [
                'json_data' => $saveDB,
                'use' => filter_var($request->use, FILTER_VALIDATE_BOOLEAN),
            ];



            $methodExists->update(
                $data2
            );


            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
            ], 200);
        }
    }
}
