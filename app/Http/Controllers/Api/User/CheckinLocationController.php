<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\CheckinLocation;
use App\Models\MsgCode;
use App\Services\UploadImageService;
use Illuminate\Http\Request;


/**
 * @group User/Vị trí làm việc
 * 
 * APIs AppTheme
 */
class CheckinLocationController extends Controller
{
    /**
     * Thêm vị trí làm việc
     * @urlParam  store_code required Store code
     * @bodyParam name string name
     * @bodyParam wifi_name string wifi_name
     * @bodyParam wifi_mac string wifi_mac
     */
    public function create(Request $request)
    {

        if (empty($request->name)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $checkCheckinLocationExists = CheckinLocation::where(
            'name',
            $request->name
        )
            ->where(
                'branch_id',
                $request->branch->id
            )->where(
                'store_id',
                $request->store->id
            )->first();

        if ($checkCheckinLocationExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }

        $checkCheckinLocationExists = CheckinLocation::where(
            'wifi_mac',
            $request->wifi_mac
        )
            ->where(
                'branch_id',
                $request->branch->id
            )->where(
                'store_id',
                $request->store->id
            )->first();

        if ($checkCheckinLocationExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::MAC_WIFI_EXISTS[0],
                'msg' => MsgCode::MAC_WIFI_EXISTS[1],
            ], 400);
        }

        $checkinLocationCreate = CheckinLocation::create(
            [
                'store_id' => $request->store->id,
                "branch_id"  =>  $request->branch->id,
                "name" => $request->name,
                "wifi_name" => $request->wifi_name,
                "wifi_mac" => $request->wifi_mac,

            ]
        );
        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $checkinLocationCreate
        ], 201);
    }


    /**
     * Danh sách Vị trí làm việc
     * @urlParam  store_code required Store code
     */
    public function getAll(Request $request)
    {

        $checkinLocations = CheckinLocation::where('store_id', $request->store->id)
            ->where(
                'branch_id',
                $request->branch->id
            )
            ->orderBy('id', 'ASC')->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $checkinLocations,
        ], 200);
    }


    /**
     * xóa một vị trí
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  checkin_location_id required ID checkinLocation cần xóa thông tin.
     */
    public function delete(Request $request, $id)
    {

        $id = $request->route()->parameter('checkin_location_id');
        $checkCheckinLocationExists = CheckinLocation::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkCheckinLocationExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CHECKIN_LOCATION_EXISTS[0],
                'msg' => MsgCode::NO_CHECKIN_LOCATION_EXISTS[1],
            ], 404);
        } else {
            $idDeleted = $checkCheckinLocationExists->id;
            $checkCheckinLocationExists->delete();
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => ['idDeleted' => $idDeleted],
            ], 200);
        }
    }


    /**
     * update một CheckinLocation
     * @urlParam  store_code required Store code cần update
     * @urlParam  checkin_location_id required ID checkinLocation cần xóa thông tin.
     * @bodyParam name string name
     * @bodyParam wifi_name string wifi_name
     * @bodyParam wifi_mac string wifi_mac
     */
    public function update(Request $request)
    {

        if (empty($request->name)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $id = $request->route()->parameter('checkin_location_id');
        $checkCheckinLocationExists = CheckinLocation::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkCheckinLocationExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CHECKIN_LOCATION_EXISTS[0],
                'msg' => MsgCode::NO_CHECKIN_LOCATION_EXISTS[1],
            ], 404);
        } else {

            $checkCheckinLocationExists2 = CheckinLocation::where(
                'name',
                $request->name
            )->where(
                'store_id',
                $request->store->id
            )->where(
                'branch_id',
                $request->branch->id
            )
                ->where(
                    'id',
                    '<>',
                    $id
                )->first();
            if ($checkCheckinLocationExists2 != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                    'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
                ], 400);
            }


            $checkCheckinLocationExists2 = CheckinLocation::where(
                'wifi_mac',
                $request->wifi_mac
            )->where(
                'store_id',
                $request->store->id
            )->where(
                'branch_id',
                $request->branch->id
            )
                ->where(
                    'id',
                    '<>',
                    $id
                )->first();
            if ($checkCheckinLocationExists2 != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::MAC_WIFI_EXISTS[0],
                    'msg' => MsgCode::MAC_WIFI_EXISTS[1],
                ], 400);
            }

            $checkCheckinLocationExists->update(Helper::sahaRemoveItemArrayIfNullValue([
                'store_id' => $request->store->id,
                "branch_id"  =>  $request->branch->id,
                "wifi_name" => $request->wifi_name,
                "wifi_mac" => $request->wifi_mac,
                "name" => $request->name,
            ]));

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => CheckinLocation::where('id', $id)->first(),
            ], 200);
        }
    }
}
