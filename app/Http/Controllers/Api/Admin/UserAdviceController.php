<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\HistoryConsultant;
use App\Models\UserAdvice;
use App\Models\MsgCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;

/**
 * @group  Admin/Khách hàng cần tư vấn
 */
class UserAdviceController extends Controller
{
    /**
     * Khách hàng cần tư vấn
     * @urlParam search string search
     * @urlParam status int status
     * @urlParam limit int số user mỗi trang
     * @urlParam id_employee_help Id nhân viên hõ trọ
     * @queryParam  begin_date_register Ngày đăng ký từ
     * @queryParam  end_date_register Ngày đăng ký đến
     * 
     */
    public function getAll(Request $request)
    {

        $users = UserAdvice::sortByRelevance(true)

            ->when(UserAdvice::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when(request('sort_by') == null, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->when(request('status') != null && request('status') != -1, function ($query) {
                $query->where('status', request('status'));
            })
            ->when(request('status') == -1, function ($query) {
                $query->whereIn('status', [1, 5, 6, 7]);
            })

            ->when($request->employee != null && $request->employee->id_decentralization == 1, function ($query) use ($request) {
                $query->where('id_employee_help', $request->employee->id);
            })
            ->when($request->id_employee_help != null, function ($query) use ($request) {
                $query->where('id_employee_help', $request->id_employee_help);
            })

            ->when(request('begin_date_register') != null, function ($query) {
                $t2 =  Helper::get_begin_date_string(new Carbon(request('begin_date_register')));
                $query->where('created_at', '>=', $t2);
            })
            ->when(request('end_date_register') != null, function ($query) {
                $t1 =  Helper::get_end_date_string(new Carbon(request('end_date_register')));
                $query->where('created_at', '<=', $t1);
            })

            ->when(request('begin_updated') != null, function ($query) {
                $t2 =  Helper::get_begin_date_string(new Carbon(request('begin_updated')));
                $query->where('updated_at', '>=', $t2);
            })
            ->when(request('end_updated') != null, function ($query) {
                $t1 =  Helper::get_end_date_string(new Carbon(request('end_updated')));
                $query->where('updated_at', '<=', $t1);
            })

            ->search(request('search'))
            ->paginate(request('limit') == null ? 20 : request('limit'));

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' =>  $users,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Thêm 1 user cần tư vấn
     * @bodyParam status int 0 chưa xử lý, 1 đang hỗ trợ, 2 thành công, 3 thất bại
     * @bodyParam username string tên gợi nhớ
     * @bodyParam phone_number string Số điện thoại
     * @bodyParam email string Email
     * @bodyParam name string Tên đầy đủ
     * @bodyParam salary string Lương
     * @bodyParam id_employee_help Id nhân viên hõ trọ
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
     * @bodyParam id_decentralization int id phân quyền (ủy quyền cho user cần tư vấn)
     * @bodyParam consultation_1 string Lần tư vấn 1
     * @bodyParam consultation_2 string Lần tư vấn 2
     * @bodyParam consultation_3 string Lần tư vấn 3
     */
    public function create(Request $request)
    {


        if (UserAdvice::where('phone_number', $request->phone_number)->exists()) {
            return response()->json([
                'code' => 409,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 409);
        }


        $userAdvice_created = UserAdvice::create([
            'area_code' => '+84',
            'username' => $request->username,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'name' => $request->name,
            'note' => $request->note,
            'avatar_image' => $request->avatar_image,
            'id_employee_help' => $request->id_employee_help,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'sex' => $request->sex,
            'status' => 0
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => UserAdvice::where('id', '=',   $userAdvice_created->id)->first(),
        ], 200);
    }


    /**
     * Thêm nhiều user tu van
     * 
     * @bodyParam allow_skip_same_name bool required Có bỏ qua khách hàng tư vấn trùng tên không (Không bỏ qua sẽ replace khách hàng tư vấn trùng tên)
     * @bodyParam list List required List danh sách khách hàng tư vấn  (item json như thêm 1 userAdvice)
     * @bodyParam item userAdvice thêm {category_name}
     * 
     */
    public function createManyUserAdvice(Request $request)
    {
        $allow_skip_same_name = filter_var($request->allow_skip_same_name, FILTER_VALIDATE_BOOLEAN);

        $list = $request->list;

        $total_user_advices_request = 0;
        $total_skip_same_name = 0;
        $total_changed_same_name = 0;
        $total_failed = 0;
        $total_new_add = 0;


        if (is_array($list) && count($list) > 0) {

            $total_user_advices_request = count($list);
            foreach ($list as $pro) {

                $username = $pro['username'] ?? null;
                $phone_number = $pro['phone_number'];
                $email = $pro['email'] ?? null;
                $name = $pro['name'] ?? null;
                $note = $pro['note'] ?? null;
                $id_employee_help = $pro['id_employee_help'] ?? null;
                $date_of_birth = $pro['date_of_birth'] ?? null;
                $address = $pro['address'] ?? null;
                $sex = $pro['sex'] ?? null;
                $status = $pro['status'] ?? null;
                $avatar_image = $pro['avatar_image'] ?? null;

                $consultation_1 = $pro['consultation_1'] ?? null;
                $consultation_2 = $pro['consultation_2'] ?? null;
                $consultation_3 = $pro['consultation_3'] ?? null;




                try {
                    $userAdviceExists = UserAdvice::where(
                        'phone_number',
                        $phone_number
                    )->first();


                    $is_change = false;
                    if ($userAdviceExists != null) {


                        $total_skip_same_name++;
                        continue;
                    }


                    $userAdviceCreate = null;
                    if ($userAdviceExists != null) {
                        $userAdviceCreate = $userAdviceExists->update(
                            Helper::sahaRemoveItemArrayIfNullValue([
                                'username' => $username,
                                'phone_number' => $phone_number,
                                'email' => $email,
                                'name' => $name,
                                'note' => $note,
                                'id_employee_help' => $id_employee_help,
                                'date_of_birth' => $date_of_birth,
                                'address' => $address,
                                'sex' => $sex,
                                'status' => $status,
                                'avatar_image' => $avatar_image,

                                'consultation_1' => $consultation_1,
                                'consultation_2' => $consultation_2,
                                'consultation_3' => $consultation_3,
                            ])

                        );


                        $userAdviceCreate =   $userAdviceExists = UserAdvice::where(
                            'id',
                            $userAdviceExists->id
                        )->first();
                    } else {

                        $userAdviceCreate = UserAdvice::create(
                            [
                                'area_code' => '+84',
                                'username' => $username,
                                'phone_number' => $phone_number,
                                'email' => $email,
                                'name' => $name,
                                'note' => $note,
                                'id_employee_help' => $id_employee_help,
                                'date_of_birth' => $date_of_birth,
                                'address' => $address,
                                'sex' => $sex,
                                'status' => $status,
                                'consultation_1' => $consultation_1,
                                'consultation_2' => $consultation_2,
                                'consultation_3' => $consultation_3,
                            ]
                        );
                        $total_new_add++;
                    }
                } catch (Exception $e) {


                    $total_failed++;
                }
            }
        }


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "allow_skip_same_name" => $allow_skip_same_name,
                "total_user_advices_request" => $total_user_advices_request,
                "total_skip_same_name"  => $total_skip_same_name,
                "total_changed_same_name" =>  $total_changed_same_name,
                "total_failed" => $total_failed,
                "total_new_add" =>  $total_new_add,
            ]
        ], 201);
    }

    /**
     * Cập nhật thông tin user cần tư vấn
     * @bodyParam id_employee_help int id nhân viên sale hỗ trợ
     * @bodyParam username string tên gợi nhớ
     * @bodyParam phone_number string Số điện thoại
     * @bodyParam email string Email
     * @bodyParam name string Tên đầy đủ
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
     * @bodyParam consultation_1 string Lần tư vấn 1
     * @bodyParam consultation_2 string Lần tư vấn 2
     * @bodyParam consultation_3 string Lần tư vấn 3
     */
    public function update(Request $request)
    {

        $userAdvice_id = request("userAdvice_id");
        $userAdviceExists = UserAdvice::where(
            'id',
            $userAdvice_id
        )
            ->first();


        if ($userAdviceExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ], 400);
        }

