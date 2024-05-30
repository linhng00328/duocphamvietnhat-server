<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Models\ConfigNotification;
use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\TaskNoti;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group  User/Lên lịch thông báo tới khách hàng
 */

class NotificationTaskController extends Controller
{
    // $weekMap = [
    //     0 => 'SU',
    //     1 => 'MO',
    //     2 => 'TU',
    //     3 => 'WE',
    //     4 => 'TH',
    //     5 => 'FR',
    //     6 => 'SA',
    // ];
    public function test(Request $request)
    {

        // $date = Helper::getTimeNowDateTime();
        // $dateC =  Carbon::parse(Helper::getTimeNowString());
        // $time1 = $date->format('H:i:00');
        // $time2 = $date->format('H:i:59');

        // $dayNow = (int)$date->format('d');
        // $monthNow =  (int)$date->format('m');
        // $dayOfWeek =    (int)$dateC->dayOfWeek;

        // //Xử lý 1 lần
        // $timeOnce1 = $date->format('Y-m-d H:i:00');
        // $timeOnce2 = $date->format('Y-m-d H:i:59');

        // $listCanOnce = TaskNoti::where('status', 0)
        //     ->where('type_schedule', 0)
        //     ->where('time_run', '>=',  $timeOnce1)
        //     ->where('time_run', '<',  $timeOnce2)
        //     ->get();


        // foreach ($listCanOnce  as $itemTask) {
        //     if ($itemTask->type_schedule === 0) {
        //         $listCustomer = Customer::where(
        //             'store_id',
        //             $itemTask->store_id
        //         )->get();

        //         foreach ($listCustomer as $customer) {
        //             PushNotificationCustomerJob::dispatch(
        //                 $request->store->id,
        //                 $customer->id,
        //                 $itemTask->content,
        //                 $itemTask->title,
        //                 TypeFCM::SEND_ALL
        //             );
        //         }

        //         $task = TaskNoti::where(
        //             'id',
        //             $itemTask->id
        //         )->first();

        //         $task->update([
        //             'status' => 2,
        //             'time_run_near' => $dateC
        //         ]);
        //     }
        // }


        // //Xử lý noti lịch trình lặp lại
        // $listCanHandle = TaskNoti::where('status', '<>', 0)
        //     ->whereTime('time_of_day', '>=', $time1)
        //     ->whereTime('time_of_day', '<', $time2)
        //     ->where('time_of_day', '<', $time2)
        //     ->get();

        // foreach ($listCanHandle as $itemTask) {

        //     $allowSend = false;
        //     if ($itemTask->type_schedule === 1) {
        //         $allowSend = true;
        //     }

        //     if ($itemTask->type_schedule === 2) {
        //         if ($itemTask->day_of_week ==  $dayOfWeek) {
        //             $allowSend = true;
        //         }
        //     }

        //     if ($itemTask->type_schedule === 3) {
        //         if ($itemTask->day_of_month ==   $dayNow) {
        //             $allowSend = true;
        //         }
        //     }

        //     if ($allowSend === true) {

        //         $listCustomer = Customer::where(
        //             'store_id',
        //             $itemTask->store_id
        //         );

        //         if ($itemTask->group_customer == 1) {
        //             $dayBirth1 = $date->format('Y-m-d 00:00:00');
        //             $dayBirth2 = $date->format('Y-m-d 23:59:59');

        //             $listCustomer =  $listCustomer
        //                 ->where('day_of_birth', '>=',  $dayBirth1)
        //                 ->where('day_of_birth', '<',   $dayBirth2);
        //         }

        //         $listCustomer =  $listCustomer->get();

        //         foreach ($listCustomer as $customer) {
        //             PushNotificationCustomerJob::dispatch(
        //                 $request->store->id,
        //                 $customer->id,
        //                 $itemTask->content,
        //                 $itemTask->title,
        //                 TypeFCM::SEND_ALL
        //             );
        //         }

        //         $task = TaskNoti::where(
        //             'id',
        //             $itemTask->id
        //         )->first();

        //         $task->update([
        //             'time_run_near' => $dateC
        //         ]);
        //     }
        // }

        dd("ok");
    }

    /**
     * 
     * Test gửi thông báo
     * 
     * @bodyParam title string required Tiêu đề thông báo
     * @bodyParam description string required Mô tả thông báo
     * 
     */

