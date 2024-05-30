<?php

use App\Helper\StatusDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\PushNotificationUserJob;
use App\Models\HistoryPayOrder;
use App\Models\LinkBackPay;
use App\Models\Order;
use App\Models\StatusPaymentHistory;
use App\Models\WebhookHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::fallback(function () {
    return response()->json([
        'code' => 404,
        'success' => false,
        'msg_code' => "NOT_FOUND",
        'msg' => "Trang không tồn tại",
    ], 404);
});



Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Ghi log lỗi
Route::post('logger_fail', 'App\Http\Controllers\Api\LoggerFailController@log');

//Chạy mỗi phút một lần
Route::get('run_every_minute', 'App\Http\Controllers\Api\EveryMinuteController@runEveryMinute');

function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        )
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //execute post
    $result = curl_exec($ch);
    //close connection
    curl_close($ch);
    return $result;
}

// Route::get('/return/momo', function () {
//     header('Content-type: text/html; charset=utf-8');


//     $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'; //Put your secret key in there

//     if (!empty($_GET)) {
//         $accessKey = "klm05TvNBzhg7h7j";
//         $partnerCode = "MOMOBKUN20180529";
//         $orderId = $_GET["orderId"];
//         $localMessage = $_GET["message"];
//         $message = $_GET["message"];
//         $transId = $_GET["transId"];
//         $orderInfo = $_GET["orderInfo"];
//         $amount = $_GET["amount"];
//         // $errorCode = $_GET["resultCode"];
//         $responseTime = $_GET["responseTime"];
//         $requestId = $_GET["requestId"];
//         $extraData = $_GET["extraData"];
//         $payType = $_GET["payType"];
//         $orderType = $_GET["orderType"];
//         $extraData = $_GET["extraData"];
//         $m2signature = $_GET["signature"]; //MoMo signature


//         //Checksum
//         $rawHash = 'accessKey=' . $accessKey;
//         $rawHash .= '&amount=' . $amount;
//         $rawHash .= '&extraData=' . $extraData;
//         $rawHash .= '&message=' . $message;
//         $rawHash .= '&orderId=' . $orderId;
//         $rawHash .= '&orderInfo=' . $orderInfo;
//         $rawHash .= '&orderType=' . $orderType;
//         $rawHash .= '&partnerCode=' . $partnerCode;
//         $rawHash .= '&payType=' . $payType;
//         $rawHash .= '&requestId=' . $requestId;
//         $rawHash .= '&responseTime=' . $responseTime;
//         $rawHash .= '&resultCode=' . $_GET['resultCode'];
//         $rawHash .= '&transId=' . $transId;

//         $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);
//         // dd($partnerSignature, $m2signature);

//         echo "<script>console.log('Debug huhu Objects: " . $rawHash . "' );</script>";
//         echo "<script>console.log('Debug huhu Objects: " . $secretKey . "' );</script>";
//         echo "<script>console.log('Debug huhu Objects: " . $partnerSignature . "' );</script>";
//         echo "<script>console.log('Debug huhu Objects11: " . $m2signature . "' );</script>";


//         if ($m2signature == $partnerSignature) {
//             if ($_GET['resultCode'] == '0') {
//                 $result = '<div class="alert alert-success"><strong>Payment status: </strong>Success</div>';
//             } else {
//                 $result = '<div class="alert alert-danger"><strong>Payment status: </strong>' . $message . '/' . $localMessage . '</div>';
//             }
//             return $result;
//         } else {
//             $result = '<div class="alert alert-danger">This transaction could be hacked, please check your signature and returned signature</div>';
//             return $result;
//         }
//     }
// })->name('return/momo');

Route::get('/return/ipn', function () {
    header("content-type: application/json; charset=UTF-8");
    http_response_code(200); //200 - Everything will be 200 Oke
    if (!empty($_POST)) {
        $response = array();
        try {
            $partnerCode = "MOMOBKUN20180529";
            $accessKey = "klm05TvNBzhg7h7j";
            $serectkey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
            $orderId = $_POST["orderId"];
            $message = $_POST["message"];
            $message = $_POST["message"];
            $transId = $_POST["transId"];
            $orderInfo = $_POST["orderInfo"];
            $amount = $_POST["amount"];
            $errorCode = $_POST["errorCode"];
            $responseTime = $_POST["responseTime"];
            $requestId = $_POST["requestId"];
            $extraData = $_POST["extraData"];
            $payType = $_POST["payType"];
            $orderType = $_POST["orderType"];
            $extraData = $_POST["extraData"];
            $m2signature = $_POST["signature"]; //MoMo signature


            //Checksum
            $rawHash = 'accessKey=' . $accessKey;
            $rawHash .= '&amount=' . $amount;
            $rawHash .= '&extraData=' . $extraData;
            $rawHash .= '&message=' . $message;
            $rawHash .= '&orderId=' . $orderId;
            $rawHash .= '&orderInfo=' . $orderInfo;
            $rawHash .= '&orderType=' . $orderType;
            $rawHash .= '&partnerCode=' . $partnerCode;
            $rawHash .= '&payType=' . $payType;
            $rawHash .= '&requestId=' . $requestId;
            $rawHash .= '&responseTime=' . $responseTime;
            $rawHash .= '&resultCode=' . $_POST['resultCode'];
            $rawHash .= '&transId=' . $transId;

            $partnerSignature = hash_hmac("sha256", $rawHash, $serectkey);

            if ($m2signature == $partnerSignature) {
                if ($errorCode == '0') {
                    $result = '<div class="alert alert-success">Capture Payment Success</div>';
                } else {
                    $result = '<div class="alert alert-danger">' . $message . '</div>';
                }
                return $result;
            } else {
                $result = '<div class="alert alert-danger">This transaction could be hacked, please check your signature and returned signature</div>';
                return $result;
            }
        } catch (Exception $e) {
            echo $response['message'] = $e;
        }

        $debugger = array();
        $debugger['rawData'] = $rawHash;
        $debugger['momoSignature'] = $m2signature;
        $debugger['partnerSignature'] = $partnerSignature;

        if ($m2signature == $partnerSignature) {
            $response['message'] = "Received payment result success";
        } else {
            $response['message'] = "ERROR! Fail checksum";
        }
        $response['debugger'] = $debugger;
        echo json_encode($response);
    }
})->name('return/ipn');

Route::post('/test-momo', function (Request $request) {
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

    $partnerCode = "MOMOBKUN20180529";
    $accessKey = "klm05TvNBzhg7h7j";
    $secretKey = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";
    $orderInfo = "Thanh toán qua MoMo";
    $amount = "10000";
    // $ipnUrl = $_POST["ipnUrl"];
    $orderId = time() . "";
    $redirectUrl = route("return/momo");
    $ipnUrl = route("return/ipn");
    // Lưu ý: link notifyUrl không phải là dạng localhost
    $bankCode = "SML";

    // $partnerCode = $_POST["partnerCode"];
    // $accessKey = $_POST["accessKey"];
    // $serectkey = $_POST["secretKey"];
    $orderid = time() . "";
    // $orderInfo = $_POST["orderInfo"];
    // $amount = $_POST["amount"];
    // $bankCode = $_POST['bankCode'];
    // $returnUrl = $_POST['returnUrl'];
    $requestId = time() . "";
    $requestType = "payWithATM";
    $extraData = "";
    //before sign HMAC SHA256 signature
    $rawHashArr =  array(
        'partnerCode' => $partnerCode,
        'accessKey' => $accessKey,
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderid,
        'orderInfo' => $orderInfo,
        'bankCode' => $bankCode,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'extraData' => $extraData,
        'requestType' => $requestType
    );
    // echo $serectkey;die;
    $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl="
        . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl="
        . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;

    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    $data =  array(
        'partnerCode' => $partnerCode,
        'accessKey' => $accessKey,
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderid,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'bankCode' => $bankCode,
        'extraData' => $extraData,
        'requestType' => $requestType,
        'signature' => $signature
    );
    $result = execPostRequest($endpoint, json_encode($data));
    $jsonResult = json_decode($result, true);  // decode json
    dd($jsonResult);

    error_log(print_r($jsonResult, true));
    header('Location: ' . $jsonResult['payUrl']);
});

