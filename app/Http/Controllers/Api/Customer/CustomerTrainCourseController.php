<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Models\Comment;
use App\Models\MsgCode;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\TrainCourse;
use Illuminate\Http\Request;

/**
 * @group  Đào tạo/khóa học
 */
class CustomerTrainCourseController extends Controller
{



    /**
     * Thong tin 1 bai hco
     * 
     * 
     */
    public function getOneCourse(Request $request)
    {

        $course_id = request('course_id');
        $trainCourseExists = TrainCourse::where('id', $course_id)->where('store_id', $request->store->id)->first();

        if ($trainCourseExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COURSE[1],
                'msg' => MsgCode::NO_COURSE[1],
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $trainCourseExists
        ], 200);
    }

    /**
     * Danh sách khóa học
     * 
     * 
     */
    public function getAll(Request $request)
    {

        $all =
            TrainCourse::where('store_id', $request->store->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);;


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $all
        ], 200);
    }
}
