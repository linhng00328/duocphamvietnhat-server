<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Models\Comment;
use App\Models\MsgCode;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\LastSubmitQuiz;
use App\Models\LastWatchLesson;
use App\Models\TrainChapter;
use App\Models\TrainCourse;
use App\Models\TrainLesson;
use App\Models\TrainQuiz;
use App\Models\TrainQuizQuestion;
use Illuminate\Http\Request;

/**
 * @group  Đào tạo/Trắc nghiệm
 */
class CustomerTrainQuizController extends Controller
{

    /**
     * Danh sách bài thi trắc nghiệm
     * 
     * 
     */
    public function getAllQuiz(Request $request)
    {
        $train_course_id = $request->route()->parameter('train_course_id');

        $all = TrainQuiz::where('store_id', $request->store->id)
            ->where('show',  true)
            ->where('train_course_id',  $train_course_id)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($all as $itemQuiz) {
            $last =  LastSubmitQuiz::where('quiz_id',  $itemQuiz->id)
                ->where('customer_id',  $request->customer->id)
                ->where('store_id',  $request->store->id)

                ->orderBy('id', 'desc')
                ->first();

            $itemQuiz->last_submit_quiz =  $last;
        }

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
        function intToABCD($num)
        {
            if ($num == 0) return "A";
            if ($num == 1) return "B";
            if ($num == 2) return "C";
            if ($num == 3) return "D";
            return 'A';
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

        $auto_change_order_questions = $quizIdExists->auto_change_order_questions;
        $auto_change_order_answer = $quizIdExists->auto_change_order_answer;

        $questions = TrainQuizQuestion::where('store_id', $request->store->id)
            ->where('quiz_id', $quiz_id)->get();

        $questions_new = [];

        $define_sort_answers = '';

        foreach ($questions  as  $question) {

            $answers = [];
            array_push($answers, $question->answer_a);
            array_push($answers, $question->answer_b);
            array_push($answers, $question->answer_c);
            array_push($answers, $question->answer_d);

            $arr_shuffle = [0, 1, 2, 3];
            if ($auto_change_order_answer  == true) {
                shuffle($arr_shuffle);
            }

            array_push($questions_new,  [
                'question_id' =>   $question->id,
                'question' =>   $question->question,
                'question_image' =>   $question->question_image,
                "answers" => [
                    "A" =>  $answers[$arr_shuffle[0]],
                    "B" =>  $answers[$arr_shuffle[1]],
                    "C" =>  $answers[$arr_shuffle[2]],
                    "D" =>  $answers[$arr_shuffle[3]],
                ]
            ]);


            $define_sort_answers =   $define_sort_answers . ($question->id) . "-A:" . intToABCD($arr_shuffle[0]) . ",B:" . intToABCD($arr_shuffle[1]) . ",C:" . intToABCD($arr_shuffle[2]) . ",D:" . intToABCD($arr_shuffle[3]) . "|";
        }
        if ($auto_change_order_questions  == true) {
            shuffle($questions_new);
        }


        $quizIdExists->define_sort_answers =    $define_sort_answers;
        $quizIdExists->questions = $questions_new;
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $quizIdExists
        ], 200);
    }


    /**
     * 
     * Nộp bài trắc nghiệm
     * 
     * @bodyParam work_time int work_time giây làm bài
     * @bodyParam define_sort_answers int chuỗi câu trả lời định nghĩa lại
     * @bodyParam answers list danh sách câu trả lời dạng [ { "question_id": 5,  "answer": "A" }  ]
     * 
     */
    public function submitQuiz(Request $request)
    {

        $total_questions = 0;
        $total_correct_answer = 0;
        $total_wrong_answer = 0;

        $history_submit_quizzes = [];
        function checkAnswer($request, $question_id, $right_answer, $question)
        {
            //Lấy đáp án gửi lên
            $answer_request = "";
            foreach ($request->answers as $answer_r) {
                if ($answer_r['question_id'] == $question_id) {
                    $answer_request = $answer_r['answer'];
                }
            }

            //
            $question_after_with_abcd["A"] = $question->answer_a;
            $question_after_with_abcd['B'] = $question->answer_b;
            $question_after_with_abcd['C'] = $question->answer_c;
            $question_after_with_abcd['D'] = $question->answer_d;

            //Lấy đáp án đúng 
            $right_answer_after_sort = "";

            $answer_a = "";
            $answer_b = "";
            $answer_c = "";
            $answer_d = "";


            $sortList = explode("|", $request->define_sort_answers);
            foreach ($sortList as $s) {

                if ($s != '') {
                    $question_id_sort = (int) explode("-", $s)[0];
                    $answers_abcd = explode("-", $s)[1];
                    if ($question_id_sort == $question_id) { //kiem tra cau hoi trung nhau

                        foreach (explode(",", $answers_abcd) as $an) {

                            $answer1 = explode(":", $an)[0]; //câu cỏi ban đầu
                            $answerConvert = explode(":", $an)[1]; //câu trả lời đúng sau khi chuyển

                            if ($answerConvert == "A") {
                                $answer_a = $question_after_with_abcd[$answer1] ?? "";
                            }
                            if ($answerConvert == "B") {
                                $answer_b = $question_after_with_abcd[$answer1] ?? "";
                            }
                            if ($answerConvert == "C") {
                                $answer_c = $question_after_with_abcd[$answer1] ?? "";
                            }
                            if ($answerConvert == "D") {
                                $answer_d = $question_after_with_abcd[$answer1] ?? "";
                            }


                            if (
                                $right_answer ==  $answerConvert
                            ) {
                                $right_answer_after_sort =   $answer1;
                            }
                        }
                    }
                }
            }

            return [
                'right_answer' =>    $right_answer_after_sort,
                'answer_request' =>  $answer_request,
                'is_valid' => $right_answer_after_sort  == $answer_request,
                "answer_a" => $answer_a,
                "answer_b" => $answer_b,
                "answer_c" => $answer_c,
                "answer_d" => $answer_d,
            ];
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


        $questions = TrainQuizQuestion::where('store_id', $request->store->id)
            ->where('quiz_id', $quiz_id)->get();



        foreach ($questions  as    $question) {
            $data_valid = checkAnswer($request, $question->id, $question->right_answer, $question);

            $total_questions++;
            if ($data_valid['is_valid'] == true) {
                $total_correct_answer++;
            } else {
                $total_wrong_answer++;
            }

            $data_valid['question'] = [
                'id' => $question->id,
                'question' => $question->question,
                'question_image' => $question->question_image,
            ];

            array_push($history_submit_quizzes,  $data_valid);
        }

        $lastSubmitQuiz =    LastSubmitQuiz::create([
            'quiz_id' =>  $quiz_id,
            "work_time" => $request->work_time,
            'customer_id' => $request->customer->id,
            'store_id' => $request->store->id,
            "total_questions" => $total_questions,
            "total_correct_answer" => $total_correct_answer,
            "total_wrong_answer"  => $total_wrong_answer,
            'history_submit_quizzes_json' => json_encode($history_submit_quizzes),
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => LastSubmitQuiz::where('id',  $lastSubmitQuiz->id)->orderBy('id', 'desc')->first()
        ], 200);
    }


    /**
     * 
     * Lịch sử bài làm
     * 
     */
    public function historySubmit(Request $request)
    {

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

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => LastSubmitQuiz::where('customer_id',  $request->customer->id)
            ->orderBy('id', 'desc')
                ->where('quiz_id',  $quiz_id)
                ->paginate(20)
        ], 200);
    }
}
