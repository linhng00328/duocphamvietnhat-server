<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\TrainChapter;
use App\Models\TrainCourse;
use App\Models\TrainLesson;
use Illuminate\Http\Request;

use YouTube\YouTubeDownloader;
use YouTube\Exception\YouTubeException;

/**
 * @group  Đào tạo/giáo án chương trình học
 */
class TrainChaptersController extends Controller
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


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $lessonExist
        ], 200);
    }

    /**
     * Thêm 1 bài học
     * 
     * @bodyParam train_chapter_id int ID chương học
     * @bodyParam title string Tiêu đề bài học
     * @bodyParam short_description string Mô tả ngắn
     * @bodyParam link_video_youtube string Link video bài học
     * @bodyParam description string Nội dung bài học 
     * 
     */
    public function createLesson(Request $request)
    {

        $train_chapter_id = $request->train_chapter_id;

        $trainChapterExists = TrainChapter::where('id', $train_chapter_id)
            ->where('store_id', $request->store->id)->first();

        if ($trainChapterExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_TRAIN_CHAPTER[1],
                'msg' => MsgCode::NO_TRAIN_CHAPTER[1],
            ], 400);
        }

        if ($request->title == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_IS_REQUIRED[1],
                'msg' => MsgCode::TITLE_IS_REQUIRED[1],
            ], 400);
        }

        $lessionExists = TrainLesson::where('store_id', $request->store->id)
            ->where('train_chapter_id', $train_chapter_id)
            ->where('title', $request->title)->first();

        if ($lessionExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[1],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }
        
        $lessonCreate = TrainLesson::create([
            "store_id" => $request->store->id,
            "train_chapter_id" => $train_chapter_id,
            "title" => $request->title,
            "short_description" => $request->short_description,
            "link_video_youtube" => $request->link_video_youtube,
            "description" => $request->description,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  TrainLesson::where('id',  $lessonCreate->id)->first()
        ], 200);
    }



    /**
     * Cập nhật bài học
     * 
     * @bodyParam train_chapter_id int ID chương học
     * @bodyParam title string Tiêu đề bài học
     * @bodyParam short_description string Mô tả ngắn
     * @bodyParam link_video_youtube string Link video bài học
     * @bodyParam description string Nội dung bài học 
     * 
     */
    public function updateLesson(Request $request)
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

        $train_chapter_id = $request->train_chapter_id;

        $trainChapterExists = TrainChapter::where('id', $train_chapter_id)
            ->where('store_id', $request->store->id)->first();

        if ($trainChapterExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_LESSON[1],
                'msg' => MsgCode::NO_LESSON[1],
            ], 400);
        }

        if ($request->title == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_IS_REQUIRED[1],
                'msg' => MsgCode::TITLE_IS_REQUIRED[1],
            ], 400);
        }

        $lessionExists = TrainLesson::where('store_id', $request->store->id)
            ->where('train_chapter_id', $train_chapter_id)
            ->where('id', '!=', $lessonExist->id)
            ->where('title', $request->title)->first();

        if ($lessionExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[1],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }

        $lessonExist->update([
            "store_id" => $request->store->id,
            "train_chapter_id" => $train_chapter_id,
            "title" => $request->title,
            "short_description" => $request->short_description,
            "link_video_youtube" => $request->link_video_youtube,
            "description" => $request->description,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  TrainLesson::where('id',  $lessonExist->id)->first()
        ], 200);
    }


    /**
     * Xóa 1 bài học
     * 
     * 
     */
    public function deleteLesson(Request $request)
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

        $lessonExist->delete();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * 
     * Sắp xếp lại bài học
     * @bodyParam train_chapter_id int ID chương học
     * @bodyParam list_sort List gồm id cần sort và vị trí của nó [{ id:1, position:1 }, { id:2, position:2 }
     * 
     */
    public function sortLesson(Request $request)
    {

        $train_chapter_id = $request->train_chapter_id;

        $trainChapterExists = TrainChapter::where('id', $train_chapter_id)
            ->where('store_id', $request->store->id)->first();

        if ($trainChapterExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_TRAIN_CHAPTER[1],
                'msg' => MsgCode::NO_TRAIN_CHAPTER[1],
            ], 400);
        }

        if ($request->list_sort != null && is_array($request->list_sort)) {
            foreach ($request->list_sort  as $sort) {
                $id = $sort['id'] ?? null;
                $position = $sort['position'] ?? null;
                $less = TrainLesson::where('id', $id)->where('store_id', $request->store->id)->first();
                if ($less != null) {
                    $less->update([
                        'position' => $position
                    ]);
                }
            }
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Thêm 1 chương học
     * 
     * @bodyParam train_course_id int id khóa học
     * @bodyParam title string Tiêu đề chương học
     * @bodyParam short_description string Mô tả ngắn
     * 
     */
    public function createChapter(Request $request)
    {

        $train_course_id = $request->train_course_id;

        $courseExist = TrainCourse::where('id', $train_course_id)->where('store_id', $request->store->id)->first();

        if ($courseExist == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COURSE[1],
                'msg' => MsgCode::NO_COURSE[1],
            ], 400);
        }


        if ($request->title == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_IS_REQUIRED[1],
                'msg' => MsgCode::TITLE_IS_REQUIRED[1],
            ], 400);
        }

        $chapterExists = TrainChapter::where('store_id', $request->store->id)
            ->where('title', $request->title)->first();

        if ($chapterExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[1],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }

        $chapterCreate = TrainChapter::create([
            "store_id" => $request->store->id,
            "title" => $request->title,
            "short_description" => $request->short_description,
            "train_course_id" => $request->train_course_id
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  TrainChapter::where('id',  $chapterCreate->id)->first()
        ], 200);
    }



    /**
     * Cập nhật chương học
     * 
     * @bodyParam train_course_id int id khóa học
     * @bodyParam title string Tiêu đề bài học
     * @bodyParam short_description string Mô tả ngắn
     * 
     */
    public function updateChapter(Request $request)
    {

        $train_course_id = $request->train_course_id;

        $courseExist = TrainCourse::where('id', $train_course_id)->where('store_id', $request->store->id)->first();

        if ($courseExist == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COURSE[1],
                'msg' => MsgCode::NO_COURSE[1],
            ], 400);
        }


        $train_chapter_id = $request->route()->parameter('train_chapter_id');


        $chapterExist = TrainChapter::where('id', $train_chapter_id)->where('store_id', $request->store->id)->first();

        if ($chapterExist == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_TRAIN_CHAPTER[1],
                'msg' => MsgCode::NO_TRAIN_CHAPTER[1],
            ], 400);
        }


        if ($request->title == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_IS_REQUIRED[1],
                'msg' => MsgCode::TITLE_IS_REQUIRED[1],
            ], 400);
        }

        $chapterTitleExists = TrainChapter::where('store_id', $request->store->id)
            ->where('id','!=', $chapterExist->id)
            ->where('title', $request->title)->first();

        if ($chapterTitleExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[1],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }

        $chapterExist->update([
            "train_course_id" => $train_course_id,
            "store_id" => $request->store->id,
            "title" => $request->title,
            "short_description" => $request->short_description,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  TrainChapter::where('id',  $chapterExist->id)->first()
        ], 200);
    }


    /**
     * Xóa 1 chương học
     * 
     * 
     */
    public function deleteChapter(Request $request)
    {

        $train_chapter_id = request('train_chapter_id');

        $chapterExist = TrainChapter::where('id', $train_chapter_id)->where('store_id', $request->store->id)->first();

        if ($chapterExist == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_TRAIN_CHAPTER[1],
                'msg' => MsgCode::NO_TRAIN_CHAPTER[1],
            ], 400);
        }

        $chapterExist->delete();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * 
     * Sắp xếp lại chương học
     * @bodyParam list_sort List gồm id cần sort và vị trí của nó [{ id:1, position:1 }, { id:2, position:2 }
     * 
     */
    public function sortChapter(Request $request)
    {


        if ($request->list_sort != null && is_array($request->list_sort)) {
            foreach ($request->list_sort  as $sort) {
                $id = $sort['id'] ?? null;
                $position = $sort['position'] ?? null;
                $chap = TrainChapter::where('id', $id)->where('store_id', $request->store->id)->first();
                if ($chap != null) {
                    $chap->update([
                        'position' => $position
                    ]);
                }
            }
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
