<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\TrainCourse;
use App\Models\TrainLesson;
use App\Models\TrainQuiz;
use App\Models\TrainQuizQuestion;
use Illuminate\Http\Request;

/**
 * @group  Đào tạo/Trắc nghiệm
 */
class TrainQuizController extends Controller
{


    /**
     * Danh sách bài thi trắc nghiệm
     * 
     * @urlParam train_course_id int ID khóa học
     * 
     */
    public function getAllQuiz(Request $request)
    {

        $train_course_id = $request->route()->parameter('train_course_id');

        $all = TrainQuiz::where('store_id', $request->store->id)
            ->where('train_course_id',  $train_course_id)
            ->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $all
        ], 200);
    }


    /**
     * Thông tin 1 bài trắc nghiệm
     * 
     * 
     */
    public function getOneQuiz(Request $request)
    {

        $train_course_id = 0;
        $quiz_id = $request->route()->parameter('quiz_id');
        $quizIdExists = TrainQuiz::where('store_id', $request->store->id)
            ->where('id', $quiz_id)->first();

        if ($quizIdExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_QUIZ[1],
                'msg' => MsgCode::NO_QUIZ[1],
            ], 400);
        }


        $questions = TrainQuizQuestion::where('store_id', $request->store->id)
            ->where('quiz_id', $quiz_id)->get();

        $quizIdExists->questions = $questions;
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $quizIdExists
        ], 200);
    }


    /**
     * Thêm 1 bài thi trắc nghiệm
     * 
     * @urlParam train_course_id int ID khóa học
     * @bodyParam title string Tiêu đề bài thi
     * @bodyParam short_description string Mô tả ngắn
     * @bodyParam minute int số phút thi
     * @bodyParam show bool hiển thị bài thi hay không
     * @bodyParam auto_change_order_questions bool cho phép tự động đổi vị trí câu hỏi
     * @bodyParam auto_change_order_answer bool cho phép tự động đổi vị trí câu trả lời ABCD
     * 
     */
    public function createQuiz(Request $request)
    {

        $train_course_id = $request->route()->parameter('train_course_id');

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

        $quizExists = TrainQuiz::where('store_id', $request->store->id)
            ->where('train_course_id', $train_course_id)
            ->where('title', $request->title)->first();

        if ($quizExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[1],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }

        $quizCreate = TrainQuiz::create([
            "store_id" => $request->store->id,
            "train_course_id" => $train_course_id,
            "title" => $request->title,
            "short_description" => $request->short_description,
            "minute" => $request->minute,
            "show" => $request->show,
            "auto_change_order_questions" => $request->auto_change_order_questions,
            "auto_change_order_answer" => $request->auto_change_order_answer,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  TrainQuiz::where('id',  $quizCreate->id)->first()
        ], 200);
    }



    /**
     * Cập nhật bài thi
     * 
     * @urlParam train_course_id int ID khóa học
     * @bodyParam title string Tiêu đề bài thi
     * @bodyParam short_description string Mô tả ngắn
     * @bodyParam minute int số phút thi
     * @bodyParam show bool hiển thị bài thi hay không
     * @bodyParam auto_change_order_questions bool cho phép tự động đổi vị trí câu hỏi
     * @bodyParam auto_change_order_answer bool cho phép tự động đổi vị trí câu trả lời ABCD
     * 
     */
    public function updateQuiz(Request $request)
    {


        $train_course_id = $request->route()->parameter('train_course_id');


        $courseExist = TrainCourse::where('id', $train_course_id)->where('store_id', $request->store->id)->first();

        if ($courseExist == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COURSE[1],
                'msg' => MsgCode::NO_COURSE[1],
            ], 400);
        }

        $quiz_id = $request->route()->parameter('quiz_id');
        $quizIdExists = TrainQuiz::where('store_id', $request->store->id)
            ->where('id', $quiz_id)->first();

        if ($quizIdExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_QUIZ[1],
                'msg' => MsgCode::NO_QUIZ[1],
            ], 400);
        }


        if ($request->title == null && $request->count_answer_right_complete === null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_IS_REQUIRED[1],
                'msg' => MsgCode::TITLE_IS_REQUIRED[1],
            ], 400);
        }

        $quizTitleExists = TrainQuiz::where('store_id', $request->store->id)
            ->where('train_course_id', $train_course_id)
            ->where('id', '!=', $quiz_id)
            ->where('title', $request->title)->first();

        if ($quizTitleExists != null  && $request->count_answer_right_complete === null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[1],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }

        $quizIdExists->update(Helper::sahaRemoveItemArrayIfNullValue(
            [
                "store_id" => $request->store->id,
                "train_course_id" => $train_course_id,
                "title" => $request->title,
                "short_description" => $request->short_description,
                "minute" => $request->minute,
                "show" => $request->show,
                "auto_change_order_questions" => $request->auto_change_order_questions,
                "auto_change_order_answer" => $request->auto_change_order_answer,
                "count_answer_right_complete" => $request->count_answer_right_complete,
            ]
        ));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  TrainLesson::where('id',  $quizIdExists->id)->first()
        ], 200);
    }


    /**
     * Xóa 1 bài học
     * 
     * 
     */
    public function deleteQuiz(Request $request)
    {
        $train_course_id = 0;
        $quiz_id = $request->route()->parameter('quiz_id');
        $quizIdExists = TrainQuiz::where('store_id', $request->store->id)
            ->where('id', $quiz_id)->first();

        if ($quizIdExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_QUIZ[1],
                'msg' => MsgCode::NO_QUIZ[1],
            ], 400);
        }
        $quizIdExists->delete();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    function checkErrorRequsetQuestion($request)
    {
        if (empty($request->question) && empty($request->question_image)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[1],
                'msg' => "Không có câu hỏi",
            ], 400);
        }

        if (empty($request->answer_a) && empty($request->answer_b) && empty($request->answer_c) && empty($request->answer_d)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[1],
                'msg' => "Vui lòng điền đầy đủ câu trả lời",
            ], 400);
        }

        if ($request->right_answer != 'A' && $request->right_answer != 'B'  && $request->right_answer != 'C'  && $request->right_answer != 'D') {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[1],
                'msg' => "Câu trả lời đúng không hợp lệ",
            ], 400);
        }
    }

    /**
     * Thêm 1 câu trắc ngiệm
     * 
     * @bodyParam quiz_id int id bài thi
     * @bodyParam question string Câu hỏi
     * @bodyParam question_image string Ảnh câu hỏi
     * @bodyParam answer_a string Câu trả lời A
     * @bodyParam answer_b string Câu trả lời B
     * @bodyParam answer_c string Câu trả lời C
     * @bodyParam answer_d string Câu trả lời D
     * @bodyParam right_answer string Câu trả lời đúng (A,B,C,D)
     * 
     */
    public function createQuestion(Request $request)
    {


        $err = $this->checkErrorRequsetQuestion($request);

        if ($err  != null) return  $err;

        $quiz_id = $request->route()->parameter('quiz_id');


        $chapterCreate = TrainQuizQuestion::create([
            "store_id" => $request->store->id,
            "quiz_id" => $quiz_id,
            "question" => $request->question,
            "question_image" => $request->question_image,
            "answer_a" => $request->answer_a,
            "answer_b" => $request->answer_b,
            "answer_c" => $request->answer_c,
            "answer_d" => $request->answer_d,
            "right_answer" => $request->right_answer
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  TrainQuizQuestion::where('id',  $chapterCreate->id)->first()
        ], 200);
    }



    /**
     * Cập nhật cau hỏi
     * 
     * @bodyParam quiz_id int id bài thi
     * @bodyParam question string Câu hỏi
     * @bodyParam question_image string Ảnh câu hỏi
     * @bodyParam answer_a string Câu trả lời A
     * @bodyParam answer_b string Câu trả lời B
     * @bodyParam answer_c string Câu trả lời C
     * @bodyParam answer_d string Câu trả lời D
     * @bodyParam right_answer string Câu trả lời đúng (A,B,C,D)
     * 
     */
    public function updateQuestion(Request $request)
    {

        $err = $this->checkErrorRequsetQuestion($request);

        if ($err  != null) return  $err;

        $question_id = $request->route()->parameter('question_id');
        $questionExists = TrainQuizQuestion::where('store_id', $request->store->id)
            ->where('id', $question_id)->first();


        $questionExists->update([
            "question" => $request->question,
            "question_image" => $request->question_image,
            "answer_a" => $request->answer_a,
            "answer_b" => $request->answer_b,
            "answer_c" => $request->answer_c,
            "answer_d" => $request->answer_d,
            "right_answer" => $request->right_answer
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  TrainQuizQuestion::where('id',  $questionExists->id)->first()
        ], 200);
    }


    /**
     * Xóa 1 câu hỏi
     * 
     * 
     */
    public function deleteQuestion(Request $request)
    {

        $question_id = $request->route()->parameter('question_id');
        $questionExists = TrainQuizQuestion::where('store_id', $request->store->id)
            ->where('id', $question_id)->first();

        if ($questionExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_TRAIN_CHAPTER[1],
                'msg' => MsgCode::NO_TRAIN_CHAPTER[1],
            ], 400);
        }

        $questionExists->delete();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
