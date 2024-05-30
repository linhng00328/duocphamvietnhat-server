<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\BonusAgency;
use App\Models\BonusAgencyStep;
use App\Models\MsgCode;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group  User/Thưởng đại lý
 */
class BonusAgencyController extends Controller
{
    /**
     * Lấy cấu hình thưởng cho đại lý
     * @urlParam  store_code required Store code
     */
    public function getBonusAgencyConfig(Request $request)
    {

        $bonusAgencyConfig = BonusAgency::where('store_id', $request->store->id)->first();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "config" =>  $bonusAgencyConfig,
                "step_bonus" => BonusAgencyStep::where('store_id', $request->store->id)->orderBy('threshold')->get()
            ],
        ], 200);
    }

    /**
     * Cấu hình thưởng cho đại lý
     * @urlParam  store_code required Store code
     * 
     * @bodyParam is_end required is_end
     * @bodyParam start_time required start_time
     * @bodyParam end_time required end_time
     */
    public function updateConfig(Request $request)
    {

        $bonusAgencyConfig = BonusAgency::where('store_id', $request->store->id)->first();

        if ($request->start_time == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::START_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::START_TIME_IS_REQUIRED[1],
            ], 400);
        }

        if ($request->end_time == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::END_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::END_TIME_IS_REQUIRED[1],
            ], 400);
        }


        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($request->start_time);
        $date2 = $carbon->parse($request->end_time);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';

      
        if ($bonusAgencyConfig == null) {
            BonusAgency::create([
                "store_id" => $request->store->id,
                "is_end" => $request->is_end,
                "start_time" =>  $dateFrom ,
                "end_time" => $dateTo 
            ]);
        } else {

            if ($request->start_time == null) {
                $bonusAgencyConfig->update([
                    "store_id" => $request->store->id,
                    "is_end" => $request->is_end,
                ]);
            } else {
                $bonusAgencyConfig->update([
                    "store_id" => $request->store->id,
                    "is_end" => $request->is_end,
                    "start_time" =>  $dateFrom ,
                    "end_time" =>  $dateTo 
                ]);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "config" =>  BonusAgency::where('store_id', $request->store->id)->first(),
                "step_bonus" => BonusAgencyStep::where('store_id', $request->store->id)->orderBy('threshold')->get()
            ],
        ], 200);
    }


    /**
     * Thêm 1 bậc tiền thưởng 
     * @urlParam  store_code required Store code
     * @bodyParam threshold double required Giới hạn được thưởng
     * @bodyParam reward_name double required Tên giải thưởng
     * @bodyParam reward_description double required Mô tả thưởng
     * @bodyParam reward_image_url double required Link ảnh thưởng
     * @bodyParam reward_value double required Giá trị thưởng
     * @bodyParam limit double required Giới hạn
     */
    public function createOneStep(Request $request)
    {

        $callaboratorExists = BonusAgencyStep::where(
            'store_id',
            $request->store->id
        )->where(
            'threshold',
            $request->threshold
        )->first();

        if ($callaboratorExists != null ||   $request->threshold == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::BONUS_EXISTS[0],
                'msg' => MsgCode::BONUS_EXISTS[1],
            ], 400);
        }

        if ($request->bonus < 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_VALUE[0],
                'msg' => MsgCode::INVALID_VALUE[1],
            ], 400);
        }

        $callaboratorExists = BonusAgencyStep::create([
            'store_id' => $request->store->id,
            'threshold' => $request->threshold,
            'reward_name' => $request->reward_name,
            'reward_description' => $request->reward_description,
            'reward_image_url' => $request->reward_image_url,
            'reward_value' => $request->reward_value,
            'limit' => $request->limit,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * xóa một bac thang
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  step_id required ID Step cần xóa thông tin.
     */
    public function deleteOneStep(Request $request, $id)
    {

        $id = $request->route()->parameter('step_id');
        $checkStepExists = BonusAgencyStep::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkStepExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST[0],
                'msg' => MsgCode::DOES_NOT_EXIST[1],
            ], 404);
        } else {
            $idDeleted = $checkStepExists->id;
            $checkStepExists->delete();
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
     * update một Step
     * @urlParam  store_code required Store code cần update
     * @urlParam  step_id required Step_id cần update
     * @bodyParam threshold double required Giới hạn được thưởng
     * @bodyParam reward_name double required Tên giải thưởng
     * @bodyParam reward_description double required Mô tả thưởng
     * @bodyParam reward_image_url double required Link ảnh thưởng
     * @bodyParam reward_value double required Giá trị thưởng
     * @bodyParam limit double required Giới hạn
     */
    public function updateOneStep(Request $request)
    {

        if ($request->reward_value <= 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_VALUE[0],
                'msg' => MsgCode::INVALID_VALUE[1],
            ], 400);
        }

        $id = $request->route()->parameter('step_id');
        $checkStepExists = BonusAgencyStep::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkStepExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST[0],
                'msg' => MsgCode::DOES_NOT_EXIST[1],
            ], 404);
        } else {

            $callaboratorExists = BonusAgencyStep::where(
                'store_id',
                $request->store->id
            )->where(
                'threshold',
                $request->threshold
            )->where('id', "<>", $id)->first();

            if ($callaboratorExists != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::BONUS_EXISTS[0],
                    'msg' => MsgCode::BONUS_EXISTS[1],
                ], 400);
            }


            $checkStepExists->update([
                'threshold' => $request->threshold,
                'reward_name' => $request->reward_name,
                'reward_description' => $request->reward_description,
                'reward_image_url' => $request->reward_image_url,
                'reward_value' => $request->reward_value,
                'limit' => $request->limit,
            ]);

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => BonusAgencyStep::where('id', $id)->first(),
            ], 200);
        }
    }
}
