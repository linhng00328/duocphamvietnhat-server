<?php

namespace App\Http\Controllers\Api\User\Shipment;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use Exception;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Validator;

/**
 * @group  User/Đơn vị vận chuyển
 */
class LoginGetTokenController extends Controller
{
    /**
     * Danh cách tất cả đơn vị vận chuyển
     */
    public function viettel(Request $request)
    {
        $client = new GuzzleClient();

        $tokenAgency = "";
        /// lấy token đại lý
        try {
            $headers = [
                'Content-Type' => 'application/json',
            ];
            $response = $client->post(
                'https://partner.viettelpost.vn/v2/user/Login', //  'https://partner.viettelpost.vn/v2/user/ownerconnect',
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' => [
                        "USERNAME" => "hello@ikitech.vn",
                        "PASSWORD" => "Ikitech@2021",
                    ]
                ]
            );
            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);
            $tokenAgency =  $jsonResponse->data->token;
        } catch (Exception $e) {
        }


        $headers = [
            'Content-Type' => 'application/json',
            'token' => $tokenAgency,
        ];

        try {
            $response = $client->post(
                'https://partner.viettelpost.vn/v2/user/ownerconnect',
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' => [
                        "USERNAME" => $request->USERNAME,
                        "PASSWORD" => $request->PASSWORD,
                    ]
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->status != 200) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => $jsonResponse->message,
                ], 400);
            } else {


                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'data' => $jsonResponse->data,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                ], 200);
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => $jsonResponse->message,
                ], 400);
            }
            return new Exception('error');
        } catch (Exception $e) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Không thể đăng nhập",
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            // 'data' => $listShip,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Login Get token nhatTin
     */
    public function nhatTin(Request $request)
    {
        $client = new GuzzleClient();
        $config = config('saha.shipper.list_shipper')[4];
        $base_url = $config["url_base"];
        /// lấy token đại lý
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'username' => $request->USERNAME,
                'password' => $request->PASSWORD,
                'partner_id' => $request->CUSTOMERCODE,
            ];

            $response = $client->get(
                $base_url . '/v1/loc/provinces',
                [
                    'headers' => $headers
                ]
            );
            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->success) {
                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'data' => [
                        'token' => base64_encode(json_encode([
                            "username" => $request->USERNAME,
                            "password" => $request->PASSWORD,
                            "partner_id" => $request->CUSTOMERCODE
                        ]))
                    ],
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                ], 200);
            } else {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Thông tin đăng nhập không hợp lệ",
                ], 400);
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Thông tin đăng nhập không hợp lệ",
                ], 400);
            }
            return new Exception('error');
        } catch (Exception $e) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Thông tin đăng nhập không hợp lệ",
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Login Get token vietnamPost
     */
    public function vietnamPost(Request $request)
    {
        $client = new GuzzleClient();
        $urlSanBox = "https://my-uat.vnpost.vn/MYVNP_API";
        $urlProduction = "https://connect-my.vnpost.vn";
        $token = "";

        /// lấy token
        try {
            $headers = [
                'Content-Type' => 'application/json',
            ];
            $response = $client->post(
                $urlProduction . '/customer-partner/GetAccessToken',
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' => [
                        "username" => $request->USERNAME,
                        "password" => $request->PASSWORD,
                        "customerCode" => $request->CUSTOMERCODE
                    ],
                ]
            );
            $body = (string) $response->getBody();

            $jsonResponse = json_decode($body);
            if ($jsonResponse->success) {
                $token =  $jsonResponse->token;
                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'data' => [
                        'token' => base64_encode(json_encode([
                            'token' => $token,
                            'customerCode' => $request->CUSTOMERCODE,
                            'contractCode' => $request->CONTRACTCODE,
                        ]))
                    ],
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                ], 200);
            } else {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => $jsonResponse->errorMessage,
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đăng nhập không thành công",
            ], 400);
        }
    }
}
