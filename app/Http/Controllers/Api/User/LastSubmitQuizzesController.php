<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LastSubmitQuiz;
use App\Models\MsgCode;
use App\Models\TrainQuiz;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LastSubmitQuizzesController extends Controller
{
    /**
     * 
     * Lịch sử  DS khách hàng làm bài thi
     * 
     * @urlParam  store_code required Store code. Example: kds
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên,số điện thoại cần tìm VD: covid 19
     * @queryParam  train_course_ids DS khóa học VD: train_course_ids=1,2
     * 
     */
    public function getHistoryQuizzes(Request $request)
    {
        $train_course_ids = request("train_course_ids") ? explode(',', request("train_course_ids")) : [];

        $customer_ids = LastSubmitQuiz::where('store_id',  $request->store->id)
            ->where('customer_id',  '!=', null)
            ->distinct()
            ->pluck('customer_id');

        $customers = Customer::sortByRelevance(true)
            ->where('store_id', $request->store->id)
            ->when(count($customer_ids) > 0, function ($query) use ($customer_ids) {
                $query->whereIn('customers.id', $customer_ids);
            })
            ->select('customers.id', 'customers.name', 'customers.phone_number', 'customers.email')
            ->selectSub(
                // Tính tổng số bài quiz
                function ($query) use ($train_course_ids, $request) {
                    $query->from('train_quizzes')
                        ->where('store_id', $request->store->id)
                        ->when(count($train_course_ids) > 0, function ($q) use ($train_course_ids) {
                            $q->whereIn('train_course_id', $train_course_ids);
                        })
                        ->selectRaw('COUNT(train_quizzes.id) as count_quizzes');
                },
                'count_quizzes'
            )
            ->selectSub(
                // Tính tổng số bài thi đã thi
                function ($query) use ($train_course_ids, $request) {
                    $query->from('last_submit_quizzes')
                        ->where('last_submit_quizzes.store_id', $request->store->id)
                        ->when(count($train_course_ids) > 0, function ($q) use ($train_course_ids) {
                            $q->join('train_quizzes', 'last_submit_quizzes.quiz_id', '=', 'train_quizzes.id')
                                ->whereIn('train_quizzes.train_course_id', $train_course_ids);
                        })
                        ->whereColumn('last_submit_quizzes.customer_id', 'customers.id')
                        ->selectRaw('COUNT(DISTINCT last_submit_quizzes.quiz_id) as count_quizzes_submit');
                },
                'count_quizzes_submit'
            )
            ->selectSub(
                // Tính tổng số bài thi đã vượt qua
                function ($query) use ($train_course_ids, $request) {
                    $query->from('last_submit_quizzes')
                        ->where('last_submit_quizzes.store_id', $request->store->id)
                        ->join('train_quizzes', 'last_submit_quizzes.quiz_id', '=', 'train_quizzes.id')
                        ->when(count($train_course_ids) > 0, function ($q) use ($train_course_ids) {
                            $q->whereIn('train_quizzes.train_course_id', $train_course_ids);
                        })
                        ->whereColumn('last_submit_quizzes.customer_id', 'customers.id')
                        ->selectRaw('COUNT(DISTINCT CASE WHEN last_submit_quizzes.total_correct_answer >= train_quizzes.count_answer_right_complete THEN last_submit_quizzes.quiz_id END) as count_quizzes_submit_completed');
                },
                'count_quizzes_submit_completed'
            )
            ->search(request('search'))
            ->paginate(request('limit') ?: 20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $customers
        ], 200);
    }

    /**
     * 
     * Lịch sử chi tiết của một khách hàng làm bài thi
     * 
     * @urlParam  store_code required Store code. Example: kds
     * @urlParam  customer_id required ID khách hàng.
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên,số điện thoại cần tìm VD: covid 19
     * @queryParam  train_course_ids DS khóa học VD: train_course_ids=1,2
     * @queryParam  status 1 Đã hoàn thành 2: chưa hoàn thành
     */
    public function getDetailHistoryQuizzesForCustomer(Request $request)
    {
        $train_course_ids = request("train_course_ids") ? explode(',', request("train_course_ids")) : [];
        $status = $request->status;
        $customer_id = $request->route()->parameter('customer_id');
        $customer_exists = Customer::where('id', $customer_id)
            ->where('store_id', $request->store->id)
            ->exists();

        if (!$customer_exists) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 404);
        }

        $quizz_ids = LastSubmitQuiz::where('store_id',  $request->store->id)
            ->where('customer_id',  $customer_id)
            ->distinct()
            ->pluck('quiz_id');

        $quizzes = TrainQuiz::sortByRelevance(true)
            ->with(['train_course', 'last_submit_quizzes' => function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id);
            }])
            ->where('store_id', $request->store->id)
            ->whereIn('id', $quizz_ids)
            ->when(count($train_course_ids) > 0, function ($query) use ($train_course_ids) {
                $query->whereIn('train_course_id', $train_course_ids);
            })
            ->when($status, function ($query) use ($status, $customer_id, $request) {
                if ($status == 1) {
                    $query->whereHas('last_submit_quizzes', function ($subQuery) use ($customer_id, $request) {
                        $subQuery->where('last_submit_quizzes.customer_id', $customer_id)
                            ->where('last_submit_quizzes.store_id', $request->store->id)
                            ->whereColumn('quiz_id', 'train_quizzes.id')
                            ->whereColumn('total_correct_answer', '>=', 'train_quizzes.count_answer_right_complete');
                    });
                } elseif ($status == 2) {
                    $query->whereDoesntHave('last_submit_quizzes', function ($subQuery) use ($customer_id, $request) {
                        $subQuery->where('last_submit_quizzes.customer_id', $customer_id)
                            ->where('last_submit_quizzes.store_id', $request->store->id)
                            ->whereColumn('quiz_id', 'train_quizzes.id')
                            ->whereColumn('total_correct_answer', '>=', 'train_quizzes.count_answer_right_complete');
                    });
                }
            })
            ->select(
                'train_quizzes.*',
                DB::raw('(SELECT MAX(total_correct_answer)
                    FROM last_submit_quizzes
                    WHERE last_submit_quizzes.quiz_id = train_quizzes.id
                    AND last_submit_quizzes.store_id = ' . $request->store->id . '
                    AND last_submit_quizzes.customer_id = ' . $customer_id . '
                ) as total_correct_answer_max'),
                DB::raw('(CASE WHEN EXISTS (
                    SELECT 1
                    FROM last_submit_quizzes
                    WHERE last_submit_quizzes.quiz_id = train_quizzes.id
                    AND last_submit_quizzes.total_correct_answer >= train_quizzes.count_answer_right_complete
                    AND last_submit_quizzes.store_id = ' . $request->store->id . '
                    AND last_submit_quizzes.customer_id = ' . $customer_id . '
                ) THEN 1 ELSE 0 END) as is_completed'),
            )
            ->search(request('search'))
            ->paginate(request('limit') ?: 20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $quizzes
        ], 200);
    }

    /**
     * 
     * Lịch sử chi tiết những lần làm bài thi của khách hàng
     * 
     * @urlParam  store_code required Store code. Example: kds
     * @urlParam  customer_id required ID khách hàng.
     * @urlParam  quiz_id required ID bài quiz.
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  status 1 Đã hoàn thành 2: chưa hoàn thành
     */
    public function getDetailQuizHistoryForCustomer(Request $request)
    {
        $customer_id = $request->route()->parameter('customer_id');
        $customer_exists = Customer::where('id', $customer_id)
            ->where('store_id', $request->store->id)
            ->exists();

        if (!$customer_exists) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 404);
        }

        $quiz_id = $request->route()->parameter('quiz_id');
        $quiz_exists = TrainQuiz::where('id', $quiz_id)
            ->where('store_id', $request->store->id)
            ->first();

        if (!$quiz_exists) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_QUIZ[1],
                'msg' => MsgCode::NO_QUIZ[1],
            ], 400);
        }

        $last_submit_quiz = LastSubmitQuiz::with('train_quiz')
            ->where('store_id',  $request->store->id)
            ->where('customer_id',  $customer_id)
            ->where('quiz_id',  $quiz_exists->id)
            ->orderBy('id', 'desc')
            ->paginate(request('limit') ?: 20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[1],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $last_submit_quiz
        ], 200);
    }
}
