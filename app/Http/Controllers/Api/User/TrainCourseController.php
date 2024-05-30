<?php

namespace App\Http\Controllers\Api\User;

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
class TrainCourseController extends Controller
{
    /**
     * 
     * Thông tin 1 khóa học
     * 
     * 
     */
    public function getOne(Request $request)
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
     * 
     * Xóa1 khóa học
     * 
     * 
     */
    public function delete(Request $request)
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

        $trainCourseExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
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
            TrainCourse::sortByRelevance(true)
            ->where('store_id', $request->store->id)
            ->orderBy('created_at', 'desc')
            ->search(request('search'))
            ->paginate(20);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $all
        ], 200);
    }

    /**
     * Danh sách khóa học
     * 
     * 
     */
    public function getAllForFilter(Request $request)
    {

        $all = TrainCourse::where('store_id', $request->store->id)
            ->orderBy('created_at', 'desc')
            ->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $all
        ], 200);
    }

    /**
     * Thêm 1 khóa học
     * 
     * @bodyParam title string tiêu đề khóa học
     * @bodyParam short_description string Mô tả ngắn
     * @bodyParam description string Mô tả dài html
     * @bodyParam image_url string Anh
     * 
     */
    public function create(Request $request)
    {

        if (empty($request->title)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_IS_REQUIRED[1],
                'msg' => MsgCode::TITLE_IS_REQUIRED[1],
            ], 400);
        }

        if (TrainCourse::where('store_id', $request->store->id)->where('title', $request->title)->first() != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[1],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }

        $create = TrainCourse::create([
            'store_id' => $request->store->id,
            'title' => $request->title,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'image_url' => $request->image_url,
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => TrainCourse::where('id',  $create->id)->first(),
        ], 200);
    }

    /**
     * Sửa 1 khóa học
     * 
     * 
     * @bodyParam title string tiêu đề khóa học
     * @bodyParam short_description string Mô tả ngắn
     * @bodyParam description string Mô tả dài html
     * 
     */
    public function update(Request $request)
    {

        $course_id = $request->route()->parameter('course_id');

        $trainCourseExists = TrainCourse::where('id', $course_id)->where('store_id', $request->store->id)->first();

        if ($trainCourseExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COURSE[1],
                'msg' => MsgCode::NO_COURSE[1],
            ], 400);
        }


        if (
            TrainCourse::where('store_id', $request->store->id)
            ->where('id', '!=', $course_id)
            ->where('title', $request->title)->first() != null
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[1],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }

        if (empty($request->title)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_IS_REQUIRED[1],
                'msg' => MsgCode::TITLE_IS_REQUIRED[1],
            ], 400);
        }

        $trainCourseExists->update([
            'store_id' => $request->store->id,
            'title' => $request->title,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'image_url' => $request->image_url,

        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => TrainCourse::where('id',  $trainCourseExists->id)->first(),
        ], 200);
    }
}