        if (UserAdvice::where('phone_number', $request->phone_number)
            ->where('id', '<>', $userAdvice_id)->exists()
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 400);
        }


        if ($userAdviceExists->consultation_1 != $request->consultation_1) {
            $userAdviceExists->update(
                [
                    'time_update_consultation_1' => Helper::getTimeNowString(),
                ]
            );
        }
        if ($userAdviceExists->consultation_2 != $request->consultation_2) {
            $userAdviceExists->update(
                [
                    'time_update_consultation_2' => Helper::getTimeNowString(),
                ]
            );
        }
        if ($userAdviceExists->consultation_3 != $request->consultation_3) {
            $userAdviceExists->update(
                [
                    'time_update_consultation_3' => Helper::getTimeNowString(),
                ]
            );
        }

        if ($userAdviceExists->note != $request->note) {
            $userAdviceExists->update(
                [
                    'time_update_note' => Helper::getTimeNowString(),
                ]
            );
        }

        $userAdviceExists->update(
            [
                'area_code' => '+84',
                'username' => $request->username,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'name' => $request->name,
                'note' => $request->note,
                'id_employee_help' => $request->id_employee_help,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address,
                'sex' => $request->sex,
                'status' => $request->status,
                'avatar_image' => $request->avatar_image,

                'consultation_1' => $request->consultation_1,
                'consultation_2' => $request->consultation_2,
                'consultation_3' => $request->consultation_3,
            ]
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => UserAdvice::where('id', '=',   $userAdviceExists->id)->first(),
        ], 200);
    }

    /**
     * Lấy thông tin user cần tư vấn
     * @queryParam user_advice_id int id hỗ trợ
     */
    public function detail(Request $request)
    {

        $userAdvice_id = request("user_advice_id");
        $userAdviceExists = UserAdvice::where(
            'id',
            $userAdvice_id
        )
            ->first();


        if ($userAdviceExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $userAdviceExists,
        ], 200);
    }

    /**
     * Cập nhật nhiều user thông tin user cần tư vấn
     * @bodyParam id_employee_help int id nhân viên sale hỗ trợ
     * @bodyParam username string tên gợi nhớ
     * @bodyParam phone_number string Số điện thoại
     * @bodyParam email string Email
     * @bodyParam name string Tên đầy đủ
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
     * @bodyParam consultation_1 string Lần tư vấn 1
     * @bodyParam consultation_2 string Lần tư vấn 2
     * @bodyParam consultation_3 string Lần tư vấn 3
     * 
     */
    public function updateMany(Request $request)
    {


        foreach ($request->list as $item) {
            $idUser = $item['id'] ?? null;

            if ($idUser != null) {

                $userAdviceExists = UserAdvice::where(
                    'id',
                    $idUser
                )
                    ->first();


                if ($userAdviceExists != null) {


                    $userAdviceExists->update(Helper::sahaRemoveItemArrayIfNullValue(
                        [
                            'area_code' => $item['area_code'] ?? '+84',
                            'username' => $item['username'] ?? null,
                            'phone_number' => $item['phone_number'] ?? null,
                            'email' => $item['email'] ?? null,
                            'name' => $item['name'] ?? null,
                            'note' => $item['note'] ?? null,
                            'id_employee_help' => $item['id_employee_help'] ?? null,
                            'date_of_birth' => $item['date_of_birth'] ?? null,
                            'address' => $item['address'] ?? null,
                            'sex' => $item['sex'] ?? null,
                            'status' => $item['status'] ?? null,
                            'avatar_image' => $item['avatar_image'] ?? null,

                            'consultation_1' => $item['consultation_1'],
                            'consultation_2' => $item['consultation_2'],
                            'consultation_3' => $item['consultation_3'],
                        ]
                    ));
                }
            }
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Xóa 1 user cần tư vấn
     */
    public function delete(Request $request)
    {

        $userAdvice_id = request("userAdvice_id");
        $userAdviceExists = UserAdvice::where(
            'id',
            $userAdvice_id
        )
            ->first();

        if ($userAdviceExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 404);
        }

        $idDeleted = $userAdviceExists->id;
        $userAdviceExists->delete();



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }

    /**
     * Thêm lịch sử tư vấn
     * @queryParam user_advice_id int mã tư vấn khách hàng
     * @bodyParam content string nội dung tư vấn
     */
    public function addHistoryUserAdvice(Request $request)
    {
        $userAdvice_id = request("user_advice_id");
        $userAdviceExists = UserAdvice::where('id', $userAdvice_id)
            ->first();

        if ($userAdviceExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ADVICE_USER_EXISTS[0],
                'msg' => MsgCode::NO_ADVICE_USER_EXISTS[1],
            ], 404);
        }

        if ($request->content == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::CONTENT_IS_REQUIRED[0],
                'msg' => MsgCode::CONTENT_IS_REQUIRED[1],
            ], 404);
        }

        $historyConsultant = HistoryConsultant::create([
            'user_advice_id' => (int)$userAdvice_id,
            'status' => $userAdviceExists->status,
            'content' => $request->content,
            'time_consultant' => Helper::getTimeNowCarbon()->format('Y-m-d H:i:s'),
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $historyConsultant,
        ], 200);
    }

    /**
     * Lấy lịch sử tư vấn
     * @queryParam user_advice_id int mã tư vấn khách hàng
     */
    public function getHistoryUserAdvice(Request $request)
    {
        $userAdvice_id = request("user_advice_id");
        $userAdviceExists = UserAdvice::where('id', $userAdvice_id)
            ->first();

        if ($userAdviceExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ADVICE_USER_EXISTS[0],
                'msg' => MsgCode::NO_ADVICE_USER_EXISTS[1],
            ], 404);
        }

        $listHistoryConsultant = HistoryConsultant::where('user_advice_id', $userAdvice_id)->orderByDesc('time_consultant')->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listHistoryConsultant,
        ], 200);
    }
}