Route::middleware(['timezone', 'record_access', 'up_speed'])
    ->prefix('/')->group(function () {

        ///////////////////// API OTHER /////////////////////
        //Đăng ký   
        Route::post('register', 'App\Http\Controllers\Api\User\RegisterController@register');
        //Đăng nhập
        Route::post('login', 'App\Http\Controllers\Api\User\LoginController@login');

        //Place
        Route::prefix('place')->group(function () {
            //App-Theme
            Route::get('/vn/{type}/{parent_id}', 'App\Http\Controllers\Api\User\PlaceController@getWithType');
            Route::get('/vn/{type}', 'App\Http\Controllers\Api\User\PlaceController@getWithType');
        });

        //Handle Receiver Sms 
        Route::get('handle_receiver_sms', 'App\Http\Controllers\Api\HandleReceiverSmsController@handle');
        //send email otp
        Route::post('send_email_otp', 'App\Http\Controllers\Api\SendMailController@send_email_otp');

        //Lấy lại mật khẩu
        Route::post('reset_password', 'App\Http\Controllers\Api\User\LoginController@reset_password');
        //Thay đổi mật khẩu
        Route::post('change_password', 'App\Http\Controllers\Api\User\LoginController@change_password')->middleware('user_auth');
        //Kiểm tra tồn tại


        Route::post('login/check_exists', 'App\Http\Controllers\Api\User\LoginController@check_exists');

        //Gui otp
        Route::post('send_otp', 'App\Http\Controllers\Api\User\OtpSmsController@send')->middleware('get_customer_store');

        //Webhook
        Route::post('webhook/ship', 'App\Http\Controllers\Api\User\Webhook\WebhookShipController@create');
        Route::get('webhook/ship', 'App\Http\Controllers\Api\User\Webhook\WebhookShipController@create');
        Route::put('webhook/ship', 'App\Http\Controllers\Api\User\Webhook\WebhookShipController@create');

        //Webhook vietnam post
        Route::post('webhook/vietnam_post', 'App\Http\Controllers\Api\User\Webhook\WebhookShipController@orderVietnamPost');
        Route::get('webhook/vietnam_post', 'App\Http\Controllers\Api\User\Webhook\WebhookShipController@orderVietnamPost');
        Route::put('webhook/vietnam_post', 'App\Http\Controllers\Api\User\Webhook\WebhookShipController@orderVietnamPost');
        Route::post('webhook/cancel_vietnam_post', 'App\Http\Controllers\Api\User\Webhook\WebhookShipController@orderVietnamPost');

        //Webhook nhattin post
        Route::post('webhook/nhattin_post', 'App\Http\Controllers\Api\User\Webhook\WebhookShipController@orderNhattinPost');
        Route::get('webhook/nhattin_post', 'App\Http\Controllers\Api\User\Webhook\WebhookShipController@orderNhattinPost');
        Route::put('webhook/nhattin_post', 'App\Http\Controllers\Api\User\Webhook\WebhookShipController@orderNhattinPost');

        //TypeOfStore
        Route::get('type_of_store', 'App\Http\Controllers\Api\User\TypeOfStoreController@getAll');

        //data home
        Route::get('/store/home_app', 'App\Http\Controllers\Api\User\HomeDataController@getHomeApp');



        //User vip
        Route::post('/vip_user/config', 'App\Http\Controllers\Api\User\VipUserController@config_user_vip')->middleware('user_auth');
        Route::get('/vip_user/config', 'App\Http\Controllers\Api\User\VipUserController@get_config_user_vip')->middleware('user_auth');


        //Store user
        Route::prefix('store_v2')->group(function () {
            //Thêm sản phẩm
            Route::post('/{store_code}/{branch_id}/products', 'App\Http\Controllers\Api\User\ProductController@create')->middleware('user_auth', 'has_store', 'has_branch');

            //Cập nhật hoa hồng cho tất cả sp
            Route::post('/{store_code}/collaborator_products', 'App\Http\Controllers\Api\User\ProductController@updateAllPercentCollaboratorProduct')->middleware('user_auth', 'has_store');


            //Thêm nhiều sản phẩm
            Route::post('/{store_code}/{branch_id}/products/all', 'App\Http\Controllers\Api\User\ProductController@createManyProduct')->middleware('user_auth', 'has_store', 'has_branch');
            //Lấy tất cả sản phẩm
            Route::get('/{store_code}/{branch_id}/products', 'App\Http\Controllers\Api\User\ProductController@getAll')->middleware('user_auth', 'has_store', 'has_branch');
            //Thêm nhiều giá vốn, tồn kho của sản phẩm
            Route::post('/{store_code}/{branch_id}/products_inventory/all', 'App\Http\Controllers\Api\User\ProductController@createManyProductInventory')->middleware('user_auth', 'has_store', 'has_branch');
            //Cập nhật 1 sản phẩm
            Route::put('/{store_code}/{branch_id}/products/{product_id}', 'App\Http\Controllers\Api\User\ProductController@updateOneProduct')->middleware('user_auth', 'has_store', 'has_branch', 'has_product');


            //Lấy 1 sản phẩm
            Route::get('/{store_code}/{branch_id}/products/{product_id}', 'App\Http\Controllers\Api\User\ProductController@getOneProduct')->middleware('user_auth', 'has_store', 'has_branch', 'has_product');

            //Xem khoảng giá theo sản phẩm 
            Route::get('/{store_code}/{branch_id}/product_retail_steps/{product_id}', 'App\Http\Controllers\Api\User\ProductController@getProductRetailStep')->middleware('user_auth', 'has_store', 'has_branch');


            //Danh sách Badges chi so user
            Route::get('/{store_code}/{branch_id}/badges', 'App\Http\Controllers\Api\User\BadgesController@getBadges')->middleware('user_auth', 'has_store', 'has_branch');
            Route::get('/{store_code}/{branch_id}/badges/v1', 'App\Http\Controllers\Api\User\BadgesController@getBadgesV1')->middleware('user_auth', 'has_store', 'has_branch');
            //Số lượng sản phẩm sắp hết hàng
            Route::get('/{store_code}/{branch_id}/amount_product_nearly_out_stock', 'App\Http\Controllers\Api\User\BadgesController@getAmountProductNearlyOutStock')->middleware('user_auth', 'has_store', 'has_branch');


            //Thanh toán đơn hàng
            Route::post('/{store_code}/{branch_id}/orders/pay_order/{order_code}', 'App\Http\Controllers\Api\User\OrderController@pay_order')->middleware('user_auth', 'has_store', 'has_branch');

            //POS Hoàn tiền trả hàng
            Route::post('/{store_code}/{branch_id}/pos/refund', 'App\Http\Controllers\Api\User\PosController@refund')->middleware('user_auth', 'has_store', 'has_branch');
            //Tính tiền hoàn
            Route::post('/{store_code}/{branch_id}/pos/refund/calculate', 'App\Http\Controllers\Api\User\PosController@calculate_money_refund')->middleware('user_auth', 'has_store', 'has_branch');

            //Thông tin distribute 1 sp
            Route::get('/{store_code}/{branch_id}/products/{product_id}/distribute', 'App\Http\Controllers\Api\User\DistributeController@get_distribute_product')->middleware('user_auth', 'has_store', 'has_branch');

            Route::put('/{store_code}/{branch_id}/products/{product_id}/distribute', 'App\Http\Controllers\Api\User\DistributeController@updateDistribute')->middleware('user_auth', 'has_store', 'has_branch');


            //Báo cáo doanh thu tổng quan
            Route::get('/{store_code}/{branch_id}/report/overview', 'App\Http\Controllers\Api\User\ReportController@overview')->middleware('user_auth', 'has_store', 'has_branch');
            Route::get('/{store_code}/{branch_id}/report/overview/v1', 'App\Http\Controllers\Api\User\ReportController@overviewV1')->middleware('user_auth', 'has_store', 'has_branch');

            //Báo cáo top 10 sản phẩm
            Route::get('/{store_code}/{branch_id}/report/top_ten_products', 'App\Http\Controllers\Api\User\ReportController@top_ten_products')->middleware('user_auth', 'has_store', 'has_branch');

            //Thêm 1 ca
            Route::post('/{store_code}/{branch_id}/shifts', 'App\Http\Controllers\Api\User\ShiftController@create')->middleware('user_auth', 'has_store', 'has_branch');
            //Lấy tất cả ca
            Route::get('/{store_code}/{branch_id}/shifts', 'App\Http\Controllers\Api\User\ShiftController@getAll')->middleware('user_auth', 'has_store', 'has_branch');
            //Cập nhật 1 ca
            Route::put('/{store_code}/{branch_id}/shifts/{shift_id}', 'App\Http\Controllers\Api\User\ShiftController@update')->middleware('user_auth', 'has_store', 'has_branch');
            //Lấy 1 ca
            Route::get('/{store_code}/{branch_id}/shifts/{shift_id}', 'App\Http\Controllers\Api\User\ShiftController@getOne')->middleware('user_auth', 'has_store', 'has_branch');
            //Xóa 1 ca
            Route::delete('/{store_code}/{branch_id}/shifts/{shift_id}', 'App\Http\Controllers\Api\User\ShiftController@delete')->middleware('user_auth', 'has_store', 'has_branch');

            //Danh sách lịch làm việc
            Route::get('/{store_code}/{branch_id}/calendar_shifts', 'App\Http\Controllers\Api\User\CalendarShiftController@getAll')->middleware('user_auth', 'has_store', 'has_branch');
            //Xếp nhiều
            Route::post('/{store_code}/{branch_id}/calendar_shifts/put_a_lot', 'App\Http\Controllers\Api\User\CalendarShiftController@put_a_lot')->middleware('user_auth', 'has_store', 'has_branch');
            //Xếp vào 1 ô
            Route::post('/{store_code}/{branch_id}/calendar_shifts/put_one', 'App\Http\Controllers\Api\User\CalendarShiftController@put_one')->middleware('user_auth', 'has_store', 'has_branch');

            //Thông tin chấm công hôm nay
            Route::get('/{store_code}/{branch_id}/timekeeping/to_day', 'App\Http\Controllers\Api\User\DateTimekeepingController@get_to_day')->middleware('user_auth', 'has_store', 'has_branch', 'check_staff');

            //Checkin checkout 
            Route::post('/{store_code}/{branch_id}/timekeeping/checkin_checkout', 'App\Http\Controllers\Api\User\DateTimekeepingController@checkin_checkout')->middleware('user_auth', 'has_store', 'has_branch', 'check_staff');


            //Tính toán thời gian công
            Route::get('/{store_code}/{branch_id}/timekeeping/calculate', 'App\Http\Controllers\Api\User\CalculateTimekeepingController@getTimeKeeping')->middleware('user_auth', 'has_store', 'has_branch');


            //Thêm 1 vị trí checkin 
            Route::post('/{store_code}/{branch_id}/checkin_location', 'App\Http\Controllers\Api\User\CheckinLocationController@create')->middleware('user_auth', 'has_store', 'has_branch');
            //Cập nhật 1 vị trí checkin 
            Route::put('/{store_code}/{branch_id}/checkin_location/{checkin_location_id}', 'App\Http\Controllers\Api\User\CheckinLocationController@update')->middleware('user_auth', 'has_store', 'has_branch');
            //Xóa 1 vị trí checkin
            Route::delete('/{store_code}/{branch_id}/checkin_location/{checkin_location_id}', 'App\Http\Controllers\Api\User\CheckinLocationController@delete')->middleware('user_auth', 'has_store', 'has_branch');
            //Danh sách vị trí checkin
            Route::get('/{store_code}/{branch_id}/checkin_location', 'App\Http\Controllers\Api\User\CheckinLocationController@getAll')->middleware('user_auth', 'has_store', 'has_branch');

            //Thêm bớt công
            Route::post('/{store_code}/{branch_id}/bonus_less_checkin_checkout', 'App\Http\Controllers\Api\User\CheckTimeKeepingController@bonus_less_checkin_checkout')->middleware('user_auth', 'has_store', 'has_branch');


            //Danh sách checkin checkout cần xử lý
            Route::get('/{store_code}/{branch_id}/await_checkin_checkouts', 'App\Http\Controllers\Api\User\CheckTimeKeepingController@getAllAwaitCheckinCheckout')->middleware('user_auth', 'has_store', 'has_branch');
            //Thay đổi trạng thái chấm công
            Route::post('/{store_code}/{branch_id}/await_checkin_checkouts/{date_timekeeping_history_id}/change_status', 'App\Http\Controllers\Api\User\CheckTimeKeepingController@changeStatus')->middleware('user_auth', 'has_store', 'has_branch');
            //Danh sách mobile cần xử lý
            Route::get('/{store_code}/{branch_id}/await_mobile_checkins', 'App\Http\Controllers\Api\User\CheckTimeKeepingController@getAllAwaitMobile')->middleware('user_auth', 'has_store', 'has_branch');
            //Thay đổi trạng thái mobile
            Route::post('/{store_code}/{branch_id}/await_mobile_checkins/{mobile_id}/change_status', 'App\Http\Controllers\Api\User\CheckTimeKeepingController@changeStatusMobileCheckin')->middleware('user_auth', 'has_store', 'has_branch');


            //Danh sách điện thoại chấm công của nv
            Route::get('/{store_code}/{branch_id}/mobile_checkin/staff/{staff_id}', 'App\Http\Controllers\Api\User\CheckTimeKeepingController@getAllMobileOfStaff')->middleware('user_auth', 'has_store', 'has_branch');


            //Danh sách điện thoại chấm công
            Route::get('/{store_code}/{branch_id}/mobile_checkin', 'App\Http\Controllers\Api\User\MobileCheckinController@getAll')->middleware('user_auth', 'has_store', 'has_branch', 'check_staff');
            //Thêm điện thoại chấm công
            Route::post('/{store_code}/{branch_id}/mobile_checkin', 'App\Http\Controllers\Api\User\MobileCheckinController@create')->middleware('user_auth', 'has_store', 'has_branch', 'check_staff');
            //Sửa điện thoại chấm công
            Route::put('/{store_code}/{branch_id}/mobile_checkin/{mobile_id}', 'App\Http\Controllers\Api\User\MobileCheckinController@update')->middleware('user_auth', 'has_store', 'has_branch', 'check_staff');
            //xóa điện thoại chấm công
            Route::delete('/{store_code}/{branch_id}/mobile_checkin/{mobile_id}', 'App\Http\Controllers\Api\User\MobileCheckinController@delete')->middleware('user_auth', 'has_store', 'has_branch');

            //Danh sách thông báo đẩy
            Route::get('/{store_code}/{branch_id}/notifications_history', 'App\Http\Controllers\Api\User\NotificationController@getAll')->middleware('user_auth', 'has_store', 'has_branch');
            //đọc hết
            Route::get('/{store_code}/{branch_id}/notifications_history/read_all', 'App\Http\Controllers\Api\\User\NotificationController@readAll')->middleware('user_auth', 'has_store', 'has_branch');
        });

        //Store user
        Route::prefix('store_v3')->group(function () {

            //Lấy tất cả sản phẩm
            Route::get('/{store_code}/products', 'App\Http\Controllers\Api\User\ProductController@getAll')->middleware('user_auth', 'has_store', 'has_branch');

            //Lấy 1 sản phẩm
            Route::get('/{store_code}/products/{product_id}', 'App\Http\Controllers\Api\User\ProductController@getOneProduct')->middleware('user_auth', 'has_store', 'has_branch', 'has_product');

            //Danh sách Badges chi so user
            Route::get('/{store_code}/badges', 'App\Http\Controllers\Api\User\BadgesController@getBadges')->middleware('user_auth', 'has_store', 'has_branch');

            //Thông tin distribute 1 sp
            Route::get('/{store_code}/products/{product_id}/distribute', 'App\Http\Controllers\Api\User\DistributeController@get_distribute_product')->middleware('user_auth', 'has_store', 'has_branch');

            //Báo cáo doanh thu tổng quan
            Route::get('/{store_code}/report/overview', 'App\Http\Controllers\Api\User\ReportController@overview')->middleware('user_auth', 'has_store', 'has_branch');

            //Báo cáo top 10 sản phẩm
            Route::get('/{store_code}/report/top_ten_products', 'App\Http\Controllers\Api\User\ReportController@top_ten_products')->middleware('user_auth', 'has_store', 'has_branch');
        });

        //Store user
        Route::prefix('store')->group(function () {

            Route::get('/new_data_example', 'App\Http\Controllers\Api\User\StoreController@new_data_example');
            Route::post('/', 'App\Http\Controllers\Api\User\StoreController@create')->middleware('user_auth');
            Route::get('/', 'App\Http\Controllers\Api\User\StoreController@getAll')->middleware('user_auth');
            Route::get('/{store_code}', 'App\Http\Controllers\Api\User\StoreController@getOneStore')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}', 'App\Http\Controllers\Api\User\StoreController@deleteOneStore')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}', 'App\Http\Controllers\Api\User\StoreController@updateOneStore')->middleware('user_auth', 'has_store');

            //Chi nhánh
            Route::post('/{store_code}/branches', 'App\Http\Controllers\Api\User\BranchController@create')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/branches', 'App\Http\Controllers\Api\User\BranchController@getAll')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/branches/{branch_id}', 'App\Http\Controllers\Api\User\BranchController@delete')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/branches/{branch_id}', 'App\Http\Controllers\Api\User\BranchController@update')->middleware('user_auth', 'has_store');

            //Nhà cung cấp
            Route::post('/{store_code}/suppliers', 'App\Http\Controllers\Api\User\SupplierController@create')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/suppliers/{supplier_id}', 'App\Http\Controllers\Api\User\SupplierController@getOne')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/suppliers', 'App\Http\Controllers\Api\User\SupplierController@getAll')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/suppliers/{supplier_id}', 'App\Http\Controllers\Api\User\SupplierController@delete')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/suppliers/{supplier_id}', 'App\Http\Controllers\Api\User\SupplierController@update')->middleware('user_auth', 'has_store');


            //Danh mục
            Route::post('/{store_code}/categories', 'App\Http\Controllers\Api\User\CategoryController@create')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/categories', 'App\Http\Controllers\Api\User\CategoryController@getAll')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/categories/{category_id}', 'App\Http\Controllers\Api\User\CategoryController@deleteOneCategory')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/categories/{category_id}', 'App\Http\Controllers\Api\User\CategoryController@updateOneCategory')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/category/sort', 'App\Http\Controllers\Api\User\CategoryController@sortCategory')->middleware('user_auth', 'has_store');

            //Danh mục con
            Route::post('/{store_code}/categories/{category_id}/category_children', 'App\Http\Controllers\Api\User\CategoryChildController@create')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/categories/{category_id}/category_children/{category_children_id}', 'App\Http\Controllers\Api\User\CategoryChildController@sortCategory')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/categories/{category_id}/category_children/{category_children_id}', 'App\Http\Controllers\Api\User\CategoryChildController@deleteOneCategory')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/categories/{category_id}/category_children/{category_children_id}', 'App\Http\Controllers\Api\User\CategoryChildController@updateOneCategory')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/category_children_sort', 'App\Http\Controllers\Api\User\CategoryChildController@sortCategory')->middleware('user_auth', 'has_store');

            //Thuộc tính tìm kiếm cha
            Route::post('/{store_code}/attribute_searches', 'App\Http\Controllers\Api\User\AttributeSearchController@create')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/attribute_searches', 'App\Http\Controllers\Api\User\AttributeSearchController@getAll')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/attribute_searches/{attribute_search_id}', 'App\Http\Controllers\Api\User\AttributeSearchController@deleteOne')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/attribute_searches/{attribute_search_id}', 'App\Http\Controllers\Api\User\AttributeSearchController@updateOne')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/attribute_search/sort', 'App\Http\Controllers\Api\User\AttributeSearchController@sort')->middleware('user_auth', 'has_store');

            //Thuộc tính tìm kiếm con
            Route::post('/{store_code}/attribute_searches/{attribute_search_id}/product_attribute_search_children', 'App\Http\Controllers\Api\User\AttributeSearchController@createChild')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/attribute_searches/{attribute_search_id}/product_attribute_search_children/{child_id}', 'App\Http\Controllers\Api\User\AttributeSearchController@sortChild')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/attribute_searches/{attribute_search_id}/product_attribute_search_children/{child_id}', 'App\Http\Controllers\Api\User\AttributeSearchController@deleteOneChild')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/attribute_searches/{attribute_search_id}/product_attribute_search_children/{child_id}', 'App\Http\Controllers\Api\User\AttributeSearchController@updateOneChild')->middleware('user_auth', 'has_store');


            //Lấy tất cả thuộc tính
            Route::get('/{store_code}/attribute_fields', 'App\Http\Controllers\Api\User\AttributeFieldController@getAll')->middleware('user_auth', 'has_store');
            //Update thuộc tính
            Route::put('/{store_code}/attribute_fields', 'App\Http\Controllers\Api\User\AttributeFieldController@updateAttributeField')->middleware('user_auth', 'has_store');
            //Cập nhật giá 1 sản phẩm
            Route::put('/{store_code}/products/{product_id}/price', 'App\Http\Controllers\Api\User\ProductController@updatePriceOneProduct')->middleware('user_auth', 'has_store', 'has_product');
            //Cập nhật tên 1 sản phẩm
            Route::put('/{store_code}/products/{product_id}/name', 'App\Http\Controllers\Api\User\ProductController@updateNameOneProduct')->middleware('user_auth', 'has_store', 'has_product');


            //Thêm sản phẩm
            Route::post('/{store_code}/products', 'App\Http\Controllers\Api\User\ProductController@create')->middleware('user_auth', 'has_store');
            //Thêm nhiều sản phẩm
            Route::post('/{store_code}/products/all', 'App\Http\Controllers\Api\User\ProductController@createManyProduct')->middleware('user_auth', 'has_store');
            //Lấy tất cả sản phẩm
            Route::get('/{store_code}/products', 'App\Http\Controllers\Api\User\ProductController@getAll')->middleware('user_auth', 'has_store');
            //Lấy 1 sản phẩm
            Route::get('/{store_code}/products/{product_id}', 'App\Http\Controllers\Api\User\ProductController@getOneProduct')->middleware('user_auth', 'has_store', 'has_product');
            //Xoa 1 sản phẩm
            Route::delete('/{store_code}/products/{product_id}', 'App\Http\Controllers\Api\User\ProductController@deleteOneProduct')->middleware('user_auth', 'has_store', 'has_product');
            //Xoa nhiều sản phẩm
            Route::delete('/{store_code}/products', 'App\Http\Controllers\Api\User\ProductController@deleteManyProduct')->middleware('user_auth', 'has_store');
            //Cập nhật 1 sản phẩm
            Route::put('/{store_code}/products/{product_id}', 'App\Http\Controllers\Api\User\ProductController@updateOneProduct')->middleware('user_auth', 'has_store', 'has_product');

            //Cập nhật thuộc tính tìm kiếm của 1 sản phẩm
            Route::put('/{store_code}/products/{product_id}/set_up_attribute_search', 'App\Http\Controllers\Api\User\ProductController@set_up_attribute_search')->middleware('user_auth', 'has_store', 'has_product');
            //Lấy thuộc tính tìm kiếm của 1 sản phẩm
            Route::get('/{store_code}/products/{product_id}/get_attribute_search', 'App\Http\Controllers\Api\User\ProductController@getAllChildSearchOfProduct')->middleware('user_auth', 'has_store', 'has_product');


            //Thông tin distribute 1 sp
            Route::get('/{store_code}/products/{product_id}/distribute', 'App\Http\Controllers\Api\User\DistributeController@get_distribute_product')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/products/{product_id}/distribute', 'App\Http\Controllers\Api\User\DistributeController@updateDistribute')->middleware('user_auth', 'has_store');



            //Cập nhật và cân bằng 1 kho
            Route::put('/{store_code}/{branch_id}/inventory/update_balance', 'App\Http\Controllers\Api\User\InventoryController@updateInventoryBalance')->middleware('user_auth', 'has_store', 'has_branch');
            //Cập nhật và cân bằng danh sách kho
            Route::put('/{store_code}/{branch_id}/inventory/update_balance/list', 'App\Http\Controllers\Api\User\InventoryController@updateListInventoryBalance')->middleware('user_auth', 'has_store', 'has_branch');
            //Lịch sử kho
            Route::post('/{store_code}/{branch_id}/inventory/history', 'App\Http\Controllers\Api\User\InventoryController@inventoryHistory')->middleware('user_auth', 'has_store', 'has_branch');
            //Lịch sử kho
            Route::post('/{store_code}/inventory/history', 'App\Http\Controllers\Api\User\InventoryController@inventoryHistory')->middleware('user_auth', 'has_store');


            //Danh sách phiếu thu chi
            Route::get('/{store_code}/{branch_id}/revenue_expenditures', 'App\Http\Controllers\Api\User\RevenueExpenditureController@getAll')->middleware('user_auth', 'has_store', 'has_branch');
            //Thêm phiếu thu chi
            Route::post('/{store_code}/{branch_id}/revenue_expenditures', 'App\Http\Controllers\Api\User\RevenueExpenditureController@create')->middleware('user_auth', 'has_store', 'has_branch');
            //Thông tin 1 phiếu thu chi
            Route::get('/{store_code}/{branch_id}/revenue_expenditures/{revenue_expenditure_id}', 'App\Http\Controllers\Api\User\RevenueExpenditureController@getOne')->middleware('user_auth', 'has_store', 'has_branch');




            //Tạo phiếu kiểm kho
            Route::post('/{store_code}/{branch_id}/inventory/tally_sheets', 'App\Http\Controllers\Api\User\TallySheetController@createTallySheet')->middleware('user_auth', 'has_store', 'has_branch');
            //Danh sách phiếu kiểm
            Route::get('/{store_code}/{branch_id}/inventory/tally_sheets', 'App\Http\Controllers\Api\User\TallySheetController@getAllTallySheet')->middleware('user_auth', 'has_store', 'has_branch');
            //Thông tin chi tiết 1 phiếu
            Route::get('/{store_code}/{branch_id}/inventory/tally_sheets/{tally_sheet_id}', 'App\Http\Controllers\Api\User\TallySheetController@getOneTallySheet')->middleware('user_auth', 'has_store', 'has_branch');
            //Thông tin chi tiết 1 phiếu
            Route::delete('/{store_code}/{branch_id}/inventory/tally_sheets/{tally_sheet_id}', 'App\Http\Controllers\Api\User\TallySheetController@deleteOneTallySheet')->middleware('user_auth', 'has_store', 'has_branch');
            //Cập nhật 1 phiếu
            Route::put('/{store_code}/{branch_id}/inventory/tally_sheets/{tally_sheet_id}', 'App\Http\Controllers\Api\User\TallySheetController@updateOneTallySheet')->middleware('user_auth', 'has_store', 'has_branch');
            //Cân bằng 1 phiếu
            Route::post('/{store_code}/{branch_id}/inventory/tally_sheets/{tally_sheet_id}/balance', 'App\Http\Controllers\Api\User\TallySheetController@balanceTallySheet')->middleware('user_auth', 'has_store', 'has_branch');



            //Tạo phiếu nhập hàng
            Route::post('/{store_code}/{branch_id}/inventory/import_stocks', 'App\Http\Controllers\Api\User\ImportStockController@create')->middleware('user_auth', 'has_store', 'has_branch');
            //Danh sách phiếu nhập hàng
            Route::get('/{store_code}/{branch_id}/inventory/import_stocks', 'App\Http\Controllers\Api\User\ImportStockController@getAll')->middleware('user_auth', 'has_store', 'has_branch');
            //Thông tin chi tiết 1 nhập hàng
            Route::get('/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}', 'App\Http\Controllers\Api\User\ImportStockController@getOne')->middleware('user_auth', 'has_store', 'has_branch');
            //In chi tiết 1 nhập hàng
            Route::get('/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}/print', 'App\Http\Controllers\Api\User\ImportStockController@printImportStock');
            //Cập nhật 1 nhập hàng
            Route::put('/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}', 'App\Http\Controllers\Api\User\ImportStockController@updateOne')->middleware('user_auth', 'has_store', 'has_branch');
            //Cập nhật trạng thái 1 đơn nhập
            Route::put('/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}/status', 'App\Http\Controllers\Api\User\ImportStockController@updateStatusImportStock')->middleware('user_auth', 'has_store', 'has_branch');
            //Thanh toan
            Route::put('/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}/payment', 'App\Http\Controllers\Api\User\ImportStockController@updatePayImportStock')->middleware('user_auth', 'has_store', 'has_branch');
            //Hoàn trả
            Route::post('/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}/refund', 'App\Http\Controllers\Api\User\ImportStockController@refund')->middleware('user_auth', 'has_store', 'has_branch');

            //Tạo phiếu chuyển hàng
            Route::post('/{store_code}/{branch_id}/inventory/transfer_stocks', 'App\Http\Controllers\Api\User\TransferStockController@create')->middleware('user_auth', 'has_store', 'has_branch');
            //Danh sách phiếu chuyển hàng
            Route::get('/{store_code}/{branch_id}/inventory/transfer_stocks/sender', 'App\Http\Controllers\Api\User\TransferStockController@getAllSender')->middleware('user_auth', 'has_store', 'has_branch');
            Route::get('/{store_code}/{branch_id}/inventory/transfer_stocks/receiver', 'App\Http\Controllers\Api\User\TransferStockController@getAllReceiver')->middleware('user_auth', 'has_store', 'has_branch');

            //Thông tin chi tiết 1 chuyển hàng
            Route::get('/{store_code}/{branch_id}/inventory/transfer_stocks/{transfer_stock_id}', 'App\Http\Controllers\Api\User\TransferStockController@getOne')->middleware('user_auth', 'has_store', 'has_branch');
            //Cập nhật 1 chuyển hàng
            Route::put('/{store_code}/{branch_id}/inventory/transfer_stocks/{transfer_stock_id}', 'App\Http\Controllers\Api\User\TransferStockController@updateOne')->middleware('user_auth', 'has_store', 'has_branch');
            //Cập nhật trạng thái 1 đơn chuyển
            Route::put('/{store_code}/{branch_id}/inventory/transfer_stocks/{transfer_stock_id}/status', 'App\Http\Controllers\Api\User\TransferStockController@updateStatus')->middleware('user_auth', 'has_store', 'has_branch');


            //Cập nhật giá cho đại lý
            Route::put('/{store_code}/products/{product_id}/agency_price', 'App\Http\Controllers\Api\User\ProductAgencyController@updatePriceAgency')->middleware('user_auth', 'has_store', 'has_product');

            //Cập nhật giá nhiều sản phẩm cho đại lý
            Route::put('/{store_code}/products/agency_price/list', 'App\Http\Controllers\Api\User\ProductAgencyController@updateListPriceAgency')->middleware('user_auth', 'has_store');
            //Lấy giá cho đại lý
            Route::get('/{store_code}/products/{product_id}/agency_price', 'App\Http\Controllers\Api\User\ProductAgencyController@getPriceAgency')->middleware('user_auth', 'has_store', 'has_product');


            //Phân quyền
            //Danh sách phân quyền
            Route::post('/{store_code}/decentralizations', 'App\Http\Controllers\Api\User\DecentralizationController@create')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/decentralizations', 'App\Http\Controllers\Api\User\DecentralizationController@getAll')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/decentralizations/{decentralization_id}', 'App\Http\Controllers\Api\User\DecentralizationController@delete')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/decentralizations/{decentralization_id}', 'App\Http\Controllers\Api\User\DecentralizationController@update')->middleware('user_auth', 'has_store');

            //Nhân viên
            //Danh sách nhân viên
            Route::post('/{store_code}/staffs', 'App\Http\Controllers\Api\User\StaffController@create')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/staffs', 'App\Http\Controllers\Api\User\StaffController@getAll')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/staffs/{staff_id}', 'App\Http\Controllers\Api\User\StaffController@delete')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/staffs/{staff_id}', 'App\Http\Controllers\Api\User\StaffController@update')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/staffs/{staff_id}/update_sale', 'App\Http\Controllers\Api\User\StaffController@updateSale')->middleware('user_auth', 'has_store');

            //Bài viết
            //Danh mục bài viết
            Route::post('/{store_code}/post_categories', 'App\Http\Controllers\Api\User\CategoryPostController@create')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/post_categories', 'App\Http\Controllers\Api\User\CategoryPostController@getAll')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/post_categories/{category_id}', 'App\Http\Controllers\Api\User\CategoryPostController@deleteOneCategory')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/post_categories/{category_id}', 'App\Http\Controllers\Api\User\CategoryPostController@updateOneCategory')->middleware('user_auth', 'has_store');

            Route::post('/{store_code}/post_categories/sort', 'App\Http\Controllers\Api\User\CategoryController@sortCategory')->middleware('user_auth', 'has_store');

            //Danh mục con
            Route::post('/{store_code}/post_categories/{category_id}/category_children', 'App\Http\Controllers\Api\User\PostCategoryChildController@create')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/post_categories/{category_id}/category_children/{category_children_id}', 'App\Http\Controllers\Api\User\PostCategoryChildController@sortCategory')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/post_categories/{category_id}/category_children/{category_children_id}', 'App\Http\Controllers\Api\User\PostCategoryChildController@deleteOneCategory')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/post_categories/{category_id}/category_children/{category_children_id}', 'App\Http\Controllers\Api\User\PostCategoryChildController@updateOneCategory')->middleware('user_auth', 'has_store');


            //Thêm bài viết
            Route::post('/{store_code}/posts', 'App\Http\Controllers\Api\User\PostController@create')->middleware('user_auth', 'has_store');
            //Lấy tất cả bài viết
            Route::get('/{store_code}/posts', 'App\Http\Controllers\Api\User\PostController@getAll')->middleware('user_auth', 'has_store');

            //Lấy 1 bài viết
            Route::get('/{store_code}/posts/{post_id}', 'App\Http\Controllers\Api\User\PostController@getOnePost')->middleware('user_auth', 'has_store');

            //Xóa 1 bài viết
            Route::delete('/{store_code}/posts/{post_id}', 'App\Http\Controllers\Api\User\PostController@deleteOnePost')->middleware('user_auth', 'has_store');

            //Update 1 bài viết
            Route::post('/{store_code}/posts/{post_id}', 'App\Http\Controllers\Api\User\PostController@updateOnePost')->middleware('user_auth', 'has_store');


            //Thương mại điện tử
            //Lấy sản phẩm
            Route::post('/{store_code}/ecommerce/products', 'App\Http\Controllers\Api\User\EcommerceLeech\ProductController@get_product')->middleware('user_auth', 'has_store');


            //Khuyến mãi
            //Tạo giảm giá sản phẩm
            Route::post('/{store_code}/discounts', 'App\Http\Controllers\Api\User\DiscountController@create')->middleware('user_auth', 'has_store');
            //Update giá sản phẩm
            Route::put('/{store_code}/discounts/{discount_id}', 'App\Http\Controllers\Api\User\DiscountController@updateOneDiscount')->middleware('user_auth', 'has_store');
            //Xem 1 chương trình giá sản phẩm
            Route::get('/{store_code}/discounts/{discount_id}', 'App\Http\Controllers\Api\User\DiscountController@getOneDiscount')->middleware('user_auth', 'has_store');
            //Xem tất cả chương trình giá sản phẩm chuẩn bị và đang phát hành
            Route::get('/{store_code}/discounts', 'App\Http\Controllers\Api\User\DiscountController@getAll')->middleware('user_auth', 'has_store');
            //Xem tất cả chương trình giá sản phẩm kết thúc
            Route::get('/{store_code}/discounts_end', 'App\Http\Controllers\Api\User\DiscountController@getAllEnd')->middleware('user_auth', 'has_store');
            //Xóa 1 ct giảm giá
            Route::delete('/{store_code}/discounts/{discount_id}', 'App\Http\Controllers\Api\User\DiscountController@deleteOneDiscount')->middleware('user_auth', 'has_store');


            //Tạo Voucher
            Route::post('/{store_code}/vouchers', 'App\Http\Controllers\Api\User\VoucherController@create')->middleware('user_auth', 'has_store');
            //Update Voucher
            Route::put('/{store_code}/vouchers/{voucher_id}', 'App\Http\Controllers\Api\User\VoucherController@updateOneVoucher')->middleware('user_auth', 'has_store');
            //Xem 1 Voucher
            Route::get('/{store_code}/vouchers/{voucher_id}', 'App\Http\Controllers\Api\User\VoucherController@getOneVoucher')->middleware('user_auth', 'has_store');
            //Xem danh sách sản phẩm trong 1 Voucher
            Route::get('/{store_code}/vouchers/{voucher_id}/products', 'App\Http\Controllers\Api\User\VoucherController@getProductVoucher')->middleware('user_auth', 'has_store');
            //Xem tất cả Voucher chuẩn vị và đang phát hành
            Route::get('/{store_code}/vouchers', 'App\Http\Controllers\Api\User\VoucherController@getAll')->middleware('user_auth', 'has_store');
            //Xem tất cả chương trình voucher  kết thúc
            Route::get('/{store_code}/vouchers_end', 'App\Http\Controllers\Api\User\VoucherController@getAllEnd')->middleware('user_auth', 'has_store');
            //Xóa 1 voucher
            Route::delete('/{store_code}/vouchers/{voucher_id}', 'App\Http\Controllers\Api\User\VoucherController@deleteOneVoucher')->middleware('user_auth', 'has_store');

            //Xem Danh sách Voucher Code
            Route::get('/{store_code}/vouchers/{voucher_id}/codes', 'App\Http\Controllers\Api\User\VoucherCodeController@index')->middleware('user_auth', 'has_store');
            //Thay đổi trạng thái Voucher Code
            Route::put('/{store_code}/vouchers/{voucher_id}/codes', 'App\Http\Controllers\Api\User\VoucherCodeController@updateStatus')->middleware('user_auth', 'has_store');
            //Export voucher code
            Route::get('/{store_code}/vouchers/{voucher_id}/codes/export', 'App\Http\Controllers\Api\User\VoucherCodeController@export');
            //Tạo link export voucher code
            Route::get('/{store_code}/vouchers/{voucher_id}/codes/link_export', 'App\Http\Controllers\Api\User\VoucherCodeController@link_export')->middleware('user_auth', 'has_store');

            //Tạo Combo
            Route::post('/{store_code}/combos', 'App\Http\Controllers\Api\User\ComboController@create')->middleware('user_auth', 'has_store');
            //Update Combo
            Route::put('/{store_code}/combos/{combo_id}', 'App\Http\Controllers\Api\User\ComboController@updateOneCombo')->middleware('user_auth', 'has_store');
            //Xem 1 Combo
            Route::get('/{store_code}/combos/{combo_id}', 'App\Http\Controllers\Api\User\ComboController@getOneCombo')->middleware('user_auth', 'has_store');
            //Xem tất cả Combo chuẩn vị và đang phát hành
            Route::get('/{store_code}/combos', 'App\Http\Controllers\Api\User\ComboController@getAll')->middleware('user_auth', 'has_store');
            //Xem tất cả Combo ket thuc
            Route::get('/{store_code}/combos_end', 'App\Http\Controllers\Api\User\ComboController@getAllEnd')->middleware('user_auth', 'has_store');
            //Xóa 1 combo
            Route::delete('/{store_code}/combos/{combo_id}', 'App\Http\Controllers\Api\User\ComboController@deleteOneCombo')->middleware('user_auth', 'has_store');

            //Tạo Tặng thưởng
            Route::post('/{store_code}/bonus_product', 'App\Http\Controllers\Api\User\BonusProductController@create')->middleware('user_auth', 'has_store');
            //Update  Tặng thưởng
            Route::put('/{store_code}/bonus_product/{bonus_product_id}', 'App\Http\Controllers\Api\User\BonusProductController@updateOne')->middleware('user_auth', 'has_store');
            //Xem 1  Tặng thưởng
            Route::get('/{store_code}/bonus_product/{bonus_product_id}', 'App\Http\Controllers\Api\User\BonusProductController@getOne')->middleware('user_auth', 'has_store');
            //Xem tất cả  Tặng thưởng chuẩn vị và đang phát hành
            Route::get('/{store_code}/bonus_product', 'App\Http\Controllers\Api\User\BonusProductController@getAll')->middleware('user_auth', 'has_store');
            //Xem tất cả  Tặng thưởng ket thuc
            Route::get('/{store_code}/bonus_product_end', 'App\Http\Controllers\Api\User\BonusProductController@getAllEnd')->middleware('user_auth', 'has_store');
            //Xóa 1  Tặng thưởng
            Route::delete('/{store_code}/bonus_product/{bonus_product_id}', 'App\Http\Controllers\Api\User\BonusProductController@deleteOne')->middleware('user_auth', 'has_store');

            //Item Tặng thưởng
            Route::get('/{store_code}/bonus_product/{bonus_product_id}/bonus_product_item', 'App\Http\Controllers\Api\User\BonusProductController@getOneItem')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/bonus_product/{bonus_product_id}/bonus_product_item', 'App\Http\Controllers\Api\User\BonusProductController@createItem')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/bonus_product/{bonus_product_id}/bonus_product_item', 'App\Http\Controllers\Api\User\BonusProductController@updateOneItem')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/bonus_product/{bonus_product_id}/bonus_product_item', 'App\Http\Controllers\Api\User\BonusProductController@destroyOneItem')->middleware('user_auth', 'has_store');

            //Danh sách thông báo đẩy
            Route::get('/{store_code}/notifications_history', 'App\Http\Controllers\Api\User\NotificationController@getAll')->middleware('user_auth', 'has_store');
            //đọc hết
            Route::get('/{store_code}/notifications_history/read_all', 'App\Http\Controllers\Api\User\NotificationController@readAll')->middleware('user_auth', 'has_store');


            //Giao hàng - Đối tác giao hàng user
            //Xem tất cả shipper
            Route::get('/{store_code}/shipments', 'App\Http\Controllers\Api\User\Shipment\ShipmentController@getAll')->middleware('user_auth', 'has_store');
            //Tính phí ship của 1 đối tác
            Route::post('/{store_code}/shipments/{partner_id}/calculate', 'App\Http\Controllers\Api\User\Shipment\ShipmentController@calculate')->middleware('user_auth', 'has_store');

            //Cập nhật 1 đối tác
            Route::put('/{store_code}/shipments/{partner_id}', 'App\Http\Controllers\Api\User\Shipment\ShipmentController@updateOne')->middleware('user_auth', 'has_store');

            //Login viettel lấy token
            Route::post('/{store_code}/shipment_get_token/viettel', 'App\Http\Controllers\Api\User\Shipment\LoginGetTokenController@viettel')->middleware('user_auth', 'has_store');

            //Login Nhất tín lấy token
            Route::post('/{store_code}/shipment_get_token/nhat_tin', 'App\Http\Controllers\Api\User\Shipment\LoginGetTokenController@nhatTin')->middleware('user_auth', 'has_store');

            //Login vietnam  lấy token
            Route::post('/{store_code}/shipment_get_token/vietnam_post', 'App\Http\Controllers\Api\User\Shipment\LoginGetTokenController@vietnamPost')->middleware('user_auth', 'has_store');

            //Danh sách nhà vận chuyển có thể sử dụng
            Route::post('/{store_code}/shipment/list_shipper', 'App\Http\Controllers\Api\Customer\CustomerShipperController@list_shipper')->middleware('user_auth', 'has_store');
            //Tính phí ship hàng

            Route::post('/{store_code}/shipment/calculate_fee/{partner_id}', 'App\Http\Controllers\Api\Customer\CustomerCartController@calculate_fee_by_partner_id')->middleware('user_auth', 'has_store');


            //Cài đặt OTP
            Route::get('/{store_code}/otp_configs', 'App\Http\Controllers\Api\User\OtpConfigController@index')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/otp_configs/{id}', 'App\Http\Controllers\Api\User\OtpConfigController@update')->middleware('user_auth', 'has_store');


            //Đơn vị gửi OTP
            Route::get('/{store_code}/otp_units', 'App\Http\Controllers\Api\User\OtpUnitController@index')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/otp_units/{id}', 'App\Http\Controllers\Api\User\OtpUnitController@show')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/otp_units/{id}', 'App\Http\Controllers\Api\User\OtpUnitController@update')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/otp_units/{id}/status', 'App\Http\Controllers\Api\User\OtpUnitController@updateStatus')->middleware('user_auth', 'has_store');
            // Route::post('/{store_code}/otp_units', 'App\Http\Controllers\Api\User\OtpUnitController@store')->middleware('user_auth', 'has_store');
            // Route::delete('/{store_code}/otp_units', 'App\Http\Controllers\Api\User\OtpUnitController@destroy')->middleware('user_auth', 'has_store');


            //Xem tất cả phương thức thanh toán
            Route::get('/{store_code}/payment_methods', 'App\Http\Controllers\Api\User\PaymentMethodController@getAll')->middleware('user_auth', 'has_store');
            //Cập nhật 1 phương thức thanh toán
            Route::put('/{store_code}/payment_methods/{method_id}', 'App\Http\Controllers\Api\User\PaymentMethodController@updateOne')->middleware('user_auth', 'has_store');

            //Địa chỉ 
            Route::post('/{store_code}/store_address', 'App\Http\Controllers\Api\User\StoreAddressController@create')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/store_address/{store_address_id}', 'App\Http\Controllers\Api\User\StoreAddressController@update')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/store_address', 'App\Http\Controllers\Api\User\StoreAddressController@getAll')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/store_address/{store_address_id}', 'App\Http\Controllers\Api\User\StoreAddressController@deleteOneStoreAddress')->middleware('user_auth', 'has_store');

            //Khách hàng
            Route::get('/{store_code}/customers', 'App\Http\Controllers\Api\User\CustomerController@getAll')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/customers/{customer_id}', 'App\Http\Controllers\Api\User\CustomerController@getOne')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/customers', 'App\Http\Controllers\Api\User\CustomerController@create')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/all_customers', 'App\Http\Controllers\Api\User\CustomerController@createManyCustomer')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/customers/{customer_id}', 'App\Http\Controllers\Api\User\CustomerController@update')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/customers/{customer_id}', 'App\Http\Controllers\Api\User\CustomerController@delete')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/address_customer', 'App\Http\Controllers\Api\User\CustomerController@getAddressCustomer')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/customers/{customer_id}/history_points', 'App\Http\Controllers\Api\User\CustomerController@historyPoints')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/customers/{customer_id}/sale_type', 'App\Http\Controllers\Api\User\CustomerController@setSalesPartner')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/customers/sale_type/list', 'App\Http\Controllers\Api\User\CustomerController@setListSalesPartner')->middleware('user_auth', 'has_store');
            //Cộng trừ xu
            Route::post('/{store_code}/customers/{customer_id}/add_sub_point', 'App\Http\Controllers\Api\User\CustomerController@addSubPoint')->middleware('user_auth', 'has_store');
            //Lịch sử thay đổi xu
            Route::get('/{store_code}/customers/{customer_id}/history_point', 'App\Http\Controllers\Api\User\CustomerController@historyChangePoint')->middleware('user_auth', 'has_store');

            //Orders
            Route::get('/{store_code}/orders', 'App\Http\Controllers\Api\User\OrderController@getAll')->middleware('user_auth', 'has_store');
            //Lấy thông tin 1 đơn hàng
            Route::get('/{store_code}/orders/{order_code}', 'App\Http\Controllers\Api\User\OrderController@getOne')->middleware('user_auth', 'has_store');
            //Xóa 1 đơn hàng
            Route::delete('/{store_code}/orders/{order_code}', 'App\Http\Controllers\Api\User\OrderController@delete')->middleware('user_auth', 'has_store');
            //Lịch sử trạng thái đơn hàng
            Route::get('/{store_code}/orders/status_records/{order_id}', 'App\Http\Controllers\Api\User\OrderController@status_records')->middleware('user_auth', 'has_store');
            //Thay đổi trạng thái đơn hàng
            Route::post('/{store_code}/orders/change_order_status', 'App\Http\Controllers\Api\User\OrderController@change_order_status')->middleware('user_auth', 'has_store');
            //Thay đổi trạng thái nhiều đơn đơn hàng
            Route::post('/{store_code}/orders/change_list_order_status', 'App\Http\Controllers\Api\User\OrderController@change_list_order_status')->middleware('user_auth', 'has_store');
            //Thay đổi trạng thái thanh toán
            Route::post('/{store_code}/orders/change_payment_status', 'App\Http\Controllers\Api\User\OrderController@change_payment_status')->middleware('user_auth', 'has_store');
            //Cập nhật thông tin đơn hàng
            Route::put('/{store_code}/orders/update/{order_code}', 'App\Http\Controllers\Api\User\OrderController@update')->middleware('user_auth', 'has_store');
            //Tính phí ship đơn hàng
            Route::post('/{store_code}/orders/calculate_fee/{order_code}', 'App\Http\Controllers\Api\Customer\CustomerCartController@calculate_fee')->middleware('user_auth', 'has_store');
            //Cập nhật thông tin kiện hàng
            Route::put('/{store_code}/orders/update_package/{order_code}', 'App\Http\Controllers\Api\User\OrderController@updatePackage')->middleware('user_auth', 'has_store');
            //Thanh toán đơn hàng
            Route::post('/{store_code}/orders/pay_order/{order_code}', 'App\Http\Controllers\Api\User\OrderController@pay_order')->middleware('user_auth', 'has_store');
            //Lịch sử thanh toán đơn hàng
            Route::get('/{store_code}/orders/history_pay/{order_code}', 'App\Http\Controllers\Api\User\OrderController@history_pay_order')->middleware('user_auth', 'has_store');

            // Lấy tổng số tiền đơn hàng theo phương thức thanh toán
            Route::get('/{store_code}/orders/totalPriceByMethodPayment/{method_payment_id}', 'App\Http\Controllers\Api\User\OrderController@handleGetTotalPriceByMethodPayment')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/orders/report_product/products', 'App\Http\Controllers\Api\User\OrderController@handleGetReportPaymentByProduct')->middleware('user_auth', 'has_store');

            //Danh sách giỏ hàng
            Route::get('/{store_code}/carts/{branch_id}/list', 'App\Http\Controllers\Api\User\CartController@cart_list')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Thay đổi tên giỏ hàng
            Route::put('/{store_code}/carts/{branch_id}/list/{cart_id}/change_name', 'App\Http\Controllers\Api\User\CartController@update_name_cart')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Tạo giỏ hàng mới
            Route::post('/{store_code}/carts/{branch_id}/list', 'App\Http\Controllers\Api\User\CartController@create_cart')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Tạo giỏ hàng chỉnh sửa đơn
            Route::post('/{store_code}/orders/create_cart_edit_order/{order_code}/init', 'App\Http\Controllers\Api\User\CartController@create_cart_edit_order')->middleware('user_auth', 'has_store', 'handle_price_agency');

            //Thông tin 1 giỏ hàng
            Route::get('/{store_code}/carts/{branch_id}/list/{cart_id}', 'App\Http\Controllers\Api\User\CartController@getOneCart')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            Route::get('/{store_code}/carts/{branch_id}/list/{cart_id}/v1', 'App\Http\Controllers\Api\User\CartController@getOneCartV1')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Thêm sản phẩm vào giỏ
            Route::post('/{store_code}/carts/{branch_id}/list/{cart_id}/items', 'App\Http\Controllers\Api\User\CartController@addLineItem')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            Route::post('/{store_code}/carts/{branch_id}/list/{cart_id}/items/v1', 'App\Http\Controllers\Api\User\CartController@addLineItemV1')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Cập nhật line item trong giỏ
            Route::put('/{store_code}/carts/{branch_id}/list/{cart_id}/items', 'App\Http\Controllers\Api\User\CartController@updateLineItem')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            Route::put('/{store_code}/carts/{branch_id}/list/{cart_id}/items/v1', 'App\Http\Controllers\Api\User\CartController@updateLineItemV1')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Cập nhật thông tin giỏ
            Route::put('/{store_code}/carts/{branch_id}/list/{cart_id}', 'App\Http\Controllers\Api\User\CartController@update_cart')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            Route::put('/{store_code}/carts/{branch_id}/list/{cart_id}/v1', 'App\Http\Controllers\Api\User\CartController@update_cartV1')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Xóa 1 giỏ
            Route::delete('/{store_code}/carts/{branch_id}/list/{cart_id}', 'App\Http\Controllers\Api\User\CartController@delete_cart')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Sử dụng voucher
            Route::post('/{store_code}/carts/{branch_id}/list/{cart_id}/use_voucher', 'App\Http\Controllers\Api\User\CartController@use_voucher')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            Route::post('/{store_code}/carts/{branch_id}/list/{cart_id}/use_voucher/v1', 'App\Http\Controllers\Api\User\CartController@use_voucherV1')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Sử dụng combo
            Route::post('/{store_code}/carts/{branch_id}/list/{cart_id}/use_combo', 'App\Http\Controllers\Api\User\CartController@addComboToCart')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            Route::post('/{store_code}/carts/{branch_id}/list/{cart_id}/use_combo/v1', 'App\Http\Controllers\Api\User\CartController@addComboToCartV1')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');

            //Cập nhật giá sản phẩm có trong giỏ
            Route::put('/{store_code}/carts/{branch_id}/list/{cart_id}/items/{cart_item_id}', 'App\Http\Controllers\Api\User\CartController@updatePriceCartItem')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Cập nhật ghi chú sản phẩm có trong giỏ
            Route::put('/{store_code}/carts/{branch_id}/list/{cart_id}/items/{cart_item_id}/note', 'App\Http\Controllers\Api\User\CartController@updateNoteCartItem')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');


            //Copy
            Route::post('/{store_code}/carts/{branch_id}/list/{cart_id}/create_cart_save', 'App\Http\Controllers\Api\User\CartController@create_cart_save')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');
            //Xóa nội dung 1 giỏ
            Route::get('/{store_code}/carts/{branch_id}/list/{cart_id}/clear_carts', 'App\Http\Controllers\Api\User\PosController@clearCart')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');


            //Lên đơn và thanh toán
            Route::post('/{store_code}/carts/{branch_id}/list/{cart_id}/order', 'App\Http\Controllers\Api\User\CartController@order_pay')->middleware('user_auth', 'has_store', 'has_branch', 'handle_price_agency');


            //Chat cho customer
            Route::post('/{store_code}/message_customers/{customer_id}', 'App\Http\Controllers\RedisRealtimeController@sendMessageCustomer')->middleware('user_auth', 'has_store');
            //Danh sách tổng quan tin nhắn
            Route::get('/{store_code}/message_customers', 'App\Http\Controllers\RedisRealtimeController@getAll')->middleware('user_auth', 'has_store');
            //Danh sách tin nhắn với customer
            Route::get('/{store_code}/message_customers/{customer_id}', 'App\Http\Controllers\RedisRealtimeController@getAllOneCustomer')->middleware('user_auth', 'has_store');


            //Báo cáo doanh thu tổng quan
            Route::get('/{store_code}/report/overview', 'App\Http\Controllers\Api\User\ReportController@overview')->middleware('user_auth', 'has_store');

            //Báo cáo top 10 sản phẩm
            Route::get('/{store_code}/report/top_ten_products', 'App\Http\Controllers\Api\User\ReportController@top_ten_products')->middleware('user_auth', 'has_store');

            //Báo cáo tổng quan kho
            Route::get('/{store_code}/report/stock/overview', 'App\Http\Controllers\Api\User\ReportInventoryController@overviewStock')->middleware('user_auth', 'has_store');

            //------------------------------------
            //Báo cáo nhập xuất kho
            Route::get('/{store_code}/report/stock/product_import_export_stock', 'App\Http\Controllers\Api\User\ReportInventoryController@product_import_export_stock')->middleware('user_auth', 'has_store');

            //Báo cáo nhập xuất kho
            Route::get('/{store_code}/report/stock/{branch_id}/product_import_export_stock', 'App\Http\Controllers\Api\User\ReportInventoryController@product_import_export_stock')->middleware('user_auth', 'has_store', 'has_branch');
            //------------------------------------
            //------------------------------------
            //Báo cáo nhập xuất kho
            Route::get('/{store_code}/report/stock/product_last_inventory', 'App\Http\Controllers\Api\User\ReportInventoryController@product_last_inventory')->middleware('user_auth', 'has_store');
            //Báo cáo nhập xuất kho (tất cả chi nhánh chưa có)
            Route::get('/{store_code}/report/stock/{branch_id}/product_last_inventory', 'App\Http\Controllers\Api\User\ReportInventoryController@product_last_inventory')->middleware('user_auth', 'has_store', 'has_branch');

            //Báo cáo lịch sử nhập xuất tất cả
            Route::get('/{store_code}/report/stock/inventory_histories', 'App\Http\Controllers\Api\User\ReportInventoryController@inventory_histories')->middleware('user_auth', 'has_store');
            //Báo cáo lịch sử nhập xuất tất cả
            Route::get('/{store_code}/report/stock/{branch_id}/inventory_histories', 'App\Http\Controllers\Api\User\ReportInventoryController@inventory_histories')->middleware('user_auth', 'has_store', 'has_branch');
            //------------------------------------

            //------------------------------------
            //Báo cáo lãi lỗ
            Route::get('/{store_code}/report/finance/profit_and_loss', 'App\Http\Controllers\Api\User\ReportFinanceController@profit_and_loss')->middleware('user_auth', 'has_store');
            //Báo cáo thu chi
            Route::get('/{store_code}/report/finance/revenue_expenditure', 'App\Http\Controllers\Api\User\ReportInventoryController@revenue_expenditure')->middleware('user_auth', 'has_store');
            //Nợ phải trả NCC
            Route::get('/{store_code}/report/finance/supplier_debt', 'App\Http\Controllers\Api\User\ReportFinanceController@report_supplier_debt')->middleware('user_auth', 'has_store');
            //Nợ phải thu customer
            Route::get('/{store_code}/report/finance/customer_debt', 'App\Http\Controllers\Api\User\ReportFinanceController@report_customer_debt')->middleware('user_auth', 'has_store');

            //Báo cáo lãi lỗ
            Route::get('/{store_code}/report/finance/{branch_id}/profit_and_loss', 'App\Http\Controllers\Api\User\ReportFinanceController@profit_and_loss')->middleware('user_auth', 'has_store', 'has_branch');
            //Báo cáo thu chi
            Route::get('/{store_code}/report/finance/{branch_id}/revenue_expenditure', 'App\Http\Controllers\Api\User\ReportInventoryController@revenue_expenditure')->middleware('user_auth', 'has_store', 'has_branch');
            //Export thu chi
            Route::get('/{store_code}/report/finance/{branch_id}/revenue_expenditure/export', 'App\Http\Controllers\Api\User\ReportInventoryController@export');
            //Tạo link export
            Route::get('/{store_code}/report/finance/{branch_id}/revenue_expenditure/link_export', 'App\Http\Controllers\Api\User\ReportInventoryController@link_export')->middleware('user_auth', 'has_store', 'has_branch');
            //Nợ phải trả NCC
            Route::get('/{store_code}/report/finance/{branch_id}/supplier_debt', 'App\Http\Controllers\Api\User\ReportFinanceController@report_supplier_debt')->middleware('user_auth', 'has_store', 'has_branch');
            //Nợ phải thu customer
            Route::get('/{store_code}/report/finance/{branch_id}/customer_debt', 'App\Http\Controllers\Api\User\ReportFinanceController@report_customer_debt')->middleware('user_auth', 'has_store', 'has_branch');

            //------------------------------------



            //Danh sách đánh giá của customer
            Route::get('/{store_code}/reviews', 'App\Http\Controllers\Api\User\ReviewsController@getAll')->middleware('user_auth', 'has_store');
            //xóa 1 đánh giá
            Route::delete('/{store_code}/reviews/{review_id}', 'App\Http\Controllers\Api\User\ReviewsController@deleteOne')->middleware('user_auth', 'has_store');
            //cập nhật 1 đánh giá
            Route::put('/{store_code}/reviews/{review_id}', 'App\Http\Controllers\Api\User\ReviewsController@updateOne')->middleware('user_auth', 'has_store');

            //Danh sách Badges chi so user
            Route::get('/{store_code}/badges', 'App\Http\Controllers\Api\User\BadgesController@getBadges')->middleware('user_auth', 'has_store');

            //Đặt lịch thông báo tới customer
            Route::post('/{store_code}/notifications/schedule', 'App\Http\Controllers\Api\User\NotificationTaskController@setup')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/notifications/schedule', 'App\Http\Controllers\Api\User\NotificationTaskController@tasks')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/notifications/schedule/{schedule_id}', 'App\Http\Controllers\Api\User\NotificationTaskController@delete')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/notifications/schedule/{schedule_id}', 'App\Http\Controllers\Api\User\NotificationTaskController@edit')->middleware('user_auth', 'has_store');
            // Route::get('/{store_code}/notifications/schedule/test', 'App\Http\Controllers\Api\User\NotificationTaskController@test')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/notifications/schedule/test', 'App\Http\Controllers\Api\User\NotificationTaskController@test_send')->middleware('user_auth', 'has_store');

            ////// ////// ////// ////// //////
            //Thêm vào danh sách banner ad web
            Route::post('/{store_code}/banner_ads', 'App\Http\Controllers\Api\User\BannerAdController@create')->middleware('user_auth', 'has_store');
            //Xóa 1 banner ads web 
            Route::delete('/{store_code}/banner_ads/{banner_ad_id}', 'App\Http\Controllers\Api\User\BannerAdController@deleteOneBannerAd')->middleware('user_auth', 'has_store');
            //Danh sách banner ad web
            Route::get('/{store_code}/banner_ads', 'App\Http\Controllers\Api\User\BannerAdController@getAll')->middleware('user_auth', 'has_store');
            //Cập nhật banner ads web
            Route::put('/{store_code}/banner_ads/{banner_ad_id}', 'App\Http\Controllers\Api\User\BannerAdController@updateOneBannerAd')->middleware('user_auth', 'has_store');

            //Thêm vào danh sách banner ad web (do chặn nên bổ sung)
            Route::post('/{store_code}/banner_webad', 'App\Http\Controllers\Api\User\BannerAdController@create')->middleware('user_auth', 'has_store');
            //Xóa 1 banner ads web 
            Route::delete('/{store_code}/banner_webad/{banner_ad_id}', 'App\Http\Controllers\Api\User\BannerAdController@deleteOneBannerAd')->middleware('user_auth', 'has_store');
            //Danh sách banner ad web
            Route::get('/{store_code}/banner_webad', 'App\Http\Controllers\Api\User\BannerAdController@getAll')->middleware('user_auth', 'has_store');
            //Cập nhật banner ads web
            Route::put('/{store_code}/banner_webad/{banner_ad_id}', 'App\Http\Controllers\Api\User\BannerAdController@updateOneBannerAd')->middleware('user_auth', 'has_store');
            ////// ////// ////// ////// ////// 

            //Thêm vào danh sách banner ad app
            Route::post('/{store_code}/banner_ads_app', 'App\Http\Controllers\Api\User\BannerAdController@create_app')->middleware('user_auth', 'has_store');
            //Xóa 1 banner ads app
            Route::delete('/{store_code}/banner_ads_app/{banner_ad_id}', 'App\Http\Controllers\Api\User\BannerAdController@deleteOneBannerAd_app')->middleware('user_auth', 'has_store');
            //Danh sách banner ad app
            Route::get('/{store_code}/banner_ads_app', 'App\Http\Controllers\Api\User\BannerAdController@getAll_app')->middleware('user_auth', 'has_store');
            //Cập nhật banner ads app
            Route::put('/{store_code}/banner_ads_app/{banner_ad_id}', 'App\Http\Controllers\Api\User\BannerAdController@updateOneBannerAd_app')->middleware('user_auth', 'has_store');


            //Thêm tầng đại lý
            Route::post('/{store_code}/agency_type', 'App\Http\Controllers\Api\User\AgencyController@createAgencyType')->middleware('user_auth', 'has_store');
            //Cập nhật tầng đại lý
            Route::put('/{store_code}/agency_type/{agency_type_id}', 'App\Http\Controllers\Api\User\AgencyController@updateAgencyType')->middleware('user_auth', 'has_store');
            //Xóa tầng đại lý
            Route::delete('/{store_code}/agency_type/{agency_type_id}', 'App\Http\Controllers\Api\User\AgencyController@deleteAgencyType')->middleware('user_auth', 'has_store');
            //Danh sách tầng
            Route::get('/{store_code}/agency_type', 'App\Http\Controllers\Api\User\AgencyController@getAgencyType')->middleware('user_auth', 'has_store');
            //Auto set tầng đại lý theo thiết đặt
            Route::post('/{store_code}/auto_set_level_agency_type', 'App\Http\Controllers\Api\User\AgencyController@auto_set_level_agency_type')->middleware('user_auth', 'has_store');
            //Sap xep
            Route::post('/{store_code}/sort_agency_type', 'App\Http\Controllers\Api\User\AgencyController@sortLevel')->middleware('user_auth', 'has_store');
            //Danh sách lịch sử thay đổi
            Route::get('/{store_code}/get_history_change_level_agency', 'App\Http\Controllers\Api\User\AgencyController@getHistoryChangeLevelAgency')->middleware('user_auth', 'has_store');

            //Cập nhật chiết khấu đại lý
            Route::post('/{store_code}/agency_type/{agency_type_id}/override_price', 'App\Http\Controllers\Api\User\AgencyController@override_price')->middleware('user_auth', 'has_store');
            //Cập nhật hoa hồng đại lý
            Route::post('/{store_code}/agency_type/{agency_type_id}/edit_percent_agency', 'App\Http\Controllers\Api\User\AgencyController@edit_percent_agency')->middleware('user_auth', 'has_store');

            //Đại lý
            Route::get('/{store_code}/agencies', 'App\Http\Controllers\Api\User\AgencyController@getAllAgency')->middleware('user_auth', 'has_store');

            //Đại lý TOP
            Route::get('/{store_code}/agencies/report', 'App\Http\Controllers\Api\User\AgencyController@getAllAgencyTop')->middleware('user_auth', 'has_store');
            //Đại lý 
            Route::get('/{store_code}/agencies/report_share', 'App\Http\Controllers\Api\User\AgencyController@getAllAgencyTopShare')->middleware('user_auth', 'has_store');

            //Danh sách yêu cầu làm đại lý
            Route::get('/{store_code}/agency_register_requests', 'App\Http\Controllers\Api\User\AgencyController@getAllAgencyRegisterRequest')->middleware('user_auth', 'has_store');
            //Xử lý yêu cầu
            Route::put('/{store_code}/agency_register_requests/{agency_register_request_id}/status', 'App\Http\Controllers\Api\User\AgencyController@handleAgencyRegisterRequest')->middleware('user_auth', 'has_store');


            //Chỉnh sửa Đại lý
            Route::put('/{store_code}/agencies/{agency_id}', 'App\Http\Controllers\Api\User\AgencyController@update_for_agency')->middleware('user_auth', 'has_store');
            //Cộng trừ tiền CTV
            Route::post('/{store_code}/agencies/{agency_id}/add_sub_balance', 'App\Http\Controllers\Api\User\AgencyController@addSubBalanceAgency')->middleware('user_auth', 'has_store');
            //Lịch sử thay đổi số dư CTV
            Route::get('/{store_code}/agencies/{agency_id}/history_balance', 'App\Http\Controllers\Api\User\AgencyController@historyChangeBalance')->middleware('user_auth', 'has_store');

            //Danh sách yêu cầu thanh toán
            Route::get('/{store_code}/agencies/request_payment/current', 'App\Http\Controllers\Api\User\AgencyControllerPayController@all_request_payment')->middleware('user_auth', 'has_store');
            //Lịch sử yêu cầu thanh toán
            Route::get('/{store_code}/agencies/request_payment/history', 'App\Http\Controllers\Api\User\AgencyControllerPayController@history_request_payment')->middleware('user_auth', 'has_store');
            //Thay đổi trạng thái chờ xỷ lý sang đã thanh toán hoặc hoàn
            Route::post('/{store_code}/agencies/request_payment/change_status', 'App\Http\Controllers\Api\User\AgencyControllerPayController@change_status')->middleware('user_auth', 'has_store');
            //Quyết toán toàn bộ CTV
            Route::post('/{store_code}/agencies/request_payment/settlement', 'App\Http\Controllers\Api\User\AgencyControllerPayController@settlement')->middleware('user_auth', 'has_store');

            //Cấu hình Đại lý
            Route::get('/{store_code}/agency_configs', 'App\Http\Controllers\Api\User\AgencyController@getConfig')->middleware('user_auth', 'has_store');
            //Cập nhật Đại lý
            Route::post('/{store_code}/agency_configs', 'App\Http\Controllers\Api\User\AgencyController@update')->middleware('user_auth', 'has_store');
            //Cập nhật Kỳ thưởng
            Route::put('/{store_code}/config_type_bonus_period_import', 'App\Http\Controllers\Api\User\AgencyController@updateConfigTypeBonusPeriodImport')->middleware('user_auth', 'has_store');

            //Danh sánh bậc thang thưởng hoa hồng
            Route::get('/{store_code}/agency_configs/bonus_steps', 'App\Http\Controllers\Api\User\AgencyController@getStepBonusAll')->middleware('user_auth', 'has_store');
            //Thêm 1 bậc thang hoa hồng
            Route::post('/{store_code}/agency_configs/bonus_steps', 'App\Http\Controllers\Api\User\AgencyController@create')->middleware('user_auth', 'has_store');
            //Cập nhật 1 bậc thang hoa hồng
            Route::put('/{store_code}/agency_configs/bonus_steps/{step_id}', 'App\Http\Controllers\Api\User\AgencyController@updateOneStep')->middleware('user_auth', 'has_store');
            //Delete 1 bậc thang hoa hồng
            Route::delete('/{store_code}/agency_configs/bonus_steps/{step_id}', 'App\Http\Controllers\Api\User\AgencyController@deleteOneStep')->middleware('user_auth', 'has_store');


            //Danh sánh bậc thang thưởng nhập hàng
            Route::get('/{store_code}/agency_configs/import_bonus_steps', 'App\Http\Controllers\Api\User\AgencyController@getStepImportBonusAll')->middleware('user_auth', 'has_store');
            //Thêm 1 bậc thang nhập hàng
            Route::post('/{store_code}/agency_configs/import_bonus_steps', 'App\Http\Controllers\Api\User\AgencyController@createStepImport')->middleware('user_auth', 'has_store');
            //Cập nhật 1 bậc thang nhập hàng
            Route::put('/{store_code}/agency_configs/import_bonus_steps/{step_id}', 'App\Http\Controllers\Api\User\AgencyController@updateOneStepImport')->middleware('user_auth', 'has_store');
            //Delete 1 bậc thang nhập hàng
            Route::delete('/{store_code}/agency_configs/import_bonus_steps/{step_id}', 'App\Http\Controllers\Api\User\AgencyController@deleteOneStepImport')->middleware('user_auth', 'has_store');


            //Cộng tác viên
            Route::get('/{store_code}/collaborators', 'App\Http\Controllers\Api\User\CollaboratorsController@getAllCollaborator')->middleware('user_auth', 'has_store');
            //Cộng tác viên báo cáo
            Route::get('/{store_code}/collaborators/report', 'App\Http\Controllers\Api\User\CollaboratorsController@getAllCollaboratorTop')->middleware('user_auth', 'has_store');

            //Danh sách yêu cầu làm ctv
            Route::get('/{store_code}/collaborator_register_requests', 'App\Http\Controllers\Api\User\CollaboratorsController@getAllCollaboratorRegisterRequest')->middleware('user_auth', 'has_store');
            //Xử lý yêu cầu
            Route::put('/{store_code}/collaborator_register_requests/{collaborator_register_request_id}/status', 'App\Http\Controllers\Api\User\CollaboratorsController@handleCollaboratorRegisterRequest')->middleware('user_auth', 'has_store');


            //Chỉnh sửa cộng tác viên
            Route::put('/{store_code}/collaborators/{collaborator_id}', 'App\Http\Controllers\Api\User\CollaboratorsController@update_for_collaborator')->middleware('user_auth', 'has_store');
           
            // Chỉnh sửa thông tin ngân hàng
            Route::put('/{store_code}/collaborators/{collaborator_id}/updateBankInfo', 'App\Http\Controllers\Api\User\CollaboratorsController@updateBankInfoCollaborator')->middleware('user_auth', 'has_store');
            //Cộng trừ tiền CTV
            Route::post('/{store_code}/collaborators/{collaborator_id}/add_sub_balance', 'App\Http\Controllers\Api\User\CollaboratorsController@addSubBalanceCTV')->middleware('user_auth', 'has_store');
            //Lịch sử thay đổi số dư CTV
            Route::get('/{store_code}/collaborators/{collaborator_id}/history_balance', 'App\Http\Controllers\Api\User\CollaboratorsController@historyChangeBalance')->middleware('user_auth', 'has_store');

            //Lịch sử thao tác
            Route::get('/{store_code}/operation_histories', 'App\Http\Controllers\Api\User\HistoryOperationController@getAll')->middleware('user_auth', 'has_store');

            //Danh sách yêu cầu thanh toán
            Route::get('/{store_code}/collaborators/request_payment/current', 'App\Http\Controllers\Api\User\CollaboratorPayController@all_request_payment')->middleware('user_auth', 'has_store');
            //Lịch sử yêu cầu thanh toán
            Route::get('/{store_code}/collaborators/request_payment/history', 'App\Http\Controllers\Api\User\CollaboratorPayController@history_request_payment')->middleware('user_auth', 'has_store');
            //Thay đổi trạng thái chờ xỷ lý sang đã thanh toán hoặc hoàn
            Route::post('/{store_code}/collaborators/request_payment/change_status', 'App\Http\Controllers\Api\User\CollaboratorPayController@change_status')->middleware('user_auth', 'has_store');
            //Quyết toán toàn bộ CTV
            Route::post('/{store_code}/collaborators/request_payment/settlement', 'App\Http\Controllers\Api\User\CollaboratorPayController@settlement')->middleware('user_auth', 'has_store');

            //Cấu hình Cộng tác viên
            Route::get('/{store_code}/collaborator_configs', 'App\Http\Controllers\Api\User\CollaboratorsController@getConfig')->middleware('user_auth', 'has_store');
            //Cập nhật cộng tác viên
            Route::post('/{store_code}/collaborator_configs', 'App\Http\Controllers\Api\User\CollaboratorsController@update')->middleware('user_auth', 'has_store');
            //Danh sánh bậc thang thưởng 
            Route::get('/{store_code}/collaborator_configs/bonus_steps', 'App\Http\Controllers\Api\User\CollaboratorsController@getStepBonusAll')->middleware('user_auth', 'has_store');

            //Thêm 1 bậc thang
            Route::post('/{store_code}/collaborator_configs/bonus_steps', 'App\Http\Controllers\Api\User\CollaboratorsController@create')->middleware('user_auth', 'has_store');
            //Cập nhật 1 bậc thang
            Route::put('/{store_code}/collaborator_configs/bonus_steps/{step_id}', 'App\Http\Controllers\Api\User\CollaboratorsController@updateOneStep')->middleware('user_auth', 'has_store');
            //Delete 1 bậc thang
            Route::delete('/{store_code}/collaborator_configs/bonus_steps/{step_id}', 'App\Http\Controllers\Api\User\CollaboratorsController@deleteOneStep')->middleware('user_auth', 'has_store');

            //Danh sách popup
            Route::get('/{store_code}/popups', 'App\Http\Controllers\Api\User\PopupController@getPopupAll')->middleware('user_auth', 'has_store');
            //Thêm 1 popup
            Route::post('/{store_code}/popups', 'App\Http\Controllers\Api\User\PopupController@create')->middleware('user_auth', 'has_store');
            //Cập nhật 1 popup
            Route::put('/{store_code}/popups/{popup_id}', 'App\Http\Controllers\Api\User\PopupController@updateOnePopup')->middleware('user_auth', 'has_store');
            //Delete 1 popup
            Route::delete('/{store_code}/popups/{popup_id}', 'App\Http\Controllers\Api\User\PopupController@deleteOnePopup')->middleware('user_auth', 'has_store');


            //Lấy cấu hình điểm thưởng
            Route::get('/{store_code}/reward_points', 'App\Http\Controllers\Api\User\ConfigPointController@getConfig')->middleware('user_auth', 'has_store');
            //Cấu hình lại điểm thưởng
            Route::post('/{store_code}/reward_points', 'App\Http\Controllers\Api\User\ConfigPointController@updateConfig')->middleware('user_auth', 'has_store');
            //Khôi phục mặc định
            Route::get('/{store_code}/reward_points/reset', 'App\Http\Controllers\Api\User\ConfigPointController@reset')->middleware('user_auth', 'has_store');

            //Lấy cấu hình public api
            Route::get('/{store_code}/public_api_config', 'App\Http\Controllers\Api\User\PubicApiController@getConfig')->middleware('user_auth', 'has_store');
            //Cấu hình public api
            Route::post('/{store_code}/public_api_config', 'App\Http\Controllers\Api\User\PubicApiController@updateConfig')->middleware('user_auth', 'has_store');
            //Reset token public api
            Route::get('/{store_code}/public_api_config/change_token', 'App\Http\Controllers\Api\User\PubicApiController@changeToken')->middleware('user_auth', 'has_store');
            //testSendWebHook
            Route::get('/{store_code}/public_api_config/test_send_webhook', 'App\Http\Controllers\Api\User\PubicApiController@testSendWebHook')->middleware('user_auth', 'has_store');


            //Cấu hình thưởng đại lý
            Route::get('/{store_code}/bonus_agency_config', 'App\Http\Controllers\Api\User\BonusAgencyController@getBonusAgencyConfig')->middleware('user_auth', 'has_store');
            //Thêm vào danh sách thưởng đơn hàng
            Route::get('/{store_code}/bonus_agency_config', 'App\Http\Controllers\Api\User\BonusAgencyController@getBonusAgencyConfig')->middleware('user_auth', 'has_store');
            //Xóa 1 step thưởng thưởng đơn hàng
            Route::delete('/{store_code}/bonus_agency_config/bonus_steps/{step_id}', 'App\Http\Controllers\Api\User\BonusAgencyController@deleteOneStep')->middleware('user_auth', 'has_store');
            //Thêm 1 step thưởng thưởng đơn hàng
            Route::post('/{store_code}/bonus_agency_config/bonus_steps', 'App\Http\Controllers\Api\User\BonusAgencyController@createOneStep')->middleware('user_auth', 'has_store');

            //Chỉnh sửa 1 step thưởng thưởng đơn hàng
            Route::put('/{store_code}/bonus_agency_config/bonus_steps/{step_id}', 'App\Http\Controllers\Api\User\BonusAgencyController@updateOneStep')->middleware('user_auth', 'has_store');

            //Cấu hình thưởng thưởng đơn hàng
            Route::put('/{store_code}/bonus_agency_config', 'App\Http\Controllers\Api\User\BonusAgencyController@updateConfig')->middleware('user_auth', 'has_store');


            //POS cập nhật thông tin giỏ hàng
            Route::post('/{store_code}/pos/carts', 'App\Http\Controllers\Api\Customer\CustomerCartController@updateCartInfo')->middleware('user_auth', 'has_store');
            //POS Danh sách sản phẩm giỏ hàng
            Route::get('/{store_code}/pos/carts', 'App\Http\Controllers\Api\Customer\CustomerCartController@getAll')->middleware('user_auth', 'has_store');
            //POS Hoàn tiền trả hàng
            Route::post('/{store_code}/pos/refund', 'App\Http\Controllers\Api\User\PosController@refund')->middleware('user_auth', 'has_store');
            //Gửi đơn hàng tới email
            Route::post('/{store_code}/pos/send_order_email', 'App\Http\Controllers\Api\User\PosController@sendOrderToEmail')->middleware('user_auth', 'has_store');



            //In bill in hóa đơn phiếu in
            Route::get('/print/bill/{order_code}', 'App\Http\Controllers\Api\User\PrintController@print_bill');


            //Thêm sản phẩm giỏ hàng
            Route::post('/{store_code}/pos/carts/items', 'App\Http\Controllers\Api\Customer\CustomerCartController@addLineItem')->middleware('user_auth', 'has_store', 'handle_price_agency');
            //Cập nhật sản phẩm giỏ hàng
            Route::put('/{store_code}/pos/carts/items', 'App\Http\Controllers\Api\Customer\CustomerCartController@updateLineItem')->middleware('user_auth', 'has_store', 'handle_price_agency');
            //Lên đơn hàng
            Route::post('/{store_code}/pos/carts/orders', 'App\Http\Controllers\Api\Customer\CustomerOrderController@create')->middleware('user_auth', 'has_store', 'handle_price_agency');

            //Gửi Đẩy đơn hàng cho shipper
            Route::post('/{store_code}/shipper/send_order', 'App\Http\Controllers\Api\User\SendOrderShipperController@sendOrderToShipper')->middleware('user_auth', 'has_store');
            //Danh sách lịch sử đơn hàng
            Route::post('/{store_code}/shipper/history_order_status', 'App\Http\Controllers\Api\User\HistoryOrderDeliveryController@getHistoryStatus')->middleware('user_auth', 'has_store');
            //Lấy trạng thái đơn hàng và cập nhật
            Route::post('/{store_code}/shipper/order_and_payment_status/{order_code}', 'App\Http\Controllers\Api\User\HistoryOrderDeliveryController@orderAndPaymentStatus')->middleware('user_auth', 'has_store');
            //Hủy kết nối vận chuyển
            Route::post('/{store_code}/shipper/cancel_order_ship_code/{order_code}', 'App\Http\Controllers\Api\User\HistoryOrderDeliveryController@cancelOrderShipCode')->middleware('user_auth', 'has_store');



            //Thông tin cấu hình chung
            Route::get('/{store_code}/general_settings', 'App\Http\Controllers\Api\User\GeneralSettingController@getSetting')->middleware('user_auth', 'has_store');
            //Cập nhật cấu hình chung
            Route::post('/{store_code}/general_settings', 'App\Http\Controllers\Api\User\GeneralSettingController@update')->middleware('user_auth', 'has_store');

            //Cấu hình vận chuyển
            Route::get('/{store_code}/config_ship', 'App\Http\Controllers\Api\User\ConfigShipController@configShip')->middleware('user_auth', 'has_store');
            //Cấu hình vận chuyển
            Route::put('/{store_code}/config_ship', 'App\Http\Controllers\Api\User\ConfigShipController@updateConfigShip')->middleware('user_auth', 'has_store');

            //Danh sách khách hàng sale
            Route::post('/{store_code}/customer_sales', 'App\Http\Controllers\Api\User\CustomerSaleController@create')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/customer_sales', 'App\Http\Controllers\Api\User\CustomerSaleController@getAll')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/customer_sales/{customersale_id}', 'App\Http\Controllers\Api\User\CustomerSaleController@getOne')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/customer_sales/{customersale_id}', 'App\Http\Controllers\Api\User\CustomerSaleController@delete')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/customer_sales/{customersale_id}', 'App\Http\Controllers\Api\User\CustomerSaleController@update')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/customer_sales/all', 'App\Http\Controllers\Api\User\CustomerSaleController@createManyCustomerSale')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/customer_sales', 'App\Http\Controllers\Api\User\CustomerSaleController@updateMany')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/customer_sales_send_to_customer', 'App\Http\Controllers\Api\User\CustomerSaleController@sendToCustomer')->middleware('user_auth', 'has_store');


            //Đăng bài cộng đồng
            Route::post('/{store_code}/community_posts', 'App\Http\Controllers\Api\User\CommunityPostController@create')->middleware('user_auth', 'has_store');
            //Danh sách bài viết cộng đồng
            Route::get('/{store_code}/community_posts', 'App\Http\Controllers\Api\User\CommunityPostController@getAll')->middleware('user_auth', 'has_store');
            //Sửa bài đăng cộng đồng
            Route::put('/{store_code}/community_posts/{community_post_id}', 'App\Http\Controllers\Api\User\CommunityPostController@update')->middleware('user_auth', 'has_store');
            //Get 1 bài đăng cộng đồng
            Route::get('/{store_code}/community_posts/{community_post_id}', 'App\Http\Controllers\Api\User\CommunityPostController@getOne')->middleware('user_auth', 'has_store');
            //Xóa bài đăng cộng đồng
            Route::delete('/{store_code}/community_posts/{community_post_id}', 'App\Http\Controllers\Api\User\CommunityPostController@delete')->middleware('user_auth', 'has_store');
            //Đăng lại cộng đồng
            Route::put('/{store_code}/community_posts/{community_post_id}/reup', 'App\Http\Controllers\Api\User\CommunityPostController@reup')->middleware('user_auth', 'has_store');

            //Ghim
            Route::post('/{store_code}/community_post_ghim', 'App\Http\Controllers\Api\User\CommunityPostController@ghim')->middleware('user_auth', 'has_store');

            //Bình luận
            Route::post('/{store_code}/community_comments', 'App\Http\Controllers\Api\User\CommunityCommentController@create')->middleware('user_auth', 'has_store');
            //Danh sách Bình luận
            Route::get('/{store_code}/community_comments', 'App\Http\Controllers\Api\User\CommunityCommentController@getAll')->middleware('user_auth', 'has_store');
            //Sửa Bình luận
            Route::put('/{store_code}/community_comments/{community_comment_id}', 'App\Http\Controllers\Api\User\CommunityCommentController@update')->middleware('user_auth', 'has_store');
            //Xóa Bình luận
            Route::delete('/{store_code}/community_comments/{community_comment_id}', 'App\Http\Controllers\Api\User\CommunityCommentController@delete')->middleware('user_auth', 'has_store');

            //Thêm khóa học
            Route::post('/{store_code}/train_courses', 'App\Http\Controllers\Api\User\TrainCourseController@create')->middleware('user_auth', 'has_store');
            //Danh sách khóa học
            Route::get('/{store_code}/train_courses', 'App\Http\Controllers\Api\User\TrainCourseController@getAll')->middleware('user_auth', 'has_store');
            //Danh sách tất cả khóa học
            Route::get('/{store_code}/train_courses_all', 'App\Http\Controllers\Api\User\TrainCourseController@getAllForFilter')->middleware('user_auth', 'has_store');
            //Sửa khóa học
            Route::put('/{store_code}/train_courses/{course_id}', 'App\Http\Controllers\Api\User\TrainCourseController@update')->middleware('user_auth', 'has_store');
            //Thông tin 1 khóa học
            Route::get('/{store_code}/train_courses/{course_id}', 'App\Http\Controllers\Api\User\TrainCourseController@getOne')->middleware('user_auth', 'has_store');
            //Xóa khóa học
            Route::delete('/{store_code}/train_courses/{course_id}', 'App\Http\Controllers\Api\User\TrainCourseController@delete')->middleware('user_auth', 'has_store');

            //Danh sách chương và bài học
            Route::get('/{store_code}/train_chapter_lessons/{train_course_id}', 'App\Http\Controllers\Api\User\TrainChaptersController@getAll')->middleware('user_auth', 'has_store');

            //Thêm chương học
            Route::post('/{store_code}/train_chapters', 'App\Http\Controllers\Api\User\TrainChaptersController@createChapter')->middleware('user_auth', 'has_store');
            //Sửa chương học
            Route::put('/{store_code}/train_chapters/{train_chapter_id}', 'App\Http\Controllers\Api\User\TrainChaptersController@updateChapter')->middleware('user_auth', 'has_store');
            //Xóa chương học
            Route::delete('/{store_code}/train_chapters/{train_chapter_id}', 'App\Http\Controllers\Api\User\TrainChaptersController@deleteChapter')->middleware('user_auth', 'has_store');
            //Sắp chương học
            Route::put('/{store_code}/train_chapters_sort', 'App\Http\Controllers\Api\User\TrainChaptersController@sortChapter')->middleware('user_auth', 'has_store');

            //Lịch sử  DS khách hàng làm bài thi
            Route::get('/{store_code}/customers/quizzes/histories', 'App\Http\Controllers\Api\User\LastSubmitQuizzesController@getHistoryQuizzes')->middleware('user_auth', 'has_store');
            //Lịch sử chi tiết của một khách hàng làm bài thi
            Route::get('/{store_code}/customers/{customer_id}/quizzes/histories', 'App\Http\Controllers\Api\User\LastSubmitQuizzesController@getDetailHistoryQuizzesForCustomer')->middleware('user_auth', 'has_store');
            //Lịch sử chi tiết những lần làm bài thi của khách hàng
            Route::get('/{store_code}/customers/{customer_id}/quizzes/{quiz_id}/histories', 'App\Http\Controllers\Api\User\LastSubmitQuizzesController@getDetailQuizHistoryForCustomer')->middleware('user_auth', 'has_store');

            //Thêm bài thi câu hỏi trắc nghiệm
            Route::post('/{store_code}/train_courses/{train_course_id}/quiz', 'App\Http\Controllers\Api\User\TrainQuizController@createQuiz')->middleware('user_auth', 'has_store');
            //Danh sách câu hỏi trắc nghiệm
            Route::get('/{store_code}/train_courses/{train_course_id}/quiz', 'App\Http\Controllers\Api\User\TrainQuizController@getAllQuiz')->middleware('user_auth', 'has_store');
            //Sửa bài thi câu hỏi trắc nghiệm
            Route::put('/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}', 'App\Http\Controllers\Api\User\TrainQuizController@updateQuiz')->middleware('user_auth', 'has_store');
            //Xóa bài thi câu hỏi trắc nghiệm
            Route::delete('/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}', 'App\Http\Controllers\Api\User\TrainQuizController@deleteQuiz')->middleware('user_auth', 'has_store');
            //Lấy bài thi câu hỏi trắc nghiệm
            Route::get('/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}', 'App\Http\Controllers\Api\User\TrainQuizController@getOneQuiz')->middleware('user_auth', 'has_store');

            //Thêm câu hỏi bài thi
            Route::post('/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}/questions', 'App\Http\Controllers\Api\User\TrainQuizController@createQuestion')->middleware('user_auth', 'has_store');
            //Sửa bài câu hỏi bài thi
            Route::put('/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}/questions/{question_id}', 'App\Http\Controllers\Api\User\TrainQuizController@updateQuestion')->middleware('user_auth', 'has_store');
            //Xóa bài câu hỏi bài thi
            Route::delete('/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}/questions/{question_id}', 'App\Http\Controllers\Api\User\TrainQuizController@deleteQuestion')->middleware('user_auth', 'has_store');


            //Thêm bài học
            Route::post('/{store_code}/train_lessons', 'App\Http\Controllers\Api\User\TrainChaptersController@createLesson')->middleware('user_auth', 'has_store');
            //Sửa bài học
            Route::put('/{store_code}/train_lessons/{train_lesson_id}', 'App\Http\Controllers\Api\User\TrainChaptersController@updateLesson')->middleware('user_auth', 'has_store');
            //Xóa bài học
            Route::delete('/{store_code}/train_lessons/{train_lesson_id}', 'App\Http\Controllers\Api\User\TrainChaptersController@deleteLesson')->middleware('user_auth', 'has_store');
            //Sắp bài học
            Route::put('/{store_code}/train_lessons_sort', 'App\Http\Controllers\Api\User\TrainChaptersController@sortLesson')->middleware('user_auth', 'has_store');

            //Up 1 ảnh
            Route::post('/{store_code}/images', 'App\Http\Controllers\Api\User\UploadImageController@uploadv2')->middleware('user_auth', 'has_store');
            //Danh sách thư viện ảnh
            Route::get('/{store_code}/images', 'App\Http\Controllers\Api\User\UploadImageController@getAll')->middleware('user_auth', 'has_store');
            //Danh sách thư viện ảnh
            Route::get('/{store_code}/images/{image_id}', 'App\Http\Controllers\Api\User\UploadImageController@getOne')->middleware('user_auth', 'has_store');
            //Cập nhật thông tin 1 ảnh
            Route::put('/{store_code}/images/{image_id}', 'App\Http\Controllers\Api\User\UploadImageController@update')->middleware('user_auth', 'has_store');
            //Xóa 1 ảnh
            Route::delete('/{store_code}/images/{image_id}', 'App\Http\Controllers\Api\User\UploadImageController@update')->middleware('user_auth', 'has_store');


            //Nhóm kh
            Route::post('/{store_code}/group_customers', 'App\Http\Controllers\Api\User\GroupControllerController@create')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/group_customers', 'App\Http\Controllers\Api\User\GroupControllerController@getAll')->middleware('user_auth', 'has_store');

            Route::get('/{store_code}/group_customers/{group_customer_id}/customers', 'App\Http\Controllers\Api\User\GroupControllerController@getListCustomersByGroup')->middleware('user_auth', 'has_store');

            Route::delete('/{store_code}/group_customers/{group_customer_id}', 'App\Http\Controllers\Api\User\GroupControllerController@delete')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/group_customers/{group_customer_id}', 'App\Http\Controllers\Api\User\GroupControllerController@update')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/group_customers/{group_customer_id}', 'App\Http\Controllers\Api\User\GroupControllerController@getOne')->middleware('user_auth', 'has_store');

            //Sàn thương mại cho cộng tác viên
            //Danh mục
            Route::get('/{store_code}/ecommerce_ctv/categories', 'App\Http\Controllers\Api\Admin\Ecommerce\CategoryController@getAll')->middleware('user_auth', 'has_store');

            //Sản phẩm
            Route::get('/{store_code}/ecommerce/products_ctv', 'App\Http\Controllers\Api\User\Ecommerce\ProductController@getAll')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/ecommerce/products_ctv', 'App\Http\Controllers\Api\User\Ecommerce\ProductController@add_remove')->middleware('user_auth', 'has_store');

            //Vòng quay mini game
            Route::post('/{store_code}/spin_wheels', 'App\Http\Controllers\Api\User\SpinWheelController@create')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/spin_wheels/{spin_wheel_id}', 'App\Http\Controllers\Api\User\SpinWheelController@update')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/spin_wheels/{spin_wheel_id}', 'App\Http\Controllers\Api\User\SpinWheelController@delete')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/spin_wheels', 'App\Http\Controllers\Api\User\SpinWheelController@getAll')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/spin_wheels/{spin_wheel_id}', 'App\Http\Controllers\Api\User\SpinWheelController@getOne')->middleware('user_auth', 'has_store');

            //Phần thưởng vòng quay mini game
            Route::post('/{store_code}/spin_wheels/{spin_wheel_id}/gift_spin_wheels', 'App\Http\Controllers\Api\User\GiftSpinWheelController@create')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/spin_wheels/{spin_wheel_id}/gift_spin_wheels/{gift_spin_wheel_id}', 'App\Http\Controllers\Api\User\GiftSpinWheelController@update')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/spin_wheels/{spin_wheel_id}/gift_spin_wheels/{gift_spin_wheel_id}', 'App\Http\Controllers\Api\User\GiftSpinWheelController@delete')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/spin_wheels/{spin_wheel_id}/gift_spin_wheels', 'App\Http\Controllers\Api\User\GiftSpinWheelController@getAll')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/spin_wheels/{spin_wheel_id}/gift_spin_wheels/{gift_spin_wheel_id}', 'App\Http\Controllers\Api\User\GiftSpinWheelController@getOne')->middleware('user_auth', 'has_store');


            Route::get('/{store_code}/staff_sale/overview', 'App\Http\Controllers\Api\User\SaleController@getOverview')->middleware('user_auth', 'has_store');

            //Cấu hình Sale
            Route::get('/{store_code}/staff_sale_configs', 'App\Http\Controllers\Api\User\SaleController@getConfig')->middleware('user_auth', 'has_store');
            //Thêm khách hàng vào sale 
            Route::post('/{store_code}/staff_sale_configs/add_customers_to_sale', 'App\Http\Controllers\Api\User\SaleController@add_customers_to_sale')->middleware('user_auth', 'has_store');
            //Thông tin tổng quan của 1 sale
            Route::get('/{store_code}/staff_sale_configs/overview_one_sale', 'App\Http\Controllers\Api\User\SaleController@getOverview')->middleware('user_auth', 'has_store');

            //Danh sách top sale
            Route::get('/{store_code}/staff_sale_configs/staff_sale_top', 'App\Http\Controllers\Api\User\SaleController@getAllStaffSaleTopShare')->middleware('user_auth', 'has_store');
            //Danh sách customer của top sale
            Route::get('/{store_code}/staff_sale_configs/get_ids_customer_staff_sale_top', 'App\Http\Controllers\Api\User\SaleController@getIdsCustomerInSaleTopShare')->middleware('user_auth', 'has_store');

            //Cập nhật Sale
            Route::post('/{store_code}/staff_sale_configs', 'App\Http\Controllers\Api\User\SaleController@updateConfig')->middleware('user_auth', 'has_store');
            //DS bậc thang
            Route::get('/{store_code}/staff_sale_configs/bonus_steps', 'App\Http\Controllers\Api\User\SaleController@getStepBonusAll')->middleware('user_auth', 'has_store');
            //Thêm 1 bậc thang
            Route::post('/{store_code}/staff_sale_configs/bonus_steps', 'App\Http\Controllers\Api\User\SaleController@createStep')->middleware('user_auth', 'has_store');
            //Cập nhật 1 bậc thang
            Route::put('/{store_code}/staff_sale_configs/bonus_steps/{step_id}', 'App\Http\Controllers\Api\User\SaleController@updateOneStep')->middleware('user_auth', 'has_store');
            //Delete 1 bậc thang
            Route::delete('/{store_code}/staff_sale_configs/bonus_steps/{step_id}', 'App\Http\Controllers\Api\User\SaleController@deleteOneStep')->middleware('user_auth', 'has_store');

            //Khách hàng của sale
            Route::get('/{store_code}/staff_sale/customers', 'App\Http\Controllers\Api\User\CustomerController@getAll')->middleware('user_auth', 'has_store', 'is_sale_staff');
            Route::get('/{store_code}/staff_sale/customers/{customer_id}', 'App\Http\Controllers\Api\User\CustomerController@getOne')->middleware('user_auth', 'has_store', 'is_sale_staff');
            Route::post('/{store_code}/staff_sale/customers', 'App\Http\Controllers\Api\User\CustomerController@create')->middleware('user_auth', 'has_store', 'is_sale_staff');
            Route::put('/{store_code}/staff_sale/customers/{customer_id}', 'App\Http\Controllers\Api\User\CustomerController@update')->middleware('user_auth', 'has_store', 'is_sale_staff');
            Route::delete('/{store_code}/staff_sale/customers/{customer_id}', 'App\Http\Controllers\Api\User\CustomerController@delete')->middleware('user_auth', 'has_store', 'is_sale_staff');


            //Đoán số mini game
            Route::post('/{store_code}/guess_numbers', 'App\Http\Controllers\Api\User\GuessNumberController@create')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/guess_numbers/{guess_number_id}', 'App\Http\Controllers\Api\User\GuessNumberController@update')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/guess_numbers/{guess_number_id}', 'App\Http\Controllers\Api\User\GuessNumberController@delete')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/guess_numbers', 'App\Http\Controllers\Api\User\GuessNumberController@getAll')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/guess_numbers/{guess_number_id}', 'App\Http\Controllers\Api\User\GuessNumberController@getOne')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/guess_numbers/{guess_number_id}/history_predicts', 'App\Http\Controllers\Api\User\GuessNumberController@getHistoryResult')->middleware('user_auth', 'has_store');

            //Gửi zalo
            Route::post('/zalo', 'App\Http\Controllers\Api\User\Ecommerce\Zalo\ZaloController@zalo');


            //Danh sách token
            Route::get('/{store_code}/ecommerce/connect/list', 'App\Http\Controllers\Api\User\Ecommerce\Connect\ConnectController@connect_list')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/ecommerce/connect/list/{shop_id}', 'App\Http\Controllers\Api\User\Ecommerce\Connect\ConnectController@getOne')->middleware('user_auth', 'has_store');
            Route::put('/{store_code}/ecommerce/connect/list/{shop_id}', 'App\Http\Controllers\Api\User\Ecommerce\Connect\ConnectController@updateOne')->middleware('user_auth', 'has_store');
            Route::delete('/{store_code}/ecommerce/connect/list/{shop_id}', 'App\Http\Controllers\Api\User\Ecommerce\Connect\ConnectController@deleteOne')->middleware('user_auth', 'has_store');


            //Connect sàn
            Route::get('/ecommerce/connect/tiki', 'App\Http\Controllers\Api\User\Ecommerce\Connect\TikiController@connect_tiki');
            Route::get('/ecommerce/connect/lazada', 'App\Http\Controllers\Api\User\Ecommerce\Connect\LazadaController@connect_lazada');
            Route::get('/ecommerce/connect/tiktok', 'App\Http\Controllers\Api\User\Ecommerce\Connect\TiktokController@connect_tiktok');
            Route::get('/ecommerce/connect/shopee', 'App\Http\Controllers\Api\User\Ecommerce\Connect\ShopeeController@connect_shopee');
            Route::post('/{store_code}/ecommerce/connect/sendo', 'App\Http\Controllers\Api\User\Ecommerce\Connect\SendoController@connect_sendo')->middleware('user_auth', 'has_store');

            //lấy sản phẩm từ các sàn 
            Route::post('/{store_code}/ecommerce/products/sync', 'App\Http\Controllers\Api\User\Ecommerce\Product\SyncProductController@syncProductEcommerce')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/ecommerce/db/products', 'App\Http\Controllers\Api\User\Ecommerce\Product\SyncProductController@getAllProductEcommerce')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/ecommerce/db/products_save', 'App\Http\Controllers\Api\User\Ecommerce\Product\SyncProductController@saveProductToDB')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/ecommerce/db/products/{ecommerce_product_id}', 'App\Http\Controllers\Api\User\Ecommerce\Product\SyncProductController@getOneProductEcommerce')->middleware('user_auth', 'has_store');

            //Chỉnh sửa sản phẩm 
            Route::put('/{store_code}/ecommerce/db/products/{product_id}', 'App\Http\Controllers\Api\User\Ecommerce\Product\SyncProductController@editProductEcommerce')->middleware('user_auth', 'has_store');
            //Xóa sản phẩm 
            Route::delete('/{store_code}/ecommerce/db/products', 'App\Http\Controllers\Api\User\Ecommerce\Product\SyncProductController@deleteProductEcommerce')->middleware('user_auth', 'has_store');

            //lấy đơn hàng từ các sàn 
            Route::post('/{store_code}/ecommerce/orders/sync', 'App\Http\Controllers\Api\User\Ecommerce\Order\SyncOrderController@syncOrderEcommerce')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/ecommerce/db/orders', 'App\Http\Controllers\Api\User\Ecommerce\Order\SyncOrderController@getAllOrderEcommerce')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/ecommerce/db/orders/{order_id}', 'App\Http\Controllers\Api\User\Ecommerce\Order\SyncOrderController@getOrderDetailEcommerce')->middleware('user_auth', 'has_store');
            //Chỉnh sửa đơn hàng
            Route::put('/{store_code}/ecommerce/db/orders/{order_id}', 'App\Http\Controllers\Api\User\Ecommerce\Order\SyncOrderController@editOrderEcommerce')->middleware('user_auth', 'has_store');

            //lấy danh sánh kho
            Route::get('/{store_code}/ecommerce/warehouses', 'App\Http\Controllers\Api\User\Ecommerce\Warehouse\WarehouseController@getAll')->middleware('user_auth', 'has_store');
            // cập nhật kho
            Route::put('/{store_code}/ecommerce/warehouses/{warehouse_id}', 'App\Http\Controllers\Api\User\Ecommerce\Warehouse\WarehouseController@updateOne')->middleware('user_auth', 'has_store');

            // Checkin đại lý
            Route::get('/{store_code}/sale_visit_agencies/agencies/{agency_id}', 'App\Http\Controllers\Api\User\SaleVisitAgencyController@getOneAgency')->middleware('user_auth', 'has_store', 'check_staff');
            Route::get('/{store_code}/sale_visit_agencies/agencies', 'App\Http\Controllers\Api\User\SaleVisitAgencyController@getAgency')->middleware('user_auth', 'has_store', 'check_staff');
            Route::put('/{store_code}/sale_visit_agencies/{sale_visit_agency_id}', 'App\Http\Controllers\Api\User\SaleVisitAgencyController@checkout')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/sale_visit_agencies', 'App\Http\Controllers\Api\User\SaleVisitAgencyController@checkIn')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/sale_visit_agencies', 'App\Http\Controllers\Api\User\SaleVisitAgencyController@index')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/sale_visit_agencies/{sale_visit_agency_id}', 'App\Http\Controllers\Api\User\SaleVisitAgencyController@getOneSaleVisitAgency')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/report_sale_visit_agencies', 'App\Http\Controllers\Api\User\SaleVisitAgencyController@reportSaleVisitAgency')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/overview_sale_visit_agencies', 'App\Http\Controllers\Api\User\SaleVisitAgencyController@overviewSaleVisitAgencyIndex')->middleware('user_auth', 'has_store');

            // Lịch sử OTP
            Route::get('/{store_code}/otp_histories', 'App\Http\Controllers\Api\User\HistoryOtpController@getAll')->middleware('user_auth', 'has_store');
        });

        ///////////////////// USER /////////////////////

        //Up 1 ảnh
        Route::post('images', 'App\Http\Controllers\Api\User\UploadImageController@upload')->middleware('user_auth');
        //Up 1 video
        Route::post('videos', 'App\Http\Controllers\Api\User\UploadVideoController@upload')->middleware('user_auth');

        //getProfile
        Route::get('/profile', 'App\Http\Controllers\Api\User\ProfileController@getProfile')->middleware('user_auth');
        //updateProfile
        Route::put('/profile', 'App\Http\Controllers\Api\User\ProfileController@updateProfile')->middleware('user_auth');


        //App Theme
        Route::prefix('app-theme')->group(function () {
            Route::post('/{store_code}', 'App\Http\Controllers\Api\User\AppThemeController@update')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}', 'App\Http\Controllers\Api\User\AppThemeController@getAppTheme')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/layout_sort', 'App\Http\Controllers\Api\User\AppThemeController@update_layout_sort')->middleware('user_auth', 'has_store');
            Route::post('/{store_code}/home_buttons', 'App\Http\Controllers\Api\User\AppThemeController@update_home_buttons')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}/home_buttons', 'App\Http\Controllers\Api\User\AppThemeController@get_home_buttons')->middleware('user_auth', 'has_store');
        });


        //Web Theme
        Route::prefix('web-theme')->group(function () {
            Route::post('/{store_code}', 'App\Http\Controllers\Api\User\WebThemeController@update')->middleware('user_auth', 'has_store');
            Route::get('/{store_code}', 'App\Http\Controllers\Api\User\WebThemeController@getWebTheme')->middleware('user_auth', 'has_store');
        });

        //device_token_user
        Route::prefix('device_token_user')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\User\UserDeviceTokenController@updateDeviceTokenUser')->middleware('user_auth');
        });



        ///////////////////// CUSTOMER /////////////////////

        //Customer ko cần customer_token
        Route::prefix('customer')->group(function () {


            //Gui otp
            Route::post('/{store_code}/send_otp', 'App\Http\Controllers\Api\User\OtpSmsController@send_v2')->middleware('has_customer_store');
            //Cài đặt otp
            Route::get('/{store_code}/otp_configs', 'App\Http\Controllers\Api\User\OtpConfigController@index')->middleware('has_customer_store');
            //Đăng ký
            Route::post('/{store_code}/register', 'App\Http\Controllers\Api\Customer\CustomerRegisterController@register')->middleware('has_customer_store');
            //Đăng nhập
            Route::post('/{store_code}/login', 'App\Http\Controllers\Api\Customer\CustomerLoginController@login')->middleware('has_customer_store');
            //Lấy lại mật khẩu
            Route::post('/{store_code}/reset_password', 'App\Http\Controllers\Api\Customer\CustomerLoginController@reset_password')->middleware('has_customer_store');

            //send email otp
            Route::post('/{store_code}/send_email_otp', 'App\Http\Controllers\Api\Customer\CustomerSendMailOtpController@send_email_otp')->middleware('has_customer_store');


            //Kiểm tra tồn tại
            Route::post('/{store_code}/login/check_exists', 'App\Http\Controllers\Api\Customer\CustomerLoginController@check_exists')->middleware('has_customer_store');

            //App-Theme
            Route::get('/{store_code}/app-theme', 'App\Http\Controllers\Api\Customer\CustomerAppWebThemeController@getAppTheme')->middleware('has_customer_store');
            Route::get('/{store_code}/home_buttons', 'App\Http\Controllers\Api\Customer\CustomerAppWebThemeController@get_home_buttons')->middleware('has_customer_store');
            //Web Theme
            Route::get('/{store_code}/web-theme', 'App\Http\Controllers\Api\Customer\CustomerAppWebThemeController@getWebTheme')->middleware('has_customer_store');

            //Chi nhánh
            Route::get('/{store_code}/branches', 'App\Http\Controllers\Api\Customer\CustomerBranchController@getAll')->middleware('has_customer_store');


            //Product
            Route::get('/{store_code}/slugs/{slug}', 'App\Http\Controllers\Api\Customer\CustomerProductController@getSlug')->middleware('has_customer_store', 'get_customer_auth');
            Route::get('/{store_code}/products', 'App\Http\Controllers\Api\Customer\CustomerProductController@getAll')->middleware('has_customer_store', 'get_customer_auth');
            Route::get('/{store_code}/products/{id}', 'App\Http\Controllers\Api\Customer\CustomerProductController@getOneProduct')->middleware('has_customer_store', 'get_customer_auth');
            //Danh sách sp tương tự
            Route::get('/{store_code}/products/{id}/similar_products', 'App\Http\Controllers\Api\Customer\CustomerProductController@getAllSimilar')->middleware('has_customer_store', 'get_customer_auth');

            //Quét sản phẩm
            Route::post('/{store_code}/scan_product', 'App\Http\Controllers\Api\Customer\CustomerScanController@productByBarcode')->middleware('has_customer_store', 'get_customer_auth');

            //Category
            Route::get('/{store_code}/categories', 'App\Http\Controllers\Api\Customer\CustomerCategoryController@getAll')->middleware('has_customer_store');

            //Store
            Route::get('/{store_code}', 'App\Http\Controllers\Api\Customer\CustomerStoreController@getOneStore')->middleware('has_customer_store');

            //Voucher
            Route::get('/{store_code}/vouchers', 'App\Http\Controllers\Api\Customer\CustomerVoucherController@getAllAvailable')->middleware('has_customer_store', 'get_customer_auth');
            Route::get('/{store_code}/vouchers/{voucher_id}/products', 'App\Http\Controllers\Api\Customer\CustomerVoucherController@getProductVoucherAvailable')->middleware('has_customer_store', 'get_customer_auth');

            //Combo
            Route::get('/{store_code}/combos', 'App\Http\Controllers\Api\Customer\CustomerComboController@getAllAvailable')->middleware('has_customer_store', 'get_customer_auth');
            //bonus_products
            Route::get('/{store_code}/bonus_products', 'App\Http\Controllers\Api\Customer\CustomerBonusProductController@getAllAvailable')->middleware('has_customer_store', 'get_customer_auth');

            //Home app
            Route::get('/{store_code}/home_app', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeApp')->middleware('has_customer_store', 'get_customer_auth');


            //Home layouts
            Route::get('/{store_code}/home_app/layouts', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeAppLayouts')->middleware('has_customer_store', 'get_customer_auth');
            //Home app button
            Route::get('/{store_code}/home_app/buttons', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeAppButtons')->middleware('has_customer_store', 'get_customer_auth');
            //data home web bannres
            Route::get('/{store_code}/home_web/banners', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeWebBanners')->middleware('has_customer_store', 'get_customer_auth');
            //data home web discount
            Route::get('/{store_code}/home_web/product_discounts', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeWebProductDiscount')->middleware('has_customer_store', 'get_customer_auth');
            //data home top sales
            Route::get('/{store_code}/home_web/product_top_sales', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeWebProductTopSales')->middleware('has_customer_store', 'get_customer_auth');
            //data home news
            Route::get('/{store_code}/home_web/product_news', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeWebProductNews')->middleware('has_customer_store', 'get_customer_auth');
            //data home product_by_category
            Route::get('/{store_code}/home_web/product_by_category', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeWebProductWithCategory')->middleware('has_customer_store', 'get_customer_auth');
            //data home posts_new
            Route::get('/{store_code}/home_web/posts_new', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeWebPostNews')->middleware('has_customer_store', 'get_customer_auth');
            //data home posts_with_category
            Route::get('/{store_code}/home_web/posts_with_category', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeWebPostWithCategory')->middleware('has_customer_store', 'get_customer_auth');
            //data home ads
            Route::get('/{store_code}/home_web/ads', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeWebAds')->middleware('has_customer_store', 'get_customer_auth');
            //data home app ads
            Route::get('/{store_code}/home_app/ads', 'App\Http\Controllers\Api\Customer\CustomerHomeController@getHomeAppAds')->middleware('has_customer_store', 'get_customer_auth');




            //Posts
            Route::get('/{store_code}/posts', 'App\Http\Controllers\Api\Customer\CustomerPostController@getAll')->middleware('has_customer_store');
            Route::get('/{store_code}/posts/{id}', 'App\Http\Controllers\Api\Customer\CustomerPostController@getOnePost')->middleware('has_customer_store', 'get_customer_auth');

            //CategoryPost
            Route::get('/{store_code}/post_categories', 'App\Http\Controllers\Api\Customer\CustomerCategoryPostController@getAll')->middleware('has_customer_store');

            //Danh sách đánh giá
            Route::get('/{store_code}/products/{product_id}/reviews', 'App\Http\Controllers\Api\Customer\CustomerReviewsController@getInProductAll')->middleware('has_customer_store', 'get_customer_auth');

            //Danh sách thuộc tính tìm kiếm
            Route::get('/{store_code}/attribute_searches', 'App\Http\Controllers\Api\Customer\CustomerAttributeSearchController@getAll')->middleware('has_customer_store');



            //device_token_customer
            Route::post('/{store_code}/device_token_customer', 'App\Http\Controllers\Api\Customer\CustomerDeviceTokenController@updateDeviceTokenCustomer')->middleware('has_customer_store', 'get_customer_auth');

            //dynamic link
            Route::get('/{store_code}/dynamic_links', 'App\Http\Controllers\Api\Customer\CustomerDynamicLinkController@getDynamicLink')->middleware('has_customer_store', 'get_customer_auth');
            //handle dynamic link
            Route::post('/{store_code}/dynamic_links/{dynamic_link_id}', 'App\Http\Controllers\Api\Customer\CustomerDynamicLinkController@handle')->middleware('has_customer_store', 'get_customer_auth');
        });

        //Customer cần customer-token
        Route::prefix('customer')->group(function () {
            //Thay đổi mật khẩu
            Route::post('/{store_code}/change_password', 'App\Http\Controllers\Api\Customer\CustomerLoginController@change_password')->middleware('has_customer_store', 'customer_auth');

            //getProfile
            Route::get('/{store_code}/profile', 'App\Http\Controllers\Api\Customer\CustomerProfileController@getProfile')->middleware('has_customer_store', 'customer_auth');
            //updateProfile
            Route::put('/{store_code}/profile', 'App\Http\Controllers\Api\Customer\CustomerProfileController@updateProfile')->middleware('has_customer_store', 'customer_auth');
            //updatePhoneNumberRefer
            Route::put('/{store_code}/profile/referral_phone_number', 'App\Http\Controllers\Api\Customer\CustomerProfileController@updateReferralPhoneNumber')->middleware('has_customer_store', 'customer_auth');
            //getPhoneNumberRefer
            Route::get('/{store_code}/profile/get_all_referral', 'App\Http\Controllers\Api\Customer\CustomerProfileController@getAllReferralPhoneNumber')->middleware('has_customer_store', 'customer_auth');


            //Chat cho user
            Route::post('/{store_code}/messages', 'App\Http\Controllers\RedisRealtimeController@customerSendToUser')->middleware('has_customer_store', 'customer_auth');

            //Danh sách tin nhắn với user
            Route::get('/{store_code}/messages', 'App\Http\Controllers\RedisRealtimeController@getAllMessageOfCustomer')->middleware('has_customer_store', 'customer_auth');

            //Up 1 ảnh
            Route::post('/{store_code}/images', 'App\Http\Controllers\Api\Customer\UploadImageController@upload')->middleware('has_customer_store', 'customer_auth');

            //Up 1 video
            Route::post('/{store_code}/video', 'App\Http\Controllers\Api\Customer\UploadVideoController@upload')->middleware('has_customer_store', 'customer_auth');

            //Đăng ký làm CTV
            Route::post('/{store_code}/collaborator/reg', 'App\Http\Controllers\Api\Customer\CustomerCollaboratorController@regCollaborator')->middleware('has_customer_store', 'customer_auth');
            //Cập nhật thông tin cộng tác viên
            Route::post('/{store_code}/collaborator/account', 'App\Http\Controllers\Api\Customer\CustomerCollaboratorController@editProfile')->middleware('has_customer_store', 'customer_auth');
            //Thông tin tài khoản
            Route::get('/{store_code}/collaborator/account', 'App\Http\Controllers\Api\Customer\CustomerCollaboratorController@info_account')->middleware('has_customer_store', 'customer_auth');
            //Danh sách đơn hàng CTV
            Route::get('/{store_code}/collaborator/orders', 'App\Http\Controllers\Api\Customer\CustomerOrderController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Lịch sử thay đổi số dư
            Route::get('/{store_code}/collaborator/history_balace', 'App\Http\Controllers\Api\Customer\CustomerChangeBalanceCollaboratorsController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Tiền thưởng tháng
            Route::get('/{store_code}/collaborator/bonus', 'App\Http\Controllers\Api\Customer\CustomerCollaboratorController@list_bonus_with_month')->middleware('has_customer_store', 'customer_auth');
            //Lấy tiền bonus
            Route::post('/{store_code}/collaborator/bonus/take', 'App\Http\Controllers\Api\Customer\CustomerCollaboratorController@take_bonus')->middleware('has_customer_store', 'customer_auth');
            //Nhận tiền thưởng tháng
            Route::post('/{store_code}/collaborator/request_payment', 'App\Http\Controllers\Api\Customer\CustomerCollaboratorPayController@request_payment')->middleware('has_customer_store', 'customer_auth');
            //Thông tin tổng quan
            Route::get('/{store_code}/collaborator/info', 'App\Http\Controllers\Api\Customer\CustomerCollaboratorController@info_overview')->middleware('has_customer_store', 'customer_auth');
            //danh sách người giới thiệu
            Route::get('/{store_code}/collaborator/get_all_referral_ctv', 'App\Http\Controllers\Api\Customer\CustomerCollaboratorController@getAllReferralPhoneNumberCTV')->middleware('has_customer_store', 'customer_auth');

            //Đăng ký làm Sale
            Route::post('/{store_code}/sale/reg', 'App\Http\Controllers\Api\Customer\CustomerSaleController@regSaleCustomer')->middleware('has_customer_store', 'customer_auth');
            //Cập nhật thông tin cộng tác viên
            Route::post('/{store_code}/sale/account', 'App\Http\Controllers\Api\Customer\CustomerSaleController@editProfile')->middleware('has_customer_store', 'customer_auth');
            //Thông tin tài khoản
            Route::get('/{store_code}/sale/account', 'App\Http\Controllers\Api\Customer\CustomerSaleController@info_account')->middleware('has_customer_store', 'customer_auth');
            //Danh sách đơn hàng Sale
            Route::get('/{store_code}/sale/orders', 'App\Http\Controllers\Api\Customer\CustomerOrderController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Lịch sử thay đổi số dư
            Route::get('/{store_code}/sale/history_balace', 'App\Http\Controllers\Api\Customer\CustomerChangeBalanceSaleController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Tiền thưởng tháng
            Route::get('/{store_code}/sale/bonus', 'App\Http\Controllers\Api\Customer\CustomerSaleController@list_bonus_with_month')->middleware('has_customer_store', 'customer_auth');
            //Lấy tiền bonus
            Route::post('/{store_code}/sale/bonus/take', 'App\Http\Controllers\Api\Customer\CustomerSaleController@take_bonus')->middleware('has_customer_store', 'customer_auth');
            // //Nhận tiền thưởng tháng
            // Route::post('/{store_code}/sale/request_payment', 'App\Http\Controllers\Api\CuFFstomer\CustomerSalePayController@request_payment')->middleware('has_customer_store', 'customer_auth');
            //Thông tin tổng quan
            Route::get('/{store_code}/sale/info', 'App\Http\Controllers\Api\Customer\CustomerSaleController@info_overview')->middleware('has_customer_store', 'customer_auth');


            //Đăng ký làm Đại lý
            Route::post('/{store_code}/agency/reg', 'App\Http\Controllers\Api\Customer\CustomerAgencyController@regAgency')->middleware('has_customer_store', 'customer_auth');
            //Cập nhật thông tin Đại lý
            Route::post('/{store_code}/agency/account', 'App\Http\Controllers\Api\Customer\CustomerAgencyController@editProfile')->middleware('has_customer_store', 'customer_auth');
            //Thông tin tài khoản
            Route::get('/{store_code}/agency/account', 'App\Http\Controllers\Api\Customer\CustomerAgencyController@info_account')->middleware('has_customer_store', 'customer_auth');
            //Danh sách đơn hàng Đại lý
            Route::get('/{store_code}/agency/orders', 'App\Http\Controllers\Api\Customer\CustomerOrderController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Danh sách đơn hàng hoa hồng Đại lý
            Route::get('/{store_code}/agency_ctv/orders', 'App\Http\Controllers\Api\Customer\CustomerOrderController@getAll')->middleware('has_customer_store', 'customer_auth');

            //Lịch sử thay đổi số dư
            Route::get('/{store_code}/agency/history_balace', 'App\Http\Controllers\Api\Customer\CustomerChangeBalanceAgenciesController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Tiền thưởng tháng
            Route::get('/{store_code}/agency/bonus', 'App\Http\Controllers\Api\Customer\CustomerAgencyController@list_bonus_with_month')->middleware('has_customer_store', 'customer_auth');
            //Lấy tiền bonus
            Route::post('/{store_code}/agency/bonus/take', 'App\Http\Controllers\Api\Customer\CustomerAgencyController@take_bonus')->middleware('has_customer_store', 'customer_auth');
            //Nhận tiền thưởng tháng
            Route::post('/{store_code}/agency/request_payment', 'App\Http\Controllers\Api\Customer\CustomerAgencyPayController@request_payment')->middleware('has_customer_store', 'customer_auth');
            //Thông tin tổng quan
            Route::get('/{store_code}/agency/info', 'App\Http\Controllers\Api\Customer\CustomerAgencyController@info_overview')->middleware('has_customer_store', 'customer_auth');
            //Báo cáo doanh thu tổng quan Đại lý
            Route::get('/{store_code}/agency/report', 'App\Http\Controllers\Api\User\ReportController@overview')->middleware('has_customer_store', 'customer_auth');
            //Báo cáo doanh thu hoa hồng Đại lý
            Route::get('/{store_code}/agency_ctv/report', 'App\Http\Controllers\Api\User\ReportController@overview')->middleware('has_customer_store', 'customer_auth');
            //danh sách người giới thiệu đl
            Route::get('/{store_code}/agency/get_all_referral_agency', 'App\Http\Controllers\Api\Customer\CustomerAgencyController@getAllReferralPhoneNumberAgency')->middleware('has_customer_store', 'customer_auth');


            //Danh sách thông báo đẩy
            Route::get('/{store_code}/notifications_history', 'App\Http\Controllers\Api\Customer\CustomerNotificationController@getAll')->middleware('has_customer_store', 'get_customer_auth');
            //đọc hết
            Route::get('/{store_code}/notifications_history/read_all', 'App\Http\Controllers\Api\Customer\CustomerNotificationController@readAll')->middleware('has_customer_store', 'get_customer_auth');


            //Cập nhật thông tin giỏ hàng
            Route::post('/{store_code}/carts', 'App\Http\Controllers\Api\Customer\CustomerCartController@updateCartInfo')->middleware('has_customer_store', 'get_customer_auth_cart');
            Route::post('/{store_code}/carts/v1', 'App\Http\Controllers\Api\Customer\CustomerCartController@updateCartInfoV1')->middleware('has_customer_store', 'get_customer_auth_cart');
            //Thêm sản phẩm giỏ hàng
            Route::post('/{store_code}/carts/items', 'App\Http\Controllers\Api\Customer\CustomerCartController@addLineItem')->middleware('has_customer_store', 'get_customer_auth_cart');
            Route::post('/{store_code}/carts/items/v1', 'App\Http\Controllers\Api\Customer\CustomerCartController@addLineItemV1')->middleware('has_customer_store', 'get_customer_auth_cart');
            //Cập nhật sản phẩm giỏ hàng
            Route::put('/{store_code}/carts/items', 'App\Http\Controllers\Api\Customer\CustomerCartController@updateLineItem')->middleware('has_customer_store', 'get_customer_auth_cart');
            Route::put('/{store_code}/carts/items/v1', 'App\Http\Controllers\Api\Customer\CustomerCartController@updateLineItemV1')->middleware('has_customer_store', 'get_customer_auth_cart');
            //Tính phí ship hàng
            Route::post('/{store_code}/carts/calculate_fee', 'App\Http\Controllers\Api\Customer\CustomerCartController@calculate_fee')->middleware('has_customer_store', 'get_customer_auth_cart');
            Route::post('/{store_code}/carts/calculate_fee/{partner_id}', 'App\Http\Controllers\Api\Customer\CustomerCartController@calculate_fee_by_partner_id')->middleware('has_customer_store', 'get_customer_auth_cart');


            //Địa chỉ giao hàng
            Route::post('/{store_code}/address', 'App\Http\Controllers\Api\Customer\CustomerAddressController@create')->middleware('has_customer_store', 'customer_auth');
            Route::put('/{store_code}/address/{customer_address_id}', 'App\Http\Controllers\Api\Customer\CustomerAddressController@update')->middleware('has_customer_store', 'customer_auth');
            Route::get('/{store_code}/address', 'App\Http\Controllers\Api\Customer\CustomerAddressController@getAll')->middleware('has_customer_store', 'customer_auth');
            Route::delete('/{store_code}/address/{customer_address_id}', 'App\Http\Controllers\Api\Customer\CustomerAddressController@deleteOneStoreAddress')->middleware('has_customer_store', 'customer_auth');

            //Caculate free ship
            Route::post('/{store_code}/shipment/fee', 'App\Http\Controllers\Api\Customer\CustomerShipperController@caculate_fee')->middleware('has_customer_store', 'customer_auth');
            //Danh sách nhà vận chuyển có thể sử dụng
            Route::post('/{store_code}/shipment/list_shipper', 'App\Http\Controllers\Api\Customer\CustomerShipperController@list_shipper')->middleware('has_customer_store');

            //Tất cả phương thức thanh toán
            Route::get('/{store_code}/payment_methods', 'App\Http\Controllers\Api\Customer\CustomerPaymentMethodController@getAll')->middleware('has_customer_store');

            //Thanh toán
            Route::get('/{store_code}/purchase/pay/{order_code}', 'App\Http\Controllers\PaymentMethod\PayController@pay')->middleware('has_customer_store', 'has_order', 'check_order_paid');
            //Chuyển khoản thủ công
            Route::get('/{store_code}/purchase/pay/{order_code}/bank', 'App\Http\Controllers\PaymentMethod\PayBankController@create')->middleware('has_customer_store', 'has_order', 'check_order_paid');
            //VNPay
            Route::get('/{store_code}/purchase/pay/{order_code}/vn_pay', 'App\Http\Controllers\PaymentMethod\VNPayController@create')->middleware('has_customer_store', 'has_order', 'check_order_paid');
            Route::get('/{store_code}/purchase/return/vn_pay', 'App\Http\Controllers\PaymentMethod\VNPayController@return')->middleware('has_customer_store');
            Route::get('/{store_code}/purchase/ipn/vn_pay', 'App\Http\Controllers\PaymentMethod\VNPayController@return')->middleware('has_customer_store');
            //ONEPay
            Route::get('/{store_code}/purchase/pay/{order_code}/one_pay', 'App\Http\Controllers\PaymentMethod\OnePayController@create')->middleware('has_customer_store', 'has_order', 'check_order_paid');
            Route::get('/{store_code}/purchase/return/one_pay', 'App\Http\Controllers\PaymentMethod\OnePayController@return')->middleware('has_customer_store');
            //Ngân lượng
            Route::get('/{store_code}/purchase/pay/{order_code}/ngan_luong', 'App\Http\Controllers\PaymentMethod\NganLuongController@create')->middleware('has_customer_store', 'has_order', 'check_order_paid');
            Route::get('/{store_code}/purchase/return/ngan_luong', 'App\Http\Controllers\PaymentMethod\NganLuongController@return')->middleware('has_customer_store');

            // Momo
            Route::get('/{store_code}/purchase/pay/{order_code}/momo', function (Request $request) {
                $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
                $order_code = $request->order->order_code;
                $store_code = $request->store->store_code;
                $host = $request->getSchemeAndHttpHost();

                $partnerCode = "MOMOBKUN20180529";
                $accessKey = "klm05TvNBzhg7h7j";
                $secretKey = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";
                $orderInfo = "Thanh toán qua MoMo";
                $amount = $request->order->total_final;
                // $ipnUrl = $_POST["ipnUrl"];
                $orderId = time() . "";
                $redirectUrl = $host . "/api/customer/$store_code/purchase/return/momo" . '?orderCode=' . $order_code;
                $ipnUrl = route("return/ipn");
                // Lưu ý: link notifyUrl không phải là dạng localhost
                $bankCode = "SML";

                // $partnerCode = $_POST["partnerCode"];
                // $accessKey = $_POST["accessKey"];
                // $serectkey = $_POST["secretKey"];
                $orderid = time() . "";
                // $orderInfo = $_POST["orderInfo"];
                // $amount = $_POST["amount"];
                // $bankCode = $_POST['bankCode'];
                // $returnUrl = $_POST['returnUrl'];
                $requestId = time() . "";
                $requestType = "payWithATM";
                $extraData = "";
                //before sign HMAC SHA256 signature
                $rawHashArr =  array(
                    'partnerCode' => $partnerCode,
                    'accessKey' => $accessKey,
                    'requestId' => $requestId,
                    'amount' => $amount,
                    'orderId' => $orderid,
                    'orderInfo' => $orderInfo,
                    'bankCode' => $bankCode,
                    'redirectUrl' => $redirectUrl,
                    'ipnUrl' => $ipnUrl,
                    'extraData' => $extraData,
                    'requestType' => $requestType,
                    "orderCode" =>  $order_code,
                );
                // echo $serectkey;die;
                $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl="
                    . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl="
                    . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;

                $signature = hash_hmac("sha256", $rawHash, $secretKey);

                $data =  array(
                    'partnerCode' => $partnerCode,
                    'accessKey' => $accessKey,
                    'requestId' => $requestId,
                    'amount' => $amount,
                    'orderId' => $orderid,
                    'orderInfo' => $orderInfo,
                    'redirectUrl' => $redirectUrl,
                    'ipnUrl' => $ipnUrl,
                    'bankCode' => $bankCode,
                    'extraData' => $extraData,
                    'requestType' => $requestType,
                    'signature' => $signature,
                    "orderCode" =>  $order_code,
                );
                $result = execPostRequest($endpoint, json_encode($data));
                $jsonResult = json_decode($result, true);  // decode json
                return redirect($jsonResult['payUrl']);
            })->middleware('has_customer_store', 'has_order', 'check_order_paid');;

            Route::get('/{store_code?}/purchase/return/momo', function (Request $request) {
                $webHook = WebhookHistory::create([
                    'order_code' => "Momo",
                    'json' =>  json_encode([
                        "ip" => $request->ip(),
                        "content" =>  $request->fullUrl()
                    ]),
                ]);
                header('Content-type: text/html; charset=utf-8');


                $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'; //Put your secret key in there

                $url = $request->fullUrl();
                $isIPN = false;
                if (str_contains($url, "/ipn/")) {
                    $isIPN = true;
                }

                if (!empty($_GET)) {
                    $accessKey = "klm05TvNBzhg7h7j";
                    $partnerCode = "MOMOBKUN20180529";
                    $orderId = $_GET["orderId"];
                    $localMessage = $_GET["message"];
                    $message = $_GET["message"];
                    $transId = $_GET["transId"];
                    $orderInfo = $_GET["orderInfo"];
                    $amount = $_GET["amount"];
                    // $errorCode = $_GET["resultCode"];
                    $responseTime = $_GET["responseTime"];
                    $requestId = $_GET["requestId"];
                    $extraData = $_GET["extraData"];
                    $payType = $_GET["payType"];
                    $orderType = $_GET["orderType"];
                    $extraData = $_GET["extraData"];
                    $m2signature = $_GET["signature"]; //MoMo signature
                    $orderCode = $_GET["orderCode"]; //MoMo signature


                    //Checksum
                    $rawHash = 'accessKey=' . $accessKey;
                    $rawHash .= '&amount=' . $amount;
                    $rawHash .= '&extraData=' . $extraData;
                    $rawHash .= '&message=' . $message;
                    $rawHash .= '&orderId=' . $orderId;
                    $rawHash .= '&orderInfo=' . $orderInfo;
                    $rawHash .= '&orderType=' . $orderType;
                    $rawHash .= '&partnerCode=' . $partnerCode;
                    $rawHash .= '&payType=' . $payType;
                    $rawHash .= '&requestId=' . $requestId;
                    $rawHash .= '&responseTime=' . $responseTime;
                    $rawHash .= '&resultCode=' . $_GET['resultCode'];
                    $rawHash .= '&transId=' . $transId;

                    $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

                    echo "<script>console.log('Debug huhu Objects: " . $rawHash . "' );</script>";
                    echo "<script>console.log('Debug huhu Objects: " . $secretKey . "' );</script>";
                    echo "<script>console.log('Debug huhu Objects: " . $partnerSignature . "' );</script>";
                    echo "<script>console.log('Debug huhu Objects11: " . $m2signature . "' );</script>";


                    if ($m2signature == $partnerSignature) {
                        $historyExists = StatusPaymentHistory::where(
                            'order_code',
                            $orderCode
                        )->first();

                        $orderExists = Order::where(
                            'order_code',
                            $orderCode
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
                            "order_code" => "Momo - " . ($orderCode ?? "")
                        ]);

                        if ($orderExists->total_final != $amount) {
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

                        if (!empty($historyExists) && $_GET['resultCode'] == '0') {
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

                        if (empty($historyExists) && $_GET['resultCode'] == '0') {
                            StatusPaymentHistory::create(
                                [
                                    "order_code" => $orderCode,
                                    "transaction_no" => $transId,
                                    "amount" => ($amount != null && $amount > 0) ? $amount : 0,
                                    "bank_code" => "",
                                    "card_type" => $payType,
                                    "order_info" => $orderInfo,
                                    "pay_date" => now(), // temp
                                    "response_code" => $_GET['resultCode'],
                                    "key_code_customer" => $partnerCode,
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
                                        "Đã thanh toán đơn hàng qua Momo",
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

                        $link_back = null;
                        $linkBackExists = LinkBackPay::where('order_id',  $orderExists->id)->first();
                        if ($_GET['resultCode'] == '0') {
                            if (!empty($orderExists)) {
                                $orderExists->update(

                                    [
                                        "payment_status" => 2,
                                    ]
                                );
                            }

                            HistoryPayOrder::create([
                                "store_id" => $request->store->id,
                                "order_id" => $orderExists->id,
                                "payment_method_id" => $orderExists->payment_method_id,
                                "money" => $amount ?? 0,
                                'remaining_amount' => 0,
                                'revenue_expenditure_id' => null
                            ]);

                           
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
                }
            })->middleware('has_customer_store')->name('return/momo');

            //Đặt hàng
            Route::post('/{store_code}/carts/orders', 'App\Http\Controllers\Api\Customer\CustomerOrderController@create')->middleware('has_customer_store', 'get_customer_auth_cart');

            //hủy đơn hàng
            Route::post('/{store_code}/carts/orders/cancel', 'App\Http\Controllers\Api\Customer\CustomerOrderController@cancel_order')->middleware('has_customer_store', 'customer_auth');
            //Danh sách đơn hàng
            Route::get('/{store_code}/carts/orders', 'App\Http\Controllers\Api\Customer\CustomerOrderController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Lấy thông tin 1 đơn hàng
            Route::get('/{store_code}/carts/orders/{order_code}', 'App\Http\Controllers\Api\Customer\CustomerOrderController@getOne')->middleware('has_customer_store', 'customer_auth');
            //Lịch sử trạng thái đơn hàng
            Route::get('/{store_code}/carts/orders/status_records/{order_id}', 'App\Http\Controllers\Api\Customer\CustomerOrderController@status_records')->middleware('has_customer_store', 'customer_auth');
            //Thay đổi phương thức thanh toán của đơn hàng
            Route::put('/{store_code}/carts/orders/change_payment_method/{order_code}', 'App\Http\Controllers\Api\Customer\CustomerOrderController@change_payment_method')->middleware('has_customer_store', 'customer_auth');

            //Đánh giá sản phẩm
            Route::post('/{store_code}/products/{product_id}/reviews', 'App\Http\Controllers\Api\Customer\CustomerReviewsController@review')->middleware('has_customer_store', 'customer_auth');

            //Danh sách đánh giá của customer
            Route::get('/{store_code}/reviews', 'App\Http\Controllers\Api\Customer\CustomerReviewsController@getManagerAll')->middleware('has_customer_store', 'customer_auth');
            //Danh sách sản phẩm chưa đánh giá
            Route::get('/{store_code}/reviews/not_rated', 'App\Http\Controllers\Api\Customer\CustomerReviewsController@getAllProductNotRated')->middleware('has_customer_store', 'get_customer_auth_cart');

            //Yêu thích sản phẩm
            Route::post('/{store_code}/products/{product_id}/favorites', 'App\Http\Controllers\Api\Customer\CustomerFavoriteController@favorite')->middleware('has_customer_store', 'customer_auth');

            //Danh sách sản phẩm đã mua
            Route::get('/{store_code}/purchased_products', 'App\Http\Controllers\Api\Customer\CustomerProductController@purchased_products')->middleware('has_customer_store', 'customer_auth');
            //Danh sách sản phẩm đã mua
            Route::get('/{store_code}/watched_products', 'App\Http\Controllers\Api\Customer\CustomerProductController@watched_products')->middleware('has_customer_store', 'customer_auth');
            //Danh sách yêu thích
            Route::get('/{store_code}/favorites', 'App\Http\Controllers\Api\Customer\CustomerProductController@getAllFavorite')->middleware('has_customer_store', 'customer_auth');

            //Thêm vào lịch sử tìm kiếm
            Route::post('/{store_code}/search_histories', 'App\Http\Controllers\Api\Customer\CustomerSearchHistoryController@create')->middleware('get_customer_auth', 'has_customer_store');
            //Xóa lịch sử tìm kiếm
            Route::delete('/{store_code}/search_histories', 'App\Http\Controllers\Api\Customer\CustomerSearchHistoryController@hideAll')->middleware('get_customer_auth', 'has_customer_store');
            //Danh sách lịch sử tìm kiếm
            Route::get('/{store_code}/search_histories', 'App\Http\Controllers\Api\Customer\CustomerSearchHistoryController@get10Item')->middleware('get_customer_auth', 'has_customer_store');

            //Danh sách Badges chi so
            Route::get('/{store_code}/badges', 'App\Http\Controllers\Api\Customer\CustomerBadgesController@getBadges')->middleware('has_customer_store', 'get_customer_auth');

            // //Danh sách điểm danh hàng ngày
            // Route::get('/{store_code}/roll_calls', 'App\Http\Controllers\Api\Customer\CustomerRollCallController@get_roll_calls')->middleware('has_customer_store', 'customer_auth');
            // //Danh sách điểm danh hàng ngày
            // Route::post('/{store_code}/roll_calls/checkin', 'App\Http\Controllers\Api\Customer\CustomerRollCallController@checkin')->middleware('has_customer_store', 'customer_auth');

            //Lịch sử tích điểm
            Route::get('/{store_code}/point_history', 'App\Http\Controllers\Api\Customer\CustomerPointHistoryController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Lấy cấu hình điểm thưởng
            Route::get('/{store_code}/reward_points', 'App\Http\Controllers\Api\User\ConfigPointController@getConfig')->middleware('has_customer_store');

            //Báo cáo doanh thu tổng quan CTV
            Route::get('/{store_code}/collaborator/report', 'App\Http\Controllers\Api\User\ReportController@overview')->middleware('has_customer_store', 'customer_auth');


            //Đăng bài
            Route::post('/{store_code}/community_posts', 'App\Http\Controllers\Api\Customer\CustomerCommunityPostController@create')->middleware('has_customer_store', 'customer_auth');
            //Danh sách bài viết
            Route::get('/{store_code}/community_posts', 'App\Http\Controllers\Api\Customer\CustomerCommunityPostController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Danh sách bài viết home
            Route::get('/{store_code}/community_posts/home', 'App\Http\Controllers\Api\Customer\CustomerCommunityPostController@getAllHome')->middleware('has_customer_store', 'get_customer_auth_cart');
            //Danh sách bài viết home
            Route::get('/{store_code}/community_posts/customer/{customer_id}', 'App\Http\Controllers\Api\Customer\CustomerCommunityPostController@getAllOfCustomerOther')->middleware('has_customer_store', 'get_customer_auth_cart');


            //Lên lại bài đăng
            Route::put('/{store_code}/community_posts/{community_post_id}/reup', 'App\Http\Controllers\Api\Customer\CustomerCommunityPostController@reup')->middleware('has_customer_store', 'customer_auth');
            //Sửa bài đăng
            Route::put('/{store_code}/community_posts/{community_post_id}', 'App\Http\Controllers\Api\Customer\CustomerCommunityPostController@update')->middleware('has_customer_store', 'customer_auth');
            //1 bài đăng
            Route::get('/{store_code}/community_posts/{community_post_id}', 'App\Http\Controllers\Api\Customer\CustomerCommunityPostController@getOne')->middleware('has_customer_store', 'customer_auth');
            //Xóa bài đăng
            Route::delete('/{store_code}/community_posts/{community_post_id}', 'App\Http\Controllers\Api\Customer\CustomerCommunityPostController@delete')->middleware('has_customer_store', 'customer_auth');
            //Like
            Route::post('/{store_code}/community_post_like', 'App\Http\Controllers\Api\Customer\CustomerCommunityLikeController@create')->middleware('has_customer_store', 'get_customer_auth_cart');



            //Bình luận
            Route::post('/{store_code}/community_comments', 'App\Http\Controllers\Api\Customer\CustomerCommunityCommentController@create')->middleware('has_customer_store', 'customer_auth');
            //Danh sách Bình luận
            Route::get('/{store_code}/community_comments', 'App\Http\Controllers\Api\Customer\CustomerCommunityCommentController@getAll')->middleware('has_customer_store', 'get_customer_auth_cart');
            //Sửa Bình luận
            Route::put('/{store_code}/community_comments/{community_comment_id}', 'App\Http\Controllers\Api\Customer\CustomerCommunityCommentController@update')->middleware('has_customer_store', 'customer_auth');
            //Xóa Bình luận
            Route::delete('/{store_code}/community_comments/{community_comment_id}', 'App\Http\Controllers\Api\Customer\CustomerCommunityCommentController@delete')->middleware('has_customer_store', 'customer_auth');

            //Danh sách bạn bè
            Route::get('/{store_code}/friends', 'App\Http\Controllers\Api\Customer\CustomerCommunityFriendController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Danh sách bạn bè
            Route::get('/{store_code}/friends/all/{customer_id}', 'App\Http\Controllers\Api\Customer\CustomerCommunityFriendController@getAllFriendOfCustomer')->middleware('has_customer_store', 'customer_auth');
            //Hủy kết bạn
            Route::delete('/{store_code}/friends/{customer_id}', 'App\Http\Controllers\Api\Customer\CustomerCommunityFriendController@cancelFriend')->middleware('has_customer_store', 'customer_auth');
            //danh sach cầu kết bạn
            Route::get('/{store_code}/friend_requests', 'App\Http\Controllers\Api\Customer\CustomerCommunityFriendController@getAllRequestFriend')->middleware('has_customer_store', 'customer_auth');
            //Gửi yêu cầu kết bạn
            Route::post('/{store_code}/friend_requests', 'App\Http\Controllers\Api\Customer\CustomerCommunityFriendController@requestFriend')->middleware('has_customer_store', 'customer_auth');
            //Huy yêu cầu kết bạn
            Route::delete('/{store_code}/friend_requests/{customer_id}', 'App\Http\Controllers\Api\Customer\CustomerCommunityFriendController@deleteRequestFriend')->middleware('has_customer_store', 'customer_auth');

            //Xử lý yêu cầu kết bạn
            Route::post('/{store_code}/friend_requests/{request_id}/handle', 'App\Http\Controllers\Api\Customer\CustomerCommunityFriendController@handleRequest')->middleware('has_customer_store', 'customer_auth');

            //Danh sách bạn bè
            Route::get('/{store_code}/community_customer_profile/{customer_id}', 'App\Http\Controllers\Api\Customer\CustomerCommunityProfileController@getInfoOverview')->middleware('has_customer_store', 'customer_auth');
            //Danh sách người chat với customer
            Route::get('/{store_code}/person_chat', 'App\Http\Controllers\Api\Customer\CustomerMessageController@getAllPerson')->middleware('has_customer_store', 'customer_auth');
            //Danh sách tin nhắn chat
            Route::get('/{store_code}/person_chat/{to_customer_id}/messages', 'App\Http\Controllers\Api\Customer\CustomerMessageController@getAllMessage')->middleware('has_customer_store', 'customer_auth');
            //Chat cho customer
            Route::post('/{store_code}/person_chat/{to_customer_id}/messages', 'App\Http\Controllers\Api\Customer\CustomerMessageController@sendMessage')->middleware('has_customer_store', 'customer_auth');




            //Đào tạo
            //Danh sách khóa học
            Route::get('/{store_code}/train_courses', 'App\Http\Controllers\Api\Customer\CustomerTrainCourseController@getAll')->middleware('has_customer_store', 'customer_auth');
            //Thông tin 1 khóa học
            Route::get('/{store_code}/train_courses/{course_id}', 'App\Http\Controllers\Api\Customer\CustomerTrainCourseController@getOneCourse')->middleware('has_customer_store', 'customer_auth');
            //Giáo án chương trình học
            Route::get('/{store_code}/train_chapter_lessons/{train_course_id}', 'App\Http\Controllers\Api\Customer\CustomerTrainChaptersController@getAll')->middleware('has_customer_store', 'customer_auth');
            // Thông tin bài học
            Route::get('/{store_code}/lessons/{train_lesson_id}', 'App\Http\Controllers\Api\Customer\CustomerTrainChaptersController@getOneLesson')->middleware('has_customer_store', 'customer_auth');
            //Thông tin bài học
            Route::get('/{store_code}/lessons/{train_lesson_id}', 'App\Http\Controllers\Api\Customer\CustomerTrainChaptersController@getOneLesson')->middleware('has_customer_store', 'customer_auth');
            //Học bài
            Route::post('/{store_code}/lessons/{train_lesson_id}/learn', 'App\Http\Controllers\Api\Customer\CustomerTrainChaptersController@learnOneLesson')->middleware('has_customer_store', 'customer_auth');


            //Danh sách bài trắc nghiệm
            Route::get('/{store_code}/train_courses/{train_course_id}/quiz', 'App\Http\Controllers\Api\Customer\CustomerTrainQuizController@getAllQuiz')->middleware('has_customer_store', 'customer_auth');
            //Danh sách câu hỏi trắc nghiệm
            Route::get('/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}', 'App\Http\Controllers\Api\Customer\CustomerTrainQuizController@getOneQuiz')->middleware('has_customer_store', 'customer_auth');
            //Nộp bài
            Route::post('/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}/submit', 'App\Http\Controllers\Api\Customer\CustomerTrainQuizController@submitQuiz')->middleware('has_customer_store', 'customer_auth');
            //Lịch sử làm bài
            Route::get('/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}/history_submit', 'App\Http\Controllers\Api\Customer\CustomerTrainQuizController@historySubmit')->middleware('has_customer_store', 'customer_auth');

            //Mini game vòng quy
            // danh sách mini game vòng quay
            Route::get('/{store_code}/spin_wheels', 'App\Http\Controllers\Api\Customer\PlayerSpinWheelController@getSpinWheels')->middleware('has_customer_store');
            // lấy 1 mini game
            Route::get('/{store_code}/spin_wheels/{spin_wheel_id}', 'App\Http\Controllers\Api\Customer\PlayerSpinWheelController@getASpinWheels')->middleware('has_customer_store');
            // thông tin người chơi
            Route::get('/{store_code}/spin_wheels/{spin_wheel_id}/player_info', 'App\Http\Controllers\Api\Customer\PlayerSpinWheelController@getInfoPlayer')->middleware('has_customer_store', 'customer_auth');
            // tham gia trò chơi
            Route::post('/{store_code}/spin_wheels/{spin_wheel_id}/player', 'App\Http\Controllers\Api\Customer\PlayerSpinWheelController@create')->middleware('has_customer_store', 'customer_auth');
            // Lịch sử nhận lượt chơi
            Route::get('/{store_code}/spin_wheels/{spin_wheel_id}/history_turn', 'App\Http\Controllers\Api\Customer\PlayerSpinWheelController@historyTurnPlay')->middleware('has_customer_store', 'customer_auth');
            // Lịch sử nhận quà
            Route::get('/{store_code}/spin_wheels/{spin_wheel_id}/history_gift', 'App\Http\Controllers\Api\Customer\PlayerSpinWheelController@historyGift')->middleware('has_customer_store', 'customer_auth');
            // Quay vòng quay
            Route::post('/{store_code}/spin_wheels/{spin_wheel_id}/play', 'App\Http\Controllers\Api\Customer\PlayerSpinWheelController@playSpinWheel')->middleware('has_customer_store', 'customer_auth');
            // Lấy lượt chơi
            Route::post('/{store_code}/spin_wheels/{spin_wheel_id}/get_turn', 'App\Http\Controllers\Api\Customer\PlayerSpinWheelController@getTurnPlayMiniGame')->middleware('has_customer_store', 'customer_auth');

            //Mini game đoán số
            // danh sách mini game đoán số
            Route::get('/{store_code}/guess_numbers', 'App\Http\Controllers\Api\Customer\GuessNumberController@getGuessNumbers')->middleware('has_customer_store');
            // lấy 1 mini game
            Route::get('/{store_code}/guess_numbers/{guess_number_id}', 'App\Http\Controllers\Api\Customer\GuessNumberController@getAGuessNumber')->middleware('has_customer_store');
            // thông tin người chơi
            Route::get('/{store_code}/guess_numbers/{guess_number_id}/player_info', 'App\Http\Controllers\Api\Customer\GuessNumberController@getInfoPlayer')->middleware('has_customer_store', 'customer_auth');
            // tham gia mini game đoán số
            Route::post('/{store_code}/guess_numbers/{guess_number_id}/player', 'App\Http\Controllers\Api\Customer\GuessNumberController@joinGuessNumber')->middleware('has_customer_store', 'customer_auth');
            // Lịch sử dự đoán
            Route::get('/{store_code}/guess_numbers/{guess_number_id}/history_guess_number', 'App\Http\Controllers\Api\Customer\GuessNumberController@historyGift')->middleware('has_customer_store', 'customer_auth');
            // Dự đoán kết quả
            Route::post('/{store_code}/guess_numbers/{guess_number_id}/play', 'App\Http\Controllers\Api\Customer\GuessNumberController@predictGuessNumber')->middleware('has_customer_store', 'customer_auth');
        });
        ///////////////////// ADMIN /////////////////////

        //Api dành cho Admin
        Route::prefix('admin')->group(function () {

            //Đăng ký
            //Route::post('/register', 'App\Http\Controllers\Api\CustomerRegisterController@register')->middleware('has_customer_store');
            //Đăng nhập
            Route::post('/login', 'App\Http\Controllers\Api\Admin\AdminLoginController@login');
            //Lấy lại mật khẩu
            Route::post('/reset_password', 'App\Http\Controllers\Api\Admin\AdminLoginController@reset_password');
            //Thay đổi mật khẩu
            Route::post('/change_password', 'App\Http\Controllers\Api\Admin\AdminLoginController@change_password')->middleware('admin_auth');

            //Danh sách store
            Route::get('/manage/stores', 'App\Http\Controllers\Api\Admin\AdminManageStoreController@getAll')->middleware('admin_auth');
            Route::get('/manage/stores/{store_id}', 'App\Http\Controllers\Api\Admin\AdminManageStoreController@getOneStore')->middleware('admin_auth');
            Route::get('/manage/stores_by_code/{store_code}', 'App\Http\Controllers\Api\Admin\AdminManageStoreController@getOneStoreByCode')->middleware('admin_auth');


            Route::put('/manage/stores/{store_id}', 'App\Http\Controllers\Api\Admin\AdminManageStoreController@updateOneStore')->middleware('admin_auth');
            Route::delete('/manage/stores/{store_id}', 'App\Http\Controllers\Api\Admin\AdminManageStoreController@deleteOneStore')->middleware('admin_auth');

            //Danh sách user
            Route::get('/manage/users', 'App\Http\Controllers\Api\Admin\AdminManageUserController@getAll')->middleware('admin_auth');
            Route::get('/manage/users/{user_id}', 'App\Http\Controllers\Api\Admin\AdminManageUserController@getOneUser')->middleware('admin_auth');
            Route::put('/manage/users/{user_id}', 'App\Http\Controllers\Api\Admin\AdminManageUserController@updateOneUser')->middleware('admin_auth');
            Route::get('/manage/users/phone_number/{phone_number}', 'App\Http\Controllers\Api\Admin\AdminManageUserController@getInfoDataWithPhone')->middleware('admin_auth');


            //User vip
            Route::post('/vip_user/{user_id}/on_off', 'App\Http\Controllers\Api\Admin\AdminConfigVipController@on_off_vip')->middleware('admin_auth');
            Route::post('/vip_user/{user_id}/config', 'App\Http\Controllers\Api\Admin\AdminConfigVipController@config_user_vip')->middleware('admin_auth');
            Route::get('/vip_user/{user_id}/config', 'App\Http\Controllers\Api\Admin\AdminConfigVipController@get_config_user_vip')->middleware('admin_auth');

            //Banner
            Route::get('/banners', 'App\Http\Controllers\Api\Admin\AdminBannerController@getAll')->middleware('admin_auth');
            Route::post('/banners', 'App\Http\Controllers\Api\Admin\AdminBannerController@create')->middleware('admin_auth');
            Route::delete('/banners/{banner_id}', 'App\Http\Controllers\Api\Admin\AdminBannerController@deleteOneBanner')->middleware('admin_auth');
            Route::put('/banners/{banner_id}', 'App\Http\Controllers\Api\Admin\AdminBannerController@updateOneBanner')->middleware('admin_auth');


            //Up 1 ảnh
            Route::post('images', 'App\Http\Controllers\Api\User\UploadImageController@upload');

            //Thông tin server
            Route::get('info_server', 'App\Http\Controllers\Api\InfoServerController@info');

            //Device token firebase
            Route::post('/device_token', 'App\Http\Controllers\Api\Admin\AdminDeviceTokenController@updateDeviceTokenAdmin')->middleware('admin_auth');

            //Cau hinh thong bao
            Route::post('/notification/user/config', 'App\Http\Controllers\Api\Admin\ConfigNotificationController@config')->middleware('admin_auth');
            Route::get('/notification/user/config', 'App\Http\Controllers\Api\Admin\ConfigNotificationController@getOne')->middleware('admin_auth');


            //Nhân viên
            //Danh sách nhân viên
            Route::post('/employee', 'App\Http\Controllers\Api\Admin\AdminEmployeeController@create')->middleware('admin_auth');
            Route::get('/employee', 'App\Http\Controllers\Api\Admin\AdminEmployeeController@getAll')->middleware('admin_auth');
            Route::delete('/employee/{employee_id}', 'App\Http\Controllers\Api\Admin\AdminEmployeeController@delete')->middleware('admin_auth');
            Route::put('/employee/{employee_id}', 'App\Http\Controllers\Api\Admin\AdminEmployeeController@update')->middleware('admin_auth');


            //Danh sách user cần tư vấn
            Route::post('/user_advices', 'App\Http\Controllers\Api\Admin\UserAdviceController@create')->middleware('admin_auth');
            Route::get('/user_advices', 'App\Http\Controllers\Api\Admin\UserAdviceController@getAll')->middleware('admin_auth');
            Route::delete('/user_advices/{userAdvice_id}', 'App\Http\Controllers\Api\Admin\UserAdviceController@delete')->middleware('admin_auth');
            Route::put('/user_advices/{userAdvice_id}', 'App\Http\Controllers\Api\Admin\UserAdviceController@update')->middleware('admin_auth');
            Route::post('/user_advices/all', 'App\Http\Controllers\Api\Admin\UserAdviceController@createManyUserAdvice')->middleware('admin_auth');
            Route::put('/user_advices', 'App\Http\Controllers\Api\Admin\UserAdviceController@updateMany')->middleware('admin_auth');
            Route::post('/history_user_advices/{user_advice_id}', 'App\Http\Controllers\Api\Admin\UserAdviceController@addHistoryUserAdvice')->middleware('admin_auth');
            Route::get('/history_user_advices/{user_advice_id}', 'App\Http\Controllers\Api\Admin\UserAdviceController@getHistoryUserAdvice')->middleware('admin_auth');

            //Lịch sử tư vấn
            Route::post('/history_contact_user_advice/{user_advice_id}', 'App\Http\Controllers\Api\Admin\HistoryContactUserAdviceController@create')->middleware('admin_auth');
            Route::get('/history_contact_user_advice/{user_advice_id}', 'App\Http\Controllers\Api\Admin\HistoryContactUserAdviceController@getAll')->middleware('admin_auth');
            Route::delete('/history_contact_user_advice/{user_advice_id}/{history_id}', 'App\Http\Controllers\Api\Admin\HistoryContactUserAdviceController@delete')->middleware('admin_auth');
            Route::put('/history_contact_user_advice/{user_advice_id}/{history_id}', 'App\Http\Controllers\Api\Admin\HistoryContactUserAdviceController@update')->middleware('admin_auth');

            Route::post('/otp', 'App\Http\Controllers\Api\Admin\OtpController@get_otp')->middleware('admin_auth');

            Route::get('/badges', 'App\Http\Controllers\Api\Admin\AdminBadgesController@get_badges')->middleware('admin_auth');

            //getProfile
            Route::get('/profile', 'App\Http\Controllers\Api\Admin\AdminProfileController@getProfile')->middleware('admin_auth');
            //updateProfile
            Route::put('/profile', 'App\Http\Controllers\Api\Admin\AdminProfileController@updateProfile')->middleware('admin_auth');

            //setup_data_example
            Route::put('/setup_data_example', 'App\Http\Controllers\Api\Admin\ExampleDataShopController@setupShopData')->middleware('admin_auth');

            //setup_data_example
            Route::get('/setup_data_example', 'App\Http\Controllers\Api\Admin\ExampleDataShopController@getSetupShopData')->middleware('admin_auth');
            //init_store_test
            Route::get('/init_store_test', 'App\Http\Controllers\Api\Admin\ExampleDataShopController@test_init_store')->middleware('admin_auth');


            //migrate
            Route::get('/migrate', 'App\Http\Controllers\Api\Admin\AdminMigrateController@migrate');


            //handle_excel
            Route::get('/handle_excel', 'App\Http\Controllers\Api\Admin\HandleExelController@handleExel');


            //Đặt lịch thông báo tới customer
            Route::post('/notifications/schedule', 'App\Http\Controllers\Api\Admin\NotificationTaskController@setup')->middleware('admin_auth');
            Route::get('/notifications/schedule', 'App\Http\Controllers\Api\Admin\NotificationTaskController@tasks')->middleware('user_auth');
            Route::delete('/notifications/schedule/{schedule_id}', 'App\Http\Controllers\Api\Admin\NotificationTaskController@delete')->middleware('admin_auth');
            Route::put('/notifications/schedule/{schedule_id}', 'App\Http\Controllers\Api\Admin\NotificationTaskController@edit')->middleware('admin_auth');
            // Route::get('/notifications/schedule/test', 'App\Http\Controllers\Api\Admin\NotificationTaskController@test')->middleware('admin_auth');
            Route::post('/notifications/schedule/test', 'App\Http\Controllers\Api\Admin\NotificationTaskController@test_send')->middleware('admin_auth');

            //Sàn thương mại cho cộng tác viên
            //Danh mục
            Route::post('/ecommerce/categories', 'App\Http\Controllers\Api\Admin\Ecommerce\CategoryController@create')->middleware('admin_auth');
            Route::get('/ecommerce/categories', 'App\Http\Controllers\Api\Admin\Ecommerce\CategoryController@getAll')->middleware('admin_auth');
            Route::put('/ecommerce/categories/{category_id}', 'App\Http\Controllers\Api\Admin\Ecommerce\CategoryController@update')->middleware('admin_auth');
            Route::delete('/ecommerce/categories/{category_id}', 'App\Http\Controllers\Api\Admin\Ecommerce\CategoryController@delete')->middleware('admin_auth');
            Route::get('/test', 'App\Http\Controllers\Api\Admin\TestController@test');

            //Lịch sử thao tác admin
            Route::get('/operation_histories', 'App\Http\Controllers\Api\Admin\AdminHistoryOperationController@getAll')->middleware('admin_auth');

            //Báo cáo cho sale
            Route::get('/consultation_reports', 'App\Http\Controllers\Api\Admin\statisticConsultationController@statisticConsultation')->middleware('admin_auth');
            Route::get('/support_consultation_reports', 'App\Http\Controllers\Api\Admin\statisticConsultationController@supportStatisticConsultation')->middleware('admin_auth');
        });
    });
