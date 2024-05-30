<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Models\Comment;
use App\Models\MsgCode;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\LastWatchLesson;
use App\Models\TrainChapter;
use App\Models\TrainCourse;
use App\Models\TrainLesson;
use Illuminate\Http\Request;

/**
 * @group  Đào tạo/giáo án chương trình học
 */
class CustomerTrainChaptersController extends Controller
{

    /**
     * Danh sách chương và bài học
     * 
     * 
     */
    public function getAll(Request $request)
    {

        $train_course_id = $request->route()->parameter('train_course_id');

        $all = TrainChapter::where('store_id', $request->store->id)
            ->where('train_course_id',  $train_course_id)
            ->orderBy('position', 'asc')
            ->get();

        $data =  [];
        foreach ($all  as $chap) {

            $lessons = [];

            $allless = TrainLesson::where('store_id', $request->store->id)
                ->where('train_chapter_id', $chap->id)
                ->orderBy('position', 'asc')
                ->get();

            foreach ($allless  as $less) {
                $less->last_learn = $this->getLastLearn($request, $less->id, $request->customer->id);
                array_push($lessons, $less);
            }
            $chap = $chap->toArray();
            $chap['lessons'] = $lessons;


            array_push($data, $chap);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $data
        ], 200);
    }

    function getLastLearn($request, $train_lesson_id, $customer_id)
    {
        $lastWatchLesson =   LastWatchLesson::where('store_id', $request->store->id)
            ->where('customer_id',  $customer_id)
            ->where('train_lesson_id', $train_lesson_id)
            ->first();
        return [
            "duration_milliseconds" =>  $lastWatchLesson == null ? -1 :   $lastWatchLesson->duration_milliseconds,
            "position_milliseconds" =>  $lastWatchLesson == null ? -1 :   $lastWatchLesson->position_milliseconds,
        ];
    }

    /**
     * Thông tin 1 bài học
     * 
     * 
     */
    public function getOneLesson(Request $request)
    {

        $train_lesson_id = request('train_lesson_id');

        $lessonExist = TrainLesson::where('id', $train_lesson_id)->where('store_id', $request->store->id)->first();

        if ($lessonExist == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_LESSON[1],
                'msg' => MsgCode::NO_LESSON[1],
            ], 400);
        }

        $lessonExist->last_learn = $this->getLastLearn($request, $lessonExist->id, $request->customer->id);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $lessonExist
        ], 200);
    }

    /**
     * Học 1 bài học
     * 
     * 
     */
    public function learnOneLesson(Request $request)
    {

        $train_lesson_id = request('train_lesson_id');

        $lessonExist = TrainLesson::where('id', $train_lesson_id)->where('store_id', $request->store->id)->first();

        if ($lessonExist == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_LESSON[1],
                'msg' => MsgCode::NO_LESSON[1],
            ], 400);
        }

        $lastWatchLesson =   LastWatchLesson::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)
            ->where('train_lesson_id', $train_lesson_id)
            ->first();

        if ($lastWatchLesson == null) {
            $lastWatchLesson = LastWatchLesson::create([
                "store_id" => $request->store->id,
                "train_lesson_id" =>  $train_lesson_id,
                "customer_id" => $request->customer->id,
                "duration_milliseconds" => $request->duration_milliseconds,
                "position_milliseconds" => $request->position_milliseconds,
            ]);
        } else {
            $lastWatchLesson->update([
                "store_id" => $request->store->id,
                "train_lesson_id" =>  $train_lesson_id,
                "customer_id" => $request->customer->id,
                "duration_milliseconds" => $request->duration_milliseconds,
                "position_milliseconds" => $request->position_milliseconds,
            ]);
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $lastWatchLesson
        ], 200);
    }
}
