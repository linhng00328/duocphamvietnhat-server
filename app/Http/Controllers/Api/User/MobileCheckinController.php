<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\MobileCheckin;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  User/Điện thoại chấm công
 */

class MobileCheckinController extends Controller
{

    /**
     * Danh sách điện thoại chấm công
     * 
     * status 0 chưa duyệt, 1 đã duyệt
     * 
     * @urlParam  store_code required Store code
     * 
     * 
     */
    public function getAll(Request $request)
    {


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => MobileCheckin::where('staff_id', $request->staff->id)->get()
        ], 200);
    }

    /**
     * Thêm thiết bị mới
     * @urlParam  store_code required Store code
     * @bodyParam name string Tên điện thoại
     * @bodyParam device_id string device_id
     */
    public function create(Request $request)
    {

        if ($request->name == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        if ($request->device_id == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::DEVICE_IS_REQUIRED[0],
                'msg' => MsgCode::DEVICE_IS_REQUIRED[1],
            ], 400);
        }

        $mobileExist = MobileCheckin::where('device_id', $request->device_id)
            ->where('staff_id', $request->staff->id)
            ->where('branch_id', $request->branch->id)
            ->where('store_id', $request->store->id)->first();

        if ($mobileExist != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::DEVICE_ALREADY_EXISTS[0],
                'msg' => MsgCode::DEVICE_ALREADY_EXISTS[1],
            ], 400);
        }



        $data = [
            'store_id' => $request->store->id,
            'staff_id' => $request->staff->id,
            'name' => $request->name,
            'device_id' =>  $request->device_id,
            'branch_id' => $request->branch->id
        ];


        $mobileCheckinCreated = MobileCheckin::create($data);

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => MobileCheckin::where('id', $mobileCheckinCreated->id)
                ->first()
        ], 201);
    }

    /**
     * Cập nhật điện thoại
     * @urlParam  store_code required Store code
     * @bodyParam name string Tên điện thoại
     * @bodyParam device_id string device_id
     */
    public function update(Request $request)
    {

        $mobile_id = $request->route()->parameter('mobile_id');



        $mobileExists = MobileCheckin::where('store_id', $request->store->id)
            ->where('staff_id', $request->staff->id)
            ->where('branch_id', $request->branch->id)
            ->where('id',   $mobile_id)
            ->first();

        if ($mobileExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_MOBILE_EXISTS[0],
                'msg' => MsgCode::NO_MOBILE_EXISTS[1],
            ], 400);
        }

        if ($request->name == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }


        if ($request->device_id == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::DEVICE_IS_REQUIRED[0],
                'msg' => MsgCode::DEVICE_IS_REQUIRED[1],
            ], 400);
        }

        $mobileNameExists = MobileCheckin::where('device_id', $request->device_id)
            ->where('staff_id', $request->staff->id)
            ->where('branch_id', $request->branch->id)
            ->where('store_id', $request->store->id)->where('id', '!=', $mobile_id)->first();

        if ($mobileNameExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::DEVICE_ALREADY_EXISTS[0],
                'msg' => MsgCode::DEVICE_ALREADY_EXISTS[1],
            ], 400);
        }


        $data = [
            'store_id' => $request->store->id,
            'staff_id' => $request->staff->id,
            'name' => $request->name,
            'device_id' =>  $request->device_id,
            'branch_id' => $request->branch->id
        ];


        $mobileExists->update($data);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => MobileCheckin::where('id', $mobileExists->id)
                ->first()
        ], 200);
    }


    /**
     * Xóa thiết bị
     * @urlParam  store_code required Store code
     * @urlParam  mobile_id required ID thiết bị
     */
    public function delete(Request $request)
    {

        $mobile_id = $request->route()->parameter('mobile_id');

        $mobileExists = MobileCheckin::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('id', $mobile_id)
            ->first();

        if ($mobileExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_MOBILE_EXISTS[0],
                'msg' => MsgCode::NO_MOBILE_EXISTS[1],
            ], 400);
        }

        $idDeleted = $mobileExists->id;

        $mobileExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }
}
