<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\PhoneUtils;
use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerSale;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Exception;

/**
 * @group  User/Khách hàng onsale
 */
class CustomerSaleController extends Controller
{
    /**
     * Khách hàng cần tư vấn
     * @urlParam search string search
     * @urlParam status int status
     * @urlParam limit int số user mỗi trang
     * @urlParam staff_id Id nhân viên hõ trọ
     */
    public function getAll(Request $request)
    {

        $customer_sales = CustomerSale::sortByRelevance(true)
            ->where('store_id', $request->store->id)
            ->when(CustomerSale::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when(request('sort_by') == null, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->when(request('status') != null, function ($query) {
                $query->where('status', request('status'));
            })
            ->when($request->employee != null && $request->employee->id_decentralization == 1, function ($query) use ($request) {
                $query->where('staff_id', $request->employee->id);
            })
            ->when($request->staff_id != null, function ($query) use ($request) {
                $query->where('staff_id', $request->staff_id);
            })
            ->when($request->staff != null, function ($query) use ($request) {
                $query->where('staff_id', $request->staff->id);
            })
            ->search(request('search'))
            ->paginate(request('limit') == null ? 20 : request('limit'));



        $custom = collect(
            [
                'total_status_0' => CustomerSale::where('store_id', $request->store->id)->where('status', 0)->when($request->staff != null, function ($query) use ($request) {
                    $query->where('staff_id', $request->staff->id);
                })->count(),
                'total_status_1' => CustomerSale::where('store_id', $request->store->id)->where('status', 1)->when($request->staff != null, function ($query) use ($request) {
                    $query->where('staff_id', $request->staff->id);
                })->count(),
                'total_status_2' => CustomerSale::where('store_id', $request->store->id)->where('status', 2)->when($request->staff != null, function ($query) use ($request) {
                    $query->where('staff_id', $request->staff->id);
                })->count(),
                'total_status_3' => CustomerSale::where('store_id', $request->store->id)->where('status', 3)->when($request->staff != null, function ($query) use ($request) {
                    $query->where('staff_id', $request->staff->id);
                })->count(),
            ]
        );

        $data = $custom->merge($customer_sales);

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' =>      $data,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Thêm 1 khách hàng cần tư vấn
     * @bodyParam status int 0 chưa xử lý, 1 đang hỗ trợ, 2 thành công, 3 thất bại
     * @bodyParam phone_number string Số điện thoại
     * @bodyParam email string Email
     * @bodyParam name string Tên đầy đủ
     * @bodyParam address string Địa chỉ
     * @bodyParam staff_id Id nhân viên hõ trọ
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
     * @bodyParam consultation_1 string Lần tư vấn 1
     * @bodyParam consultation_2 string Lần tư vấn 2
     * @bodyParam consultation_3 string Lần tư vấn 3
     */
    public function create(Request $request)
    {


        if (CustomerSale::where('phone_number', $request->phone_number)->exists()) {
            return response()->json([
                'code' => 409,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 409);
        }


        $CustomerSale_created = CustomerSale::create([
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'name' => $request->name,
            'note' => $request->note,
            'avatar_image' => $request->avatar_image,
            'staff_id' => $request->staff_id,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'sex' => $request->sex,
            'status' => 0,
            'store_id' => $request->store->id,
            'staff_id' => $request->staff != null ? $request->staff->id : null
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CustomerSale::where('id', '=',   $CustomerSale_created->id)->first(),
        ], 200);
    }


    /**
     * Thêm nhiều khách hàng tu van
     * 
     * @bodyParam allow_skip_same_phone_number bool required Có bỏ qua khách hàng tư vấn trùng sdt không (Không bỏ qua sẽ replace khách hàng tư vấn trùng tên)
     * @bodyParam list List required List danh sách khách hàng tư vấn  (item json như thêm 1 CustomerSale)
     * @bodyParam item CustomerSale thêm {category_name}
     * 
     */
    public function createManyCustomerSale(Request $request)
    {
        $allow_skip_same_phone_number = filter_var($request->allow_skip_same_phone_number, FILTER_VALIDATE_BOOLEAN);

        $list = $request->list;

        $total_customer_sale_request = 0;
        $total_skip_same_phone_number = 0;
        $total_changed_same_phone_number = 0;
        $total_failed = 0;
        $total_new_add = 0;


        if (is_array($list) && count($list) > 0) {

            $total_customer_sale_request = count($list);

            foreach ($list as $pro) {

                $phone_number = $pro['phone_number'];
                $email = $pro['email'] ?? null;
                $name = $pro['name'] ?? null;
                $note = $pro['note'] ?? null;
                $staff_id = $pro['staff_id'] ?? null;
                $date_of_birth = $pro['date_of_birth'] ?? null;
                $address = $pro['address'] ?? null;
                $sex = $pro['sex'] ?? null;
                $status = $pro['status'] ?? null;
                $avatar_image = $pro['avatar_image'] ?? null;

                $consultation_1 = $pro['consultation_1'] ?? null;
                $consultation_2 = $pro['consultation_2'] ?? null;
                $consultation_3 = $pro['consultation_3'] ?? null;


                try {
                    $CustomerSaleExists = CustomerSale::where(
                        'phone_number',
                        $phone_number
                    )->first();


                    $is_change = false;
                    if ($CustomerSaleExists != null) {


                        $total_skip_same_phone_number++;
                        continue;
                    }


                    $CustomerSaleCreate = null;
                    if ($CustomerSaleExists != null) {

                        $CustomerSaleCreate = $CustomerSaleExists->update(
                            Helper::sahaRemoveItemArrayIfNullValue([
                                'phone_number' => $phone_number,
                                'email' => $email,
                                'name' => $name,
                                'note' => $note,
                                'staff_id' => $staff_id,
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


                        $CustomerSaleCreate =   $CustomerSaleExists = CustomerSale::where(
                            'id',
                            $CustomerSaleExists->id
                        )->first();
                    } else {
                        $CustomerSaleCreate = CustomerSale::create(
                            [
                                'phone_number' => $phone_number,
                                'email' => $email,
                                'name' => $name,
                                'note' => $note,
                                'staff_id' => $staff_id,
                                'date_of_birth' => $date_of_birth,
                                'address' => $address,
                                'sex' => $sex,
                                'status' => $status,

                                'store_id' => $request->store->id,
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
                "allow_skip_same_phone_number" => $allow_skip_same_phone_number,
                "total_customer_sale_request" => $total_customer_sale_request,
                "total_skip_same_phone_number"  => $total_skip_same_phone_number,
                "total_changed_same_phone_number" =>  $total_changed_same_phone_number,
                "total_failed" => $total_failed,
                "total_new_add" =>  $total_new_add,
            ]
        ], 201);
    }

    /**
     * Cập nhật thông tin khách hàng tư vấn
     * @bodyParam staff_id int id nhân viên sale hỗ trợ
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

        $customersale_id = request("customersale_id");
        $CustomerSaleExists = CustomerSale::where(
            'id',
            $customersale_id
        )->where('store_id', $request->store->id)
            ->first();


        if ($CustomerSaleExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ], 400);
        }

        if (CustomerSale::where('phone_number', $request->phone_number)
            ->where('store_id', $request->store->id)
            ->where('id', '<>', $customersale_id)->exists()
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 400);
        }


        if ($CustomerSaleExists->consultation_1 != $request->consultation_1) {
            $CustomerSaleExists->update(
                [
                    'time_update_consultation_1' => Helper::getTimeNowString(),
                ]
            );
        }
        if ($CustomerSaleExists->consultation_2 != $request->consultation_2) {
            $CustomerSaleExists->update(
                [
                    'time_update_consultation_2' => Helper::getTimeNowString(),
                ]
            );
        }
        if ($CustomerSaleExists->consultation_3 != $request->consultation_3) {
            $CustomerSaleExists->update(
                [
                    'time_update_consultation_3' => Helper::getTimeNowString(),
                ]
            );
        }

        $CustomerSaleExists->update(
            [
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'name' => $request->name,
                'note' => $request->note,
                'staff_id' => $request->staff_id,
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
            'data' => CustomerSale::where('id', '=',   $CustomerSaleExists->id)->first(),
        ], 200);
    }

    /**
     * Cập nhật nhiều khách hàng thông tin user cần tư vấn
     * @bodyParam staff_id int id nhân viên sale hỗ trợ
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

                $CustomerSaleExists = CustomerSale::where(
                    'id',
                    $idUser
                )
                    ->first();


                if ($CustomerSaleExists != null) {


                    $CustomerSaleExists->update(Helper::sahaRemoveItemArrayIfNullValue(
                        [
                            'phone_number' => $item['phone_number'] ?? null,
                            'email' => $item['email'] ?? null,
                            'name' => $item['name'] ?? null,
                            'note' => $item['note'] ?? null,
                            'staff_id' => $item['staff_id'] ?? null,
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

        $customersale_id = request("customersale_id");
        $CustomerSaleExists = CustomerSale::where(
            'id',
            $customersale_id
        )->where('store_id', $request->store->id)
            ->first();

        if ($CustomerSaleExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 404);
        }

        $idDeleted = $CustomerSaleExists->id;
        $CustomerSaleExists->delete();



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }

    /**
     * Xem 1 kahch hang
     */
    public function getOne(Request $request)
    {

        $customersale_id = request("customersale_id");
        $CustomerSaleExists = CustomerSale::where(
            'id',
            $customersale_id
        )->where('store_id', $request->store->id)
            ->first();

        if ($CustomerSaleExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 404);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>     $CustomerSaleExists,
        ], 200);
    }


    /**
     * Chuyển khách tới khách hàng
     * 
     * @bodyParam list_ids list id 
     * 
     */
    public function sendToCustomer(Request $request)
    {


        $total_ids = 0;
        $total_success = 0;
        $total_has = 0;



        $arr_onsales = [];

        foreach ($request->list_ids as $item) {

            $idUser = $item ?? null;

            if ($idUser != null) {

                $CustomerSaleExists = CustomerSale::where(
                    'id',
                    $idUser
                )
                    ->first();

                if ($CustomerSaleExists != null) {
                    array_push($arr_onsales, $CustomerSaleExists);

                    $phone = PhoneUtils::convert($CustomerSaleExists->phone_number);

                    if (
                        $phone == null &&
                        PhoneUtils::check_valid($phone) == false
                    ) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                            'msg' => 'Một số số điện thoại không đúng',
                        ], 400);
                    }
                }
            }
        }


        foreach ($arr_onsales as $item) {
            $total_ids++;
            $idUser = $item->id ?? null;

       

            if ($idUser != null) {

                $phone_number = $item->phone_number ?? null;
                $name =  $item->name ?? null;
                $date_of_birth = $item->date_of_birth ?? null;

                $has = Customer::where('store_id', $request->store->id)->where('phone_number', $phone_number)->first()  != null;

                if ($phone_number  != null &&  $name != null  && $has  == false) {
                    Customer::create([
                        'phone_number' =>   $phone_number,
                        'date_of_birth' =>   $date_of_birth,
                        'name' =>   $name,
                        'name_str_filter' => StringUtils::convert_name_lowcase($name),
                        'store_id' => $request->store->id,
                    ]);
                    $total_success++;
                } else {
                    $total_has++;
                }
            }
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "total_has" => $total_has,
                "total_success" => $total_success,
                "total_ids" => $total_ids,
            ]
        ], 200);
    }
}
