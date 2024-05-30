<?php

namespace App\Traits;

use App\Helper\PaymentUtils;
use Illuminate\Http\Request;

trait VNPay
{

    public function pay(Request $request)
    {

        $store_code = $request->store->store_code;

        session(['cost_id' => $request->id]);
        session(['url_prev' => url()->previous()]);

        $env =  env("APP_ENV", "local");

        $vnp_TmnCode =  $env == "local" ? PaymentUtils::vnp_Url_dev : PaymentUtils::vnp_Url_main; //Mã website tại VNPAY 
        $vnp_HashSecret = $env == "local" ? PaymentUtils::vnp_HashSecret_dev : PaymentUtils::vnp_HashSecret_main;  //Chuỗi bí mật
        $vnp_Url = $env == "local" ? PaymentUtils::vnp_Url_dev : PaymentUtils::vnp_Url_main;


        $vnp_Returnurl = "http://ashop.sahavi.vn/api/api/customer/sy/purchase/return/vn_pay";
        $vnp_TxnRef = "X1"; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo = "Thanh toán hóa đơn phí dich vụ";
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = 100001 * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.0.0",
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
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
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



        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            // $vnpSecureHash = md5($vnp_HashSecret . $hashdata);
            $vnpSecureHash = hash('sha256', $vnp_HashSecret . $hashdata);
            $vnp_Url .= 'vnp_SecureHashType=SHA256&vnp_SecureHash=' . $vnpSecureHash;
        }



        return redirect($vnp_Url);
    }
}