    public function test_send(Request $request)
    {


        $configExis = ConfigNotification::where(
            'store_id',
            $request->store->id,
        )->first();


        // $customers = Customer::where("store_id", $request->store->id,)->get();
        // foreach ($customers  as $customer) {

        PushNotificationCustomerJob::dispatch(
            $request->store->id,
            null,
            $request->title,
            $request->description,
            TypeFCM::SEND_ALL,
            null
        );
        //    }



        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 201);
    }

    /**
     * Tạo lịch mới
     * @bodyParam title string required Tiêu đề thông báo
     * @bodyParam description string required Mô tả thông báo
     * @bodyParam group_customer integer required Nhóm khách hàng 0 tất cả, 1 ngày sinh nhật, 2 dai ly, 3 ctv
     * @bodyParam time_of_day string required Thời gian thông báo trong ngày
     * @bodyParam type_schedule integer required 0 chạy đúng 1 lần, 1 hàng ngày, 2 hàng tuần, 3 hàng tháng
     * @bodyParam time_run datetime required Khi chọn chạy đúng 1 lần
     * @bodyParam day_of_week integer required Khi chọn chạy hàng tuần
     * @bodyParam day_of_month integer required Khi chọn chạy hàng tháng
     * @bodyParam time_run_near datetime required Gần nhất
     * @bodyParam status datetime required 0 đang chạy, 1 tạm dừng, 2 đã xong
     * @bodyParam reminiscent_name string tên gợi nhớ (ví dụ tên sản phẩm cái này khách không nhập)
     * @bodyParam type_action gồm: NONE,PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
     * @bodyParam value_action string giá trị thực thi ví dụ  id cate,product hoặc link (string)
     * * @bodyParam group_customer int required 0 khách hàng, 1  cộng tác viên, 2 đại lý, 4 nhóm khách hàng
     * @bodyParam group_type_id int required id của group cần xử lý 
     * @bodyParam group_type_name int required name của group cần xử lý 
     * @bodyParam agency_type_id int required id tầng đại lý trường hợp group là 2
     * @bodyParam agency_type_name Tên required name cấp đại lý VD:Cấp 1
     * 
     */
    public function setup(Request $request)
    {


        $created = TaskNoti::create(
            [
                "store_id" => $request->store->id,
                "title" => $request->title,
                "description" => $request->description,
                "group_customer" => $request->group_customer,
                "time_of_day" => $request->time_of_day,
                "type_schedule" => $request->type_schedule,
                "time_run" => $request->time_run,
                "day_of_week" => $request->day_of_week,
                "day_of_month" => $request->day_of_month,
                "status" => $request->status,
                "reminiscent_name" => $request->reminiscent_name,
                "type_action" => $request->type_action,
                "value_action" => $request->value_action,

                'agency_type_id' =>  $request->agency_type_id,
                'agency_type_name' =>  $request->agency_type_name,

                'group_type_id' =>  $request->group_type_id,
                'group_type_name' =>  $request->group_type_name,
            ]
        );


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => TaskNoti::where(
                'id',
                $created->id
            )->where(
                'store_id',
                $request->store->id
            )->first()
        ], 201);
    }

    /**
     * Sửa 1 lịch
     * 
     * @urlParam title int required schedule_id
     * @bodyParam title string required Tiêu đề thông báo
     * @bodyParam description string required Mô tả thông báo
     * @bodyParam group_customer integer required Nhóm khách hàng 0 tất cả, 1 ngày sinh nhật
     * @bodyParam time_of_day string required Thời gian thông báo trong ngày
     * @bodyParam type_schedule integer required , 0 chạy đúng 1 lần, 1 hàng ngày, 2 hàng tuần, 3 hàng tháng
     * @bodyParam time_run datetime required Khi chọn chạy đúng 1 lần
     * @bodyParam day_of_week integer required Khi chọn chạy hàng tuần
     * @bodyParam day_of_month integer required Khi chọn chạy hàng tháng
     * @bodyParam time_run_near datetime required Gần nhất
     * @bodyParam status datetime required 0 đang chạy, 1 tạm dừng, 2 đã xong
     * @bodyParam reminiscent_name string tên gợi nhớ (ví dụ tên sản phẩm cái này khách không nhập)
     * @bodyParam type_action gồm: NONE,PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
     * @bodyParam value_action string giá trị thực thi ví dụ  id cate,product hoặc link (string)
     * * @bodyParam group_customer int required 0 khách hàng, 1  cộng tác viên, 2 đại lý, 4 nhóm khách hàng
     * @bodyParam group_type_id int required id của group cần xử lý 
     * @bodyParam group_type_name int required name của group cần xử lý 
     * @bodyParam agency_type_id int required id tầng đại lý trường hợp group là 2
     * @bodyParam agency_type_name Tên required name cấp đại lý VD:Cấp 1
     * 
     * 
     */
    public function edit(Request $request)
    {
        $id = $request->route()->parameter('schedule_id');
        $checkScheduleExists = TaskNoti::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkScheduleExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST[0],
                'msg' => MsgCode::DOES_NOT_EXIST[1],
            ], 404);
        }

        $checkScheduleExists->update(
            [
                "store_id" => $request->store->id,
                "title" => $request->title,
                "description" => $request->description,
                "group_customer" => $request->group_customer,
                "time_of_day" => $request->time_of_day,
                "type_schedule" => $request->type_schedule,
                "time_run" => $request->time_run,
                "day_of_week" => $request->day_of_week,
                "day_of_month" => $request->day_of_month,
                "status" => $request->status,
                "reminiscent_name" => $request->reminiscent_name,
                "type_action" => $request->type_action,
                "value_action" => $request->value_action,

                'group_customer' =>  $request->group_customer,
                'agency_type_id' =>  $request->agency_type_id,
                'agency_type_name' =>  $request->agency_type_name,

                'group_type_id' =>  $request->group_type_id,
                'group_type_name' =>  $request->group_type_name,
            ]
        );


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => TaskNoti::where(
                'id',
                $checkScheduleExists->id
            )->where(
                'store_id',
                $request->store->id
            )->first()
        ], 200);
    }

    /**
     * Xóa 1 lịch
     * @urlParam title int required schedule_id
     */
    public function delete(Request $request)
    {


        $id = $request->route()->parameter('schedule_id');
        $checkScheduleExists = TaskNoti::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkScheduleExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST[0],
                'msg' => MsgCode::DOES_NOT_EXIST[1],
            ], 404);
        }

        $idDeleted = $checkScheduleExists->id;
        $checkScheduleExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }

    /**
     * Danh sách lịch gửi noti
     * 
     */
    public function tasks(Request $request)
    {

        $list = TaskNoti::where(
            'store_id',
            $request->store->id
        )->orderBy('created_at', 'desc')->get();

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $list
        ], 201);
    }
}
