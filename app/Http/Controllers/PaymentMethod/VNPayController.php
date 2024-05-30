<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Helper\PaymentUtils;
use App\Helper\StatusDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Api\Customer\CustomerPaymentMethodController;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationJob;
use App\Jobs\PushNotificationUserJob;
use App\Models\LinkBackPay;
use App\Models\Order;
use App\Models\OrderRecord;
use App\Models\PaymentMethod;
use App\Models\StatusPaymentHistory;
use App\Models\UserDeviceToken;
use App\Models\WebhookHistory;
use App\Traits\VNPay;
use Exception;
use Illuminate\Http\Request;


/**
 * @group  Customer/thanh toán
 */
class VNPayController extends Controller
{
    use VNPay;

    // public function index(Request $request)
    // {
    //     return $this->pay($request);
    // }

    // public function check_status()
    // {
    //     $inputData = array();
    //     $returnData = array();
    //     $data = $_REQUEST;
    //     foreach ($data as $key => $value) {
    //         if (substr($key, 0, 4) == "vnp_") {
    //             $inputData[$key] = $value;
    //         }
    //     }



    // $env =  env("APP_ENV", "local");
    // $vnp_TmnCode =  $env == "local" ? PaymentUtils::vnp_Url_dev : PaymentUtils::vnp_Url_main; //Mã website tại VNPAY 
    // $vnp_HashSecret = $env == "local" ? PaymentUtils::vnp_HashSecret_dev : PaymentUtils::vnp_HashSecret_main;  //Chuỗi bí mật
    // $vnp_Url = $env == "local" ? PaymentUtils::vnp_Url_dev : PaymentUtils::vnp_Url_main;


    //     $vnp_SecureHash = $inputData['vnp_SecureHash'];
    //     unset($inputData['vnp_SecureHashType']);
    //     unset($inputData['vnp_SecureHash']);
    //     ksort($inputData);
    //     $i = 0;
    //     $hashData = "";
    //     foreach ($inputData as $key => $value) {
    //         if ($i == 1) {
    //             $hashData = $hashData . '&' . $key . "=" . $value;
    //         } else {
    //             $hashData = $hashData . $key . "=" . $value;
    //             $i = 1;
    //         }
    //     }
    //     $vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
    //     $vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
    //     $secureHash = hash('sha256', $vnp_HashSecret . $hashData);
    //     $Status = 0;
    //     $orderId = $inputData['vnp_TxnRef'];

    //     try {
    //         //Check Orderid    
    //         //Kiểm tra checksum của dữ liệu
    //         if ($secureHash == $vnp_SecureHash) {
    //             //Lấy thông tin đơn hàng lưu trong Database và kiểm tra trạng thái của đơn hàng, mã đơn hàng là: $orderId            
    //             //Việc kiểm tra trạng thái của đơn hàng giúp hệ thống không xử lý trùng lặp, xử lý nhiều lần một giao dịch
    //             //Giả sử: $order = mysqli_fetch_assoc($result);   
    //             $order = NULL;
    //             if ($order != NULL) {
    //                 if ($order["Status"] != NULL && $order["Status"] == 0) {
    //                     if ($inputData['vnp_ResponseCode'] == '00') {
    //                         $Status = 1;
    //                     } else {
    //                         $Status = 2;
    //                     }
    //                     //Cài đặt Code cập nhật kết quả thanh toán, tình trạng đơn hàng vào DB
    //                     //
    //                     //
    //                     //
    //                     //Trả kết quả về cho VNPAY: Website TMĐT ghi nhận yêu cầu thành công                
    //                     $returnData['RspCode'] = '00';
    //                     $returnData['Message'] = 'Confirm Success';
    //                 } else {
    //                     $returnData['RspCode'] = '02';
    //                     $returnData['Message'] = 'Order already confirmed';
    //                 }
    //             } else {
    //                 $returnData['RspCode'] = '01';
    //                 $returnData['Message'] = 'Order not found';
    //             }
    //         } else {
    //             $returnData['RspCode'] = '97';
    //             $returnData['Message'] = 'Chu ky khong hop le';
    //         }
    //     } catch (Exception $e) {
    //         $returnData['RspCode'] = '99';
    //         $returnData['Message'] = 'Unknow error';
    //     }
    //     //Trả lại VNPAY theo định dạng JSON
    //     echo json_encode($returnData);
    // }

