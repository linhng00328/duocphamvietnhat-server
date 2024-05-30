<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Helper\Helper;
use App\Helper\StatusDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Models\LinkBackPay;
use App\Models\Order;
use App\Models\OrderRecord;
use App\Models\PaymentMethod;
use App\Models\StatusPaymentHistory;
use Exception;
use Illuminate\Http\Request;


/**
 * @group  Customer/thanh toán onpay
 */
class OnePayController extends Controller
{


    public function index(Request $request)
    {
        return $this->pay($request);
    }


    public function getDataPay(Request $request)
    {

        $methodExists = PaymentMethod::where('store_id', $request->store->id)
            ->where('method_id', 3)->first();

        $config = null;

        $merchant = "";
        $hascode = "";
        $access_code = "";
        if ($methodExists && isset($methodExists->json_data)) {
            $config = json_decode($methodExists->json_data);

            $merchant  =  $config->merchant;
            $hascode =  $config->hascode;
            $access_code =  $config->access_code;
        }

        return [
            "merchant" => $merchant,
            "hascode" => $hascode,
            "access_code" => $access_code,
        ];
    }

    public function create(Request $request)
    {
        $host = $request->getSchemeAndHttpHost();

        $order_code = $request->order->order_code;
        $store_code = $request->store->store_code;


        $dataPay = $this->getDataPay($request);

        session(['cost_id' => $request->id]);
        session(['url_prev' => url()->previous()]);

        $env =  env("APP_ENV", "local");

        $vpc_Url = $env == "local" ? "https://mtf.onepay.vn/paygate/vpcpay.op" : "https://onepay.vn/paygate/vpcpay.op";


        $vpc_AccessCode = $dataPay['access_code'] ?? "";
        $vpc_Merchant = $dataPay['merchant'] ?? "";
        $SECURE_SECRET = $dataPay['hascode'] ?? "";

        $vpc_Amount = $request->order->total_final * 100;
        $vpc_Locale = 'vn';
        $vpc_IpAddr = request()->ip();
        $vnp_Returnurl = $host . "/api/customer/$store_code/purchase/return/one_pay";

        $inputData = array(
            "vpc_Version" => "2",
            "vpc_Currency" => "VND",
            "vpc_Command" => "pay",
            "vpc_AccessCode" => $vpc_AccessCode,
            "vpc_Merchant" => $vpc_Merchant,
            "vpc_Locale" =>  $vpc_Locale,

            "vpc_ReturnURL" =>  $vnp_Returnurl,
            "vpc_MerchTxnRef" => Helper::generateRandomNum(20),
            "vpc_OrderInfo" =>  $order_code,
            "vpc_Amount" => $vpc_Amount,
            "vpc_TicketNo" => $vpc_IpAddr,
            "Title" => "Thanh toán đơn hàng " . $order_code,
            "AgainLink" => $vnp_Returnurl
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . $key . "=" . $value;
            } else {
                $hashdata .= $key . "=" . $value;
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vpc_Url = $vpc_Url . "?" . $query;


        $stringHashData = "";

        // sort all the incoming vpc response fields and leave out any with no value
        foreach ($inputData as $key => $value) {
            //        if ($key != "vpc_SecureHash" or strlen($value) > 0) {
            //            $stringHashData .= $value;
            //        }
            //      *****************************chỉ lấy các tham số bắt đầu bằng "vpc_" hoặc "user_" và khác trống và không phải chuỗi hash code trả về*****************************
            if ($key != "vpc_SecureHash" && (strlen($value) > 0) && ((substr($key, 0, 4) == "vpc_") || (substr($key, 0, 5) == "user_"))) {
                $stringHashData .= $key . "=" . $value . "&";
            }
        }
        //  *****************************Xóa dấu & thừa cuối chuỗi dữ liệu*****************************
        $stringHashData = rtrim($stringHashData, "&");



        $vnpSecureHash =   strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*', $SECURE_SECRET)));
        $vpc_Url .= 'vpc_SecureHash=' . $vnpSecureHash;




        return redirect($vpc_Url);
    }

    public function return(Request $request)
    {

        $dataPay = $this->getDataPay($request);
        $SECURE_SECRET = $dataPay['hascode'] ?? "";

        $vpc_Txn_Secure_Hash = $_GET["vpc_SecureHash"];
        unset($_GET["vpc_SecureHash"]);
        $errorExists = false;
        ksort($_GET);

        $inputData = $_GET;

        if (strlen($SECURE_SECRET) > 0 && $_GET["vpc_TxnResponseCode"] != "7" && $_GET["vpc_TxnResponseCode"] != "No Value Returned") {

            //$stringHashData = $SECURE_SECRET;
            //*****************************khởi tạo chuỗi mã hóa rỗng*****************************
            $stringHashData = "";

            // sort all the incoming vpc response fields and leave out any with no value
            foreach ($_GET as $key => $value) {
                //        if ($key != "vpc_SecureHash" or strlen($value) > 0) {
                //            $stringHashData .= $value;
                //        }
                //      *****************************chỉ lấy các tham số bắt đầu bằng "vpc_" hoặc "user_" và khác trống và không phải chuỗi hash code trả về*****************************
                if ($key != "vpc_SecureHash" && (strlen($value) > 0) && ((substr($key, 0, 4) == "vpc_") || (substr($key, 0, 5) == "user_"))) {
                    $stringHashData .= $key . "=" . $value . "&";
                }
            }
            //  *****************************Xóa dấu & thừa cuối chuỗi dữ liệu*****************************
            $stringHashData = rtrim($stringHashData, "&");


            //    if (strtoupper ( $vpc_Txn_Secure_Hash ) == strtoupper ( md5 ( $stringHashData ) )) {
            //    *****************************Thay hàm tạo chuỗi mã hóa*****************************
            if (strtoupper($vpc_Txn_Secure_Hash) == strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*', $SECURE_SECRET)))) {
                // Secure Hash validation succeeded, add a data field to be displayed
                // later.
                $hashValidated = "CORRECT";
            } else {
                // Secure Hash validation failed, add a data field to be displayed
                // later.
                $hashValidated = "INVALID HASH";
            }
        } else {
            // Secure Hash was not validated, add a data field to be displayed later.
            $hashValidated = "INVALID HASH";
        }


        if ($hashValidated  == "CORRECT" && $_GET["vpc_TxnResponseCode"] == "0") {

            $historyExists = StatusPaymentHistory::where(
                'order_code',
                $inputData['vpc_OrderInfo']
            )->orderBy('id', 'desc')->first();

            $orderExists = Order::where(
                'order_code',
                $inputData['vpc_OrderInfo']
            )->first();

            if (empty($historyExists)) {
                StatusPaymentHistory::create(
                    [
                        "order_code" => $inputData['vpc_OrderInfo'],
                        "transaction_no" => $inputData['vpc_TransactionNo'],
                        "amount" => ($inputData['vpc_Amount'] != null && $inputData['vpc_Amount'] > 0) ? $inputData['vpc_Amount'] / 100 : 0,
                        "bank_code" =>  "",
                        "card_type" =>  $inputData['vpc_Card'] ?? "",
                        "order_info" => $inputData['vpc_OrderInfo'],
                        "pay_date" => Helper::getTimeNowString(),
                        "response_code" => $inputData['vpc_TxnResponseCode'] ?? "",
                        "key_code_customer" => "",
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
                            "Đã thanh toán đơn hàng qua OnePay",
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


            if ($_GET['vpc_TxnResponseCode'] == '0') {


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


                return response()->view(
                    'success_paid',
                    [
                        'link_back' =>  $link_back
                    ]
                );
            } else {


                return response()->view(
                    'error_pay',
                    [
                        'link_back' =>  null
                    ]
                );
                echo "Thanh toán không thành công xin thử lại";
            }
        } else {
            return response()->view(
                'error_pay',
                [
                    'link_back' =>  null
                ]
            );
            echo "Giao dịch không thành công";
        }
    }
}