    public function getDataPay(Request $request)
    {

        $methodExists = PaymentMethod::where('store_id', $request->store->id)
            ->where('method_id', 2)->first();
        $testing = false;

        $config = null;

        $vnp_TmnCode = "";
        $vnp_HashSecret = "";
        if ($methodExists && isset($methodExists->json_data)) {
            $config = json_decode($methodExists->json_data);

            $vnp_TmnCode  =  $config->vnp_TmnCode;
            $vnp_HashSecret =  $config->vnp_HashSecret;
            $testing =  $methodExists->testing;
        }

        return [
            "testing" => $testing,
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_HashSecret" => $vnp_HashSecret,
        ];
    }


    public function create(Request $request)
    {
        $host = $request->getSchemeAndHttpHost();

        $store_code = $request->store->store_code;
        $order_code = $request->order->order_code;
        $dataPay = $this->getDataPay($request);
        session(['cost_id' => $request->id]);
        session(['url_prev' => url()->previous()]);


        $testing =   $dataPay['testing'];
        $vnp_TmnCode =   $dataPay['vnp_TmnCode'];
        $vnp_HashSecret =   $dataPay['vnp_HashSecret'];
        // $vnp_Url = PaymentUtils::vnp_Url_main;
        // if ($testing  == true) {
            $vnp_Url = PaymentUtils::vnp_Url_dev;
        // }

        // $vnp_BankCode = "NCB";
        $vnp_Returnurl = $host . "/api/customer/$store_code/purchase/return/vn_pay";
        $vnp_TxnRef =  $order_code; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo = "Thanh toán hóa đơn .$order_code";
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $request->order->total_final * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+60 minutes', strtotime($startTime)));

        // //Billing
        // $vnp_Bill_Mobile = $_POST['txt_billing_mobile'] ?? "tesst";
        // $vnp_Bill_Email = $_POST['txt_billing_email'] ?? "tesst@gmail.com";
        // $fullName = trim($_POST['txt_billing_fullname'] ?? "Linh Nguyen");
        // if (isset($fullName) && trim($fullName) != '') {
        //     $name = explode(' ', $fullName);
        //     $vnp_Bill_FirstName = array_shift($name);
        //     $vnp_Bill_LastName = array_pop($name);
        // }
        // $vnp_Bill_Address = $_POST['txt_inv_addr1'] ?? "tesst";
        // $vnp_Bill_City = $_POST['txt_bill_city'] ?? "tesst";
        // $vnp_Bill_Country = $_POST['txt_bill_country'] ?? "tesst";
        // $vnp_Bill_State = $_POST['txt_bill_state'] ?? "tesst";
        // // Invoice
        // $vnp_Inv_Phone = $_POST['txt_inv_mobile'] ?? "tesst";
        // $vnp_Inv_Email = $_POST['txt_inv_email'] ?? "tesst";
        // $vnp_Inv_Customer = $_POST['txt_inv_customer'] ?? "tesst";
        // $vnp_Inv_Address = $_POST['txt_inv_addr1'] ?? "tesst";
        // $vnp_Inv_Company = $_POST['txt_inv_company'] ?? "tesst";
        // $vnp_Inv_Taxcode = $_POST['txt_inv_taxcode'] ?? "tesst";
        // $vnp_Inv_Type = $_POST['cbo_inv_type'] ?? "tesst";
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $expire
        );
      

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        // if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
        //     $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        // }
        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        return redirect($vnp_Url);
    }

    public function return(Request $request)
    {

        $content = $request->all();

        $webHook = WebhookHistory::create([
            'order_code' => "VNPAY",
            'json' =>  json_encode([
                "ip" => $request->ip(),
                "content" =>  $request->fullUrl()
            ]),
        ]);

        try {
            $url = $request->fullUrl();
            $isIPN = false;
            if (str_contains($url, "/ipn/")) {
                $isIPN = true;
            }

            $dataPay = $this->getDataPay($request);
            $vnp_HashSecret =   $dataPay['vnp_HashSecret'];


            $vnp_SecureHash = $_GET['vnp_SecureHash'];
            $inputData = array();
            foreach ($_GET as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }

            unset($inputData['vnp_SecureHash']);
            ksort($inputData);
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            if ($secureHash == $vnp_SecureHash) {

                $historyExists = StatusPaymentHistory::where(
                    'order_code',
                    $inputData['vnp_TxnRef']
                )->where('pay_date', $inputData['vnp_PayDate'])->first();

                $orderExists = Order::where(
                    'order_code',
                    $inputData['vnp_TxnRef']
                )->first();


                if ($orderExists == null) {
                    if ($isIPN) {
                        return response()->json([
                            'Message' => 'Order Not Found',
                            'RspCode' => '01'
                        ]);
                    } else {
                        return response()->view(
                            'error_pay',
                            ['link_back' =>  ""]
                        );
                    }
                }

                $webHook->update([
                    "order_code" => "VNPAY - " . ($inputData['vnp_TxnRef'] ?? "")
                ]);

                $vnp_Amount = $inputData['vnp_Amount'] / 100;

                if ($orderExists->total_final != $vnp_Amount) {
                    if ($isIPN) {
                        return response()->json([
                            'Message' => 'Invalid amount',
                            'RspCode' => '04'
                        ]);
                    } else {
                        return response()->view(
                            'error_pay',
                            ['link_back' =>  ""]
                        );
                    }
                }

                if (!empty($historyExists) && $_GET['vnp_ResponseCode'] == '00') {
                    if ($isIPN) {
                        return response()->json([
                            'Message' => 'Order already confirmed',
                            'RspCode' => '02'
                        ]);
                    } else {
                        return response()->view(
                            'success_paid',
                            [
                                'link_back' => ""
                            ]
                        );
                    }
                }
                if (empty($historyExists) && $_GET['vnp_ResponseCode'] == '00') {
                    StatusPaymentHistory::create(
                        [
                            "order_code" => $inputData['vnp_TxnRef'],
                            "transaction_no" => $inputData['vnp_TransactionNo'],
                            "amount" => ($inputData['vnp_Amount'] != null && $inputData['vnp_Amount'] > 0) ? $inputData['vnp_Amount'] / 100 : 0,
                            "bank_code" => $inputData['vnp_BankCode'],
                            "card_type" => $inputData['vnp_CardType'],
                            "order_info" => $inputData['vnp_OrderInfo'],
                            "pay_date" => $inputData['vnp_PayDate'],
                            "response_code" => $inputData['vnp_ResponseCode'],
                            "key_code_customer" => $inputData['vnp_TmnCode'],
                        ]
                    );

                    if (!empty($orderExists)) {

                        PushNotificationUserJob::dispatch(
                            $request->store->id,
                            $request->store->user_id,
                            'Shop ' . $orderExists->store->name,
                            'Đơn hàng ' . $orderExists->order_code . ' đã được thanh toán',
                            TypeFCM::CUSTOMER_PAID,
                            $orderExists->order_code,
                            null
                        );

                        if ($orderExists->customer_id != null) {
                            StatusDefineCode::saveOrderStatus(
                                $request->store->id,
                                $orderExists->customer_id,
                                $orderExists->id,
                                "Đã thanh toán đơn hàng qua VNPAY",
                                1,
                                true,
                                null
                            );
                        }

                        $orderExists->update(
                            [
                                "payment_status" => StatusDefineCode::PAID,
                                'remaining_amount' =>  0,
                                'cod' =>  0,
                            ]
                        );
                    }
                }


                if ($_GET['vnp_ResponseCode'] == '00') {

                    if (!empty($orderExists)) {
                        $orderExists->update(

                            [
                                "payment_status" => 2,
                            ]
                        );
                    }

                    $link_back = null;
                    $linkBackExists = LinkBackPay::where('order_id',  $orderExists->id)->first();
                    if (!empty($linkBackExists->link_back)) {
                        $link_back = $linkBackExists->link_back;
                    }


                    if ($isIPN) {
                        return response()->json([
                            'Message' => 'Confirm Success',
                            'RspCode' => '00'
                        ]);
                    } else {
                        return response()->view(
                            'success_paid',
                            [
                                'link_back' =>  $link_back
                            ]
                        );
                    }
                } else {


                    $link_back = null;
                    $linkBackExists = LinkBackPay::where('order_id',  $orderExists->id)->first();
                    if (!empty($linkBackExists->link_back)) {
                        $link_back = $linkBackExists->link_back;
                    }

                    if ($isIPN) {
                        return response()->json([
                            'Message' => 'Confirm Success',
                            'RspCode' => '00'
                        ]);
                    } else {
                        return response()->view(
                            'error_pay',
                            ['link_back' =>  $link_back]
                        );
                    }


                    echo "Thanh toán không thành công xin thử lại";
                }
            } else {
                if ($isIPN) {
                    return response()->json([
                        'Message' => 'Invalid Checksum',
                        'RspCode' => '97'
                    ]);
                } else {
                    return response()->view(
                        'error_pay',
                        ['link_back' =>  ""]
                    );
                }
            }
        } catch (Exception $e) {
            if ($isIPN) {
                return response()->json([
                    'Message' => 'Unknow error',
                    'RspCode' => '99'
                ]);
            } else {
                return response()->view(
                    'error_pay',
                    ['link_back' =>  ""]
                );
            }
        }
    }
}
