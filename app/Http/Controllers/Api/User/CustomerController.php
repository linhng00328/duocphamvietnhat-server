<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CustomerUtils;
use App\Helper\GroupCustomerUtils;
use App\Helper\Helper;
use App\Helper\PhoneUtils;
use App\Helper\Place;
use App\Helper\PointCustomerUtils;
use App\Helper\SendToWebHookUtils;
use App\Helper\StatusDefineCode;
use App\Helper\StringUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Models\Agency;
use App\Models\AgencyRegisterRequest;
use App\Models\AgencyType;
use App\Models\Collaborator;
use App\Models\CollaboratorRegisterRequest;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\GroupCustomer;
use App\Models\MsgCode;
use App\Models\PointHistory;
use App\Models\Staff;
use App\Services\Shipper\GHN\GHNUtils;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Foreach_;
use Prophecy\Util\StringUtil;

/**
 * @group  User/Quản lý khách hàng
 */
class CustomerController extends Controller
{
    /**
     * 
     * Danh sách tất cả khách hàng
     * 
     * @urlParam  store_code required Store code. Example: kds
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên,số điện thoại cần tìm VD: covid 19
     * @queryParam  sort_by Sắp xếp theo VD: time
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  field_by Chọn trường nào để lấy
     * @queryParam  field_by_value Giá trị trường đó
     * @queryParam  day_of_birth Ngay sinh
     * @queryParam  month_of_birth Thang sinh
     * @queryParam  year_of_birth Nam sinh
     * @queryParam  referral_phone_number Số điện thoại giới thiệu
     * @queryParam  json_list_filter  Chuỗi tìm
     * 
     * @bodyParam type_compare Kiểu so sánh //0 Tổng mua (Chỉ đơn hoàn thành), 1 tổng bán, 2 Xu hiện tại, 3 Số lần mua hàng 4, tháng sinh nhật 5, tuổi 6, giới tính, 7 tỉnh, 8 ngày đăng ký, 9 CTV, 10 đại lý, 11 nhóm kh
     * @bodyParam comparison_expression Biểu thức so sánh  (>,>=,=,<,<=)
     * @bodyParam value_compare Giá trị so sánh so sánh
     * @queryParam is_export có phải xuất exel không (true sẽ đầy đủ thông tin hơn)
     * @bodyParam sale_staff_id Staff_id nhân viên sale quản lý
     * @bodyParam customer_ids Staff_id Danh sách id của khách hàng
     * @bodyParam sale_type Vai trò của khách hàng 0 khách hàng, 1 cộng tác viên, 2 đại lý
     * 
     */
    public function getAll(Request $request)
    {

        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $customer_ids = request("customer_ids") == null ? [] : explode(',', request("customer_ids"));
        $search = StringUtils::convert_name_lowcase(request('search'));
        $descending = filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $field_by = request('field_by');
        $field_by_value = request('field_by_value');
        $referral_phone_number = request('referral_phone_number');
        $is_export = filter_var(request('is_export'), FILTER_VALIDATE_BOOLEAN);

        $day_of_birth  = request('day_of_birth');
        $month_of_birth  = request('month_of_birth');
        $year_of_birth = request('year_of_birth');

        $sale_staff_id = request('sale_staff_id');
        $sale_type = request('sale_type');
        //   ('type_compare', 'comparison_expression', 'value_compare')
        $json_list_filter = $request->group_customer_id ? json_decode($request->condition_items) : (json_decode(request('json_list_filter')) ?? []);
        $has_has_filter = false;
        $arr_id_cus_from_filter = [];

        if ($request->group_customer_type == GroupCustomer::GROUP_TYPE_LIST_CUSTOMER) {
            $has_has_filter = true;
            $arr_id_cus_from_filter = $request->customer_group_ids;
        } else {
            if ($json_list_filter != null && is_array($json_list_filter)) {
                $has_has_filter = true;
                $first = true;
                $arr =  [];
                $arrAgencyCTVNomal = [];
                $is_filter_different_agency_ctv_normal = false;
                $is_filter_agency_ctv_normal = false;

                foreach ($json_list_filter as $filter) {
                    $arrNEW = [];

                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_COMPLETED) {

                        $arrNEW =   DB::table('customers')->leftJoin('orders', "customers.id", '=', 'orders.customer_id')
                            ->selectRaw("
                            SUM(CASE WHEN orders.order_status = '10' THEN orders.total_final ELSE (CASE WHEN orders.order_status = '8' THEN -orders.total_final ELSE 0 END) END) AS total_final2,
                            customers.id
                            ")
                            ->groupBy('customers.id')
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->havingRaw("total_final2 $filter->comparison_expression $filter->value_compare")
                            ->pluck('customers.id')->toArray();
                    }

                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_WITH_REFUND) {
                        $arrNEW =   DB::table('customers')->leftJoin('orders', "customers.id", '=', 'orders.customer_id')
                            ->where('orders.order_status', '!=', '8')
                            ->selectRaw("SUM(orders.total_final) AS total_final2,customers.id")
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->groupBy('customers.id')
                            //   ->havingRaw("total_final2 $filter->comparison_expression $filter->value_compare")
                            ->pluck('customers.id')->toArray();
                    }

                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_POINT) {

                        $arrNEW =   DB::table('customers')
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->where('points', $filter->comparison_expression, (int)$filter->value_compare)
                            ->pluck('customers.id')->toArray();
                    }

                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_COUNT_ORDER) {
                        $arrNEW =   DB::table('customers')->leftJoin('orders', "customers.id", '=', 'orders.customer_id')
                            ->havingRaw("COUNT(*) $filter->comparison_expression $filter->value_compare")
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->groupBy('customers.id')
                            ->pluck('customers.id')->toArray();
                    }


                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_MONTH_BIRTH) {
                        $arrNEW =   DB::table('customers')
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->whereMonth('date_of_birth', $filter->comparison_expression, $filter->value_compare)
                            ->pluck('customers.id')->toArray();
                    }

                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_SEX) {
                        $arrNEW =   DB::table('customers')
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->where('sex', $filter->comparison_expression, $filter->value_compare)
                            ->pluck('customers.id')->toArray();
                    }

                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_PROVINCE) {
                        $arrNEW =   DB::table('customers')
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->where('province', $filter->comparison_expression, $filter->value_compare)
                            ->pluck('customers.id')->toArray();
                    }

                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_DATE_REG) {
                        $arrNEW =   DB::table('customers')
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->whereDate('created_at', $filter->comparison_expression, $filter->value_compare)
                            ->pluck('customers.id')->toArray();
                    }


                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_AGE) {


                        $arrNEW =   DB::table('customers')
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->where('date_of_birth', '!=', null)
                            ->whereRaw("TIMESTAMPDIFF(YEAR, DATE(date_of_birth), current_date) $filter->comparison_expression $filter->value_compare")
                            ->pluck('customers.id')->toArray();
                    }

                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_AGENCY) {
                        if ($filter->value_compare == 0) {
                            $arrNEW =   DB::table('customers')
                                ->where(
                                    'customers.store_id',
                                    $request->store->id
                                )
                                ->join('agencies', "customers.id", '=', 'agencies.customer_id')
                                ->where('customers.is_agency', 1)
                                ->pluck('customers.id')->toArray();
                        } else {
                            $arrNEW =   DB::table('customers')
                                ->where(
                                    'customers.store_id',
                                    $request->store->id
                                )
                                ->join('agencies', "customers.id", '=', 'agencies.customer_id')
                                ->where('customers.is_agency', 1)
                                ->where('customers.is_collaborator', false)
                                ->where('agencies.agency_type_id', $filter->value_compare)
                                ->pluck('customers.id')->toArray();
                        }

                        $arrAgencyCTVNomal = array_merge($arrAgencyCTVNomal,  $arrNEW);
                    }

                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_CTV) {
                        $arrNEW =   DB::table('customers')
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->where('customers.is_collaborator', 1)
                            ->pluck('customers.id')->toArray();
                        $arrAgencyCTVNomal = array_merge($arrAgencyCTVNomal,  $arrNEW);
                    }

                    if ($filter->type_compare == GroupCustomerUtils::TYPE_COMPARE_CUSTOMER_NORMAL) {
                        $arrNEW =   DB::table('customers')
                            ->where(
                                'customers.store_id',
                                $request->store->id
                            )
                            ->where('customers.is_collaborator', 0)
                            ->where('customers.is_agency', 0)
                            ->pluck('customers.id')->toArray();
                        $arrAgencyCTVNomal = array_merge($arrAgencyCTVNomal,  $arrNEW);
                    }

                    if (
                        $filter->type_compare != GroupCustomerUtils::TYPE_COMPARE_CTV &&
                        $filter->type_compare != GroupCustomerUtils::TYPE_COMPARE_CUSTOMER_NORMAL &&
                        $filter->type_compare != GroupCustomerUtils::TYPE_COMPARE_AGENCY
                    ) {
                        $is_filter_different_agency_ctv_normal = true;

                        if (count($arrNEW) > 0) {

                            if ($first  == true) {

                                $arr =  $arrNEW;
                                $first = false;
                            } else {

                                $arr = array_values(
                                    array_intersect(
                                        $arrNEW,
                                        $arr,
                                    )
                                );
                            }
                        }
                    } else {
                        $is_filter_agency_ctv_normal = true;
                    }
                }

                if (count($arr) > 0) {

                    if ($is_filter_agency_ctv_normal === true) {

                        $arr = array_values(
                            array_intersect(
                                $arrAgencyCTVNomal,
                                $arr,
                            )
                        );
                    }
                } else {

                    if ($is_filter_different_agency_ctv_normal === false) {

                        $arr = $arrAgencyCTVNomal;
                    }
                }

                $arr_id_cus_from_filter  =  array_unique($arr);
            }
        }


        if ($request->count_customer) {
            return count(array_unique($arr_id_cus_from_filter));
        }

        // const TYPE_COMPARE_TOTAL_FINAL_COMPLETED = 0; // Tổng mua (Chỉ đơn hoàn thành trừ trả hàng), 
        // const TYPE_COMPARE_TOTAL_FINAL_WITH_REFUND = 1; // Tổng mua (Tất cả trạng thái đơn trừ trả hàng), 
        // const TYPE_COMPARE_POINT = 2; // Xu hiện tại, 
        // const TYPE_COMPARE_COUNT_ORDER = 3; // Số lần mua hàng 
        // const TYPE_COMPARE_MONTH_BIRTH = 4; // tháng sinh nhật 
        // const TYPE_COMPARE_AGE = 5; // tuổi 
        // const TYPE_COMPARE_SEX = 6; // giới tính, 
        // const TYPE_COMPARE_PROVINCE = 7; // tỉnh, 
        // const TYPE_COMPARE_DATE_REG = 8; // ngày đăng ký
        // const TYPE_COMPARE_CTV = 9; // cộng tác viên
        // const TYPE_COMPARE_AGENCY = 10; // Đại lý

        $customers = Customer::sortByRelevance(true)

            ->where(
                'store_id',
                $request->store->id
            )
            ->when(count($customer_ids) > 0, function ($query) use ($customer_ids) {
                $query->whereIn('customers.id', $customer_ids);
            })
            // ->where('official', true)
            ->when($day_of_birth != null, function ($query) use ($day_of_birth) {
                $query->whereDay('date_of_birth', '=', $day_of_birth);
            })
            ->when($month_of_birth != null, function ($query) use ($month_of_birth) {
                $query->whereMonth('date_of_birth', '=', $month_of_birth);
            })
            ->when($year_of_birth != null, function ($query) use ($year_of_birth) {
                $query->whereYear('date_of_birth', '=', $year_of_birth);
            })
            ->when(Customer::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn, $descending) {
                $query->orderBy($sortColumn, $descending);
            })
            ->when(request('sort_by') == null && empty($search), function ($query) {
                $query->orderBy('id', 'desc');
            })
            ->when($referral_phone_number != null, function ($query) use ($referral_phone_number) {
                $query->where('referral_phone_number', $referral_phone_number);
            })

            ->when(!empty($field_by), function ($query) use ($field_by, $field_by_value) {
                $query->where($field_by, $field_by_value);
            })
            ->when($has_has_filter  == true, function ($query) use ($arr_id_cus_from_filter) {
                $query->whereIn('id', $arr_id_cus_from_filter);
            })
            ->when($sale_staff_id  != null, function ($query) use ($sale_staff_id) {
                $query->where('sale_staff_id', $sale_staff_id);
            })
            ->when($request->is_sale_staff  != null, function ($query) use ($request) {
                $query->where('sale_staff_id', $request->staff->id);
            })
            ->when($sale_type  !== null && $sale_type  !== '', function ($query) use ($sale_type) {
                if ($sale_type == 1) {
                    $query->where('is_collaborator', true)
                        ->where('is_agency', false);
                }

                if ($sale_type == 2) {
                    $query->where('is_agency', true)
                        ->where('is_collaborator', false);
                }

                if ($sale_type == 0) {
                    $query->where('is_agency', false)
                        ->where('is_collaborator', false);
                }
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name_str_filter', 'like', '%' . $search . '%')
                        ->orWhere('phone_number', 'like', '%' . $search . '%');
                })
                    //->orderBy('name_str_filter', 'ASC')
                    ->orderByraw('CHAR_LENGTH(name_str_filter) ASC');
            })
            ->when(request('date_from') != null && request('date_to') != null && $sale_staff_id  != null, function ($query) {
                $query->whereDate('time_sale_staff', '>=', request('date_from'))
                    ->whereDate('time_sale_staff', '<=', request('date_to'));
            })
            ->paginate($is_export == false ? 20 : 10000);


        $debt = Customer::where('store_id', $request->store->id)->orderBy('created_at', 'ASC')
            ->where('debt', '>', 0)->sum('debt');

        $custom = collect(
            [
                'debt' => $debt
            ]
        );

        foreach ($customers as $p) {

            $agency = Agency::where('customer_id', $p->id)->first();
            if ($agency != null) {
                $p->agency_type = AgencyType::where('id', $agency->agency_type_id)->first();
            }

            $sale = Staff::where('id', $p->sale_staff_id)->select('name', 'id', 'phone_number')->first();
            $p->sale_staff = $sale;

            if (request('date_from') != null && request('date_to') != null) {
                $total_after_discount_no_use_bonus_with_date =   DB::table('orders')->where('store_id', $request->store->id)
                    ->where('customer_id', $p->id)
                    ->when($sale_staff_id  != null, function ($query) use ($sale_staff_id) {
                        $query->where("orders.sale_by_staff_id", $sale_staff_id);
                    })
                    ->where('orders.order_status', StatusDefineCode::COMPLETED)
                    ->where('orders.payment_status', StatusDefineCode::PAID)
                    ->where('orders.created_at', '>=', $dateFrom)
                    ->where('orders.created_at', '<=', $dateTo)
                    ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));

                $p->total_after_discount_no_use_bonus_with_date = $total_after_discount_no_use_bonus_with_date;
            }
        }

        $data = $custom->merge($customers);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $data,
        ], 200);
    }

    /**
     * 
     * Thông tin 1 khách hàng
     * 
     * @urlParam  store_code required Store code. Example: kds
     * @queryParam  customer_id int required  Customer id
     */
    public function getOne(Request $request, $id)
    {

        $customer_id = $request->route()->parameter('customer_id');
        $customerExists = Customer::where('id', $customer_id)
            ->where('store_id', $request->store->id)
            ->first();

        if (empty($customerExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 404);
        }

        $customerExists->default_address = CustomerAddress::where('customer_id',   $customerExists->id)
            ->where('is_default', true)
            ->first();

        $sale = Staff::where('id', $customerExists->sale_staff_id)->select('name', 'id', 'phone_number')->first();
        $customerExists->sale_staff = $sale;

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $customerExists,
        ], 200);
    }

    /**
     * Lịch sử tích xu điểm
     */
    public function historyPoints(Request $request, $id)
    {

        $customer_id = $request->route()->parameter('customer_id');
        $customerExists = Customer::where('id', $customer_id)
            ->where('store_id', $request->store->id)
            ->first();

        if (empty($customerExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 404);
        }


        $Point_history = PointHistory::where(
            'store_id',
            $request->store->id
        )
            ->where(
                'customer_id',
                $customer_id
            )->orderBy('id', 'DESC')
            ->paginate(20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $Point_history,
        ], 200);
    }

    /**
     * Cộng trừ xu cho KH
     * 
     * @bodyParam is_sub bool cộng hay trừ
     * @bodyParam point số xu ()
     * @bodyParam reason lý do
     * 
     * 
     */
    public function addSubPoint(Request $request)
    {
        $is_sub = filter_var($request->is_sub, FILTER_VALIDATE_BOOLEAN);

        if ($request->point <= 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => 'Số xu không hợp lệ',
            ], 400);
        }

        $id = $request->route()->parameter('customer_id');

        $customerExists = Customer::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        if ($customerExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 400);
        }

        PointCustomerUtils::add_sub_point(
            $is_sub == true ?  PointCustomerUtils::SUB_POINT : PointCustomerUtils::ADD_POINT,
            $request->store->id,
            $id,
            $is_sub == true ?  (- ((int)$request->point)) : ((int)$request->point),
            $id,
            Helper::getRandomOrderString(),
            $request->reason
        );

        $customerExists = Customer::where(
            'store_id',
            $request->store->id
        )->where('id', $id)
            ->first();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $customerExists,
        ], 200);
    }

    /**
     * 
     * Tạo thêm 1 khách hàng
     * 
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam  name string required  tên khách hàng
     * @bodyParam  phone_number string required  sdt khách hàng
     * @bodyParam  email string required  email
     * @bodyParam  address_detail string required  địa chỉ
     * @bodyParam  province int required  id tỉnh
     * @bodyParam  district string required  id quận
     * @bodyParam  wards string required  id xã
     * @bodyParam date_of_birth Date   Ngày sinh
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nam - 2 Nữ
     * @bodyParam is_update boolean truong hop cap nhat chu ko them moi
     * 
     */
    public function create(Request $request)
    {
        $now = Helper::getTimeNowDateTime();
        $phone = PhoneUtils::convert($request->phone_number);
        $validPhone = PhoneUtils::check_valid($phone);

        if ($validPhone == false) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ], 400);
        }

        $customerExists = Customer::where('store_id', $request->store->id)
            ->where('phone_number', $phone)->first();

        if ($request->is_sale_staff != true && filter_var($request->is_update, FILTER_VALIDATE_BOOLEAN) == false) {

            if ($customerExists != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                    'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
                ], 400);
            }
        }

        if ($request->is_sale_staff == true &&  $customerExists != null &&  $customerExists->sale_staff_id != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => 'Khách hàng này đã có nhân viên Sale quản lý',
            ], 400);
        }


        if (!empty($request->email)) {
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_EMAIL[0],
                    'msg' => MsgCode::INVALID_EMAIL[1],
                ], 400);
            }
        }

        if ($request->name == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $cCreate = Customer::where('store_id', $request->store->id)->where('phone_number', $phone)->first();

        if ($cCreate != null) {

            $data = [
                'name' => $request->name,
                'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
                'email' => $request->email,
                "sex" => $request->sex,
                "date_of_birth" => $request->date_of_birth,
                'address_detail' => $request->address_detail,
                "province" => $request->province,
                "district" => $request->district,
                "wards" => $request->wards,


                "province_name" => Place::getNameProvince($request->province),
                "district_name" => Place::getNameDistrict($request->district),
                "wards_name" => Place::getNameWards($request->wards),
            ];
            if ($request->is_sale_staff == true && $request->staff != null) {
                $data['sale_staff_id'] = $request->staff->id;
                $data['time_sale_staff'] = $now;
            }

            $cCreate->update($data);
        } else {

            $data = [
                'area_code' => '+84',
                'name' => $request->name,
                'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
                'phone_number' =>   $phone,
                'email' => $request->email,
                'store_id' => $request->store->id,
                'password' => bcrypt('DOAPP_BCRYPT_PASS'),
                'official' => false,
                "sex" => $request->sex,
                "date_of_birth" => $request->date_of_birth,
                'address_detail' => $request->address_detail,
                "province" => $request->province,
                "district" => $request->district,
                "wards" => $request->wards,
                'sale_staff_id' => $request->is_sale_staff == true && $request->staff != null ? $request->staff->id : null,
                'time_sale_staff' => $request->is_sale_staff == true && $request->staff != null ? $now : null,
                "province_name" => Place::getNameProvince($request->province),
                "district_name" => Place::getNameDistrict($request->district),
                "wards_name" => Place::getNameWards($request->wards),
            ];

            if ($request->is_sale_staff == true && $request->staff != null) {
                $data['sale_staff_id'] = $request->staff->id;
            }

            $cCreate = Customer::create($data);
        }

        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::NEW_CUSTOMER,   $cCreate);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Customer::where('id', $cCreate->id)->first()
        ], 200);
    }


    /**
     * 
     * Nhiều khách hàng
     * 
     * @bodyParam  is_update_password bool có cập nhật lại pass của khách hàng đã tồn tại không
     * @bodyParam  password string required  Mật khẩu đăng nhập cho khách mặc định (123456)
     * 
     * gửi lên (list) object gồm các thuộc tính sau
     * 
     * @urlParam  store_code required Store code. Example: kds
     * 
     * 
     * @bodyParam  name string required  tên khách hàng
     * @bodyParam  phone_number string required  sdt khách hàng
     * @bodyParam  email string required  email
     * @bodyParam  address_detail string required  địa chỉ
     * @bodyParam  province int required  id tỉnh
     * @bodyParam  district string required  id quận
     * @bodyParam  wards string required  id xã
     * @bodyParam date_of_birth Date   Ngày sinh
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nam - 2 Nữ
     * @bodyParam is_update boolean truong hop cap nhat chu ko them moi
     * @bodyParam sale_type string required 1 cộng tác viên 2 đại lý 0 không gì cả
     * @bodyParam agency_type_name Tên tầng đại lý
     */
    public function createManyCustomer(Request $request)
    {
        $is_update_password =  filter_var($request->is_update_password, FILTER_VALIDATE_BOOLEAN);

        if ($is_update_password === null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Thiếu trường xác nhận cập nhật pass của khách hàng đã tồn tại",
            ], 400);
        }
        $list = $request->list != null && is_array($request->list) ?  $request->list : [];

        foreach ($list as $item) {
            $name   =  $item['name'] ?? null;
            $phone_number   =  $item['phone_number'] ?? null;
            $referral_phone_number   =  $item['referral_phone_number'] ?? null;
            $name_str_filter  = StringUtils::convert_name_lowcase($item['name']);
            $email = $item['email'] ?? "";
            $sex = ($item["sex"] ?? null) == "Nam" ? 1 : (($item["sex"] ?? null) == "Nữ" ? 2 : 0);
            $date_of_birth = $item["date_of_birth"]  ?? "";
            $address_detail = $item['address_detail'] ?? "";

            $provinceId = Place::getIDProvinceFromName($item["province_name"] ?? "");
            $districtId = Place::getIDDistrictFromName($provinceId, $item["district_name"] ?? "");
            $wardsId = Place::getIDWardsFromName($provinceId, $districtId, $item["wards_name"] ?? "");

            $sale_type = $item['sale_type'] ?? "";
            $agency_type_name = $item['agency_type_name'] ?? "";


            $province_name =  Place::getNameProvince($provinceId);
            $district_name =  Place::getNameDistrict($districtId);
            $wards_name =  Place::getNameWards($wardsId);

            $phone = PhoneUtils::convert($phone_number);
            $validPhone = PhoneUtils::check_valid($phone);

            $referral_phone = PhoneUtils::convert($referral_phone_number);
            $validReferralPhone = PhoneUtils::check_valid($referral_phone);

            if (($validPhone == true)) {

                $data = [
                    'store_id' => $request->store->id,
                    'phone_number' => $phone,
                    'name' => $name,
                    'name_str_filter' => $name_str_filter,
                    'email' => $email,
                    "sex" => $sex,
                    'password' => bcrypt($request->password ?? "123456"),
                    'official' => true,
                    "date_of_birth" => $date_of_birth,
                    'address_detail' => $address_detail,
                    "province" => $provinceId,
                    "district" => $districtId,
                    "wards" => $wardsId,
                    "province_name" => $province_name,
                    "district_name" => $district_name,
                    "wards_name" => $wards_name,
                ];

                if ($validReferralPhone == true) {
                    $data['referral_phone_number'] = $referral_phone;
                }

                $customerExists = Customer::where('store_id', $request->store->id)
                    ->where('phone_number', $phone)->first();
                if ($customerExists  != null) {
                    if ($is_update_password  == false) {
                        unset($data['password']);
                    }

                    $customerExists->update($data);
                } else {
                    try {
                        $customerExists = Customer::create($data);
                    } catch (Exception $e) {
                    }
                }

                //Xử lý tầng đối tác
                if ($sale_type == 1) {
                    //disable agency

                    $agencyExists = Agency::where(
                        'store_id',
                        $request->store->id
                    )->where('customer_id',  $customerExists->id)
                        ->first();

                    if ($agencyExists != null) {
                        $agencyExists->update([
                            "status" => 0,
                        ]);
                    }

                    //allow collaborator
                    $collaborator = Collaborator::where('store_id', $request->store->id)
                        ->where('customer_id', $customerExists->id)->first();

                    if ($collaborator  == null) {
                        Collaborator::create(
                            [
                                'store_id' => $request->store->id,
                                'customer_id' =>  $customerExists->id,
                                "status" => 1,
                            ]
                        );
                    } else {
                        $collaborator->update([
                            'status' => 1
                        ]);
                    }

                    $customerExists->update([
                        'is_agency' => false,
                        'is_collaborator' => true,
                    ]);
                } else
        
                if ($sale_type == 2) {
                    $allTypeAgency = AgencyType::where('store_id', $request->store->id)->get();
                    if (count($allTypeAgency) > 0) {
                        $gencyTypeByIdExists = AgencyType::where('name', $agency_type_name)
                            ->where('store_id', $request->store->id)
                            ->first();

                        $agency_type_id =  null;
                        if ($gencyTypeByIdExists  != null) {
                            $agency_type_id =  $gencyTypeByIdExists->id;
                        }
                    }




                    $collaboratorExists = Collaborator::where(
                        'store_id',
                        $request->store->id
                    )->where('customer_id',  $customerExists->id)
                        ->first();
                    if ($collaboratorExists != null) {
                        $collaboratorExists->update([
                            "status" => 0,
                        ]);
                    }
                    //allow agency
                    $agency = Agency::where('store_id', $request->store->id)
                        ->where('customer_id', $customerExists->id)->first();

                    if ($agency  == null) {
                        Agency::create(
                            [
                                'agency_type_id' =>   $agency_type_id,
                                'store_id' => $request->store->id,
                                'customer_id' =>   $customerExists->id,
                                "status" => 1,
                            ]
                        );
                    } else {
                        $agency->update([
                            'status' => 1,
                            'agency_type_id' =>  $agency_type_id,
                        ]);
                    }


                    $customerExists->update([
                        'is_agency' =>  true,
                        'is_collaborator' => false,
                    ]);
                } else {
                    $agencyExists = Agency::where('store_id', $request->store->id)->where('customer_id',  $customerExists->id)->first();
                    if ($agencyExists != null) {
                        $agencyExists->update(["status" => 0,]);
                    }

                    $collaboratorExists = Collaborator::where('store_id', $request->store->id)->where('customer_id',  $customerExists->id)->first();
                    if ($collaboratorExists != null) {
                        $collaboratorExists->update(["status" => 0,]);
                    }

                    $customerExists->update([
                        'is_agency' =>  false,
                        'is_collaborator' => false,
                    ]);
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
     * Cập nhật 1 khách hàng
     *
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam  name string required  tên khách hàng
     * @bodyParam  phone_number string required  sdt khách hàng
     * @bodyParam  email string required  email
     * @bodyParam  address_detail string required  địa chỉ
     * @bodyParam  province int required  id tỉnh
     * @bodyParam  district string required  id quận
     * @bodyParam  wards string required  id xã
     * @bodyParam date_of_birth Date   Ngày sinh
     * @bodyParam sex int   Giới tính 0 Ko xác định - 1 Nam - 2 Nữ
     * 
     * 
     * 
     */
    public function update(Request $request)
    {
        $customer_id = $request->route()->parameter('customer_id');

        $customerExistsForUpdate = Customer::where('store_id', $request->store->id)
            ->where('id',  $customer_id)->first();


        if ($customerExistsForUpdate == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 400);
        }


        if ($request->is_sale_staff == true &&  $customerExistsForUpdate != null &&  $customerExistsForUpdate->sale_staff_id != $request->staff->id) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => 'Bạn không có quyền sửa khách hàng này',
            ], 400);
        }


        $phone = PhoneUtils::convert($request->phone_number);
        $validPhone = PhoneUtils::check_valid($phone);
        $referral_phone = PhoneUtils::convert($request->referral_phone_number);
        $validReferralPhone = PhoneUtils::check_valid($referral_phone);

        if ($validPhone == false) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ], 400);
        }

        if ($request->referral_phone_number && $validReferralPhone == false) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_REFERRAL_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_REFERRAL_PHONE_NUMBER[1],
            ], 400);
        }

        if ($referral_phone == $phone) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => 'Không thể  nhập SĐT giới thiệu của trùng với SĐT',
            ], 400);
        }

        if ($request->name == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $customerExists = Customer::where('store_id', $request->store->id)
            ->where('id', '!=',  $customer_id)
            ->where('phone_number', $phone)->first();

        if ($customerExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 400);
        }

        $customerPassersby = CustomerUtils::getCustomerPassersby($request);
        if ($customerPassersby->id ==  $customer_id) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VISITORS_CANNOT_EDIT_OR_DELETE[0],
                'msg' => MsgCode::VISITORS_CANNOT_EDIT_OR_DELETE[1],
            ], 400);
        }

        // dd(bcrypt($request->password), $customerExistsForUpdate);
        $customerExistsForUpdate->update(
            [
                'area_code' => '+84',
                'name' => $request->name,
                'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
                'phone_number' => $phone,
                'referral_phone_number' => $referral_phone,
                'email' => $request->email,
                'store_id' => $request->store->id,
                'password' => $request->password != null ? bcrypt($request->password) : $customerExistsForUpdate->password,

                "sex" => $request->sex,
                "date_of_birth" => $request->date_of_birth,

                'address_detail' => $request->address_detail,
                "province" => $request->province,
                "district" => $request->district,
                "wards" => $request->wards,

                "province_name" => Place::getNameProvince($request->province),
                "district_name" => Place::getNameDistrict($request->district),
                "wards_name" => Place::getNameWards($request->wards),
            ]
        );

        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_CUSTOMER,   $customerExistsForUpdate);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Customer::where('id', $customerExistsForUpdate->id)->first()
        ], 200);
    }

    /**
     * 
     * Xóa 1 khách hàng
     * 
     * @urlParam  store_code required Store code. Example: kds
     * @urlParam  customer_id string required  id khách hàng cần xóa
     * 
     */
    public function delete(Request $request)
    {
        $customer_id = $request->route()->parameter('customer_id');


        $customerPassersby = CustomerUtils::getCustomerPassersby($request);
        if ($customerPassersby->id ==  $customer_id) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VISITORS_CANNOT_EDIT_OR_DELETE[0],
                'msg' => MsgCode::VISITORS_CANNOT_EDIT_OR_DELETE[1],
            ], 400);
        }


        $customerExistsForUpdate = Customer::where('store_id', $request->store->id)
            ->where('id',  $customer_id)->first();

        if ($customerExistsForUpdate == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 400);
        }

        if ($request->is_sale_staff == true &&  $customerExistsForUpdate != null &&  $customerExistsForUpdate->sale_staff_id != $request->staff->id) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => 'Bạn không có quyền xóa khách hàng này',
            ], 400);
        }

        $idDeleted = $customerExistsForUpdate->id;
        $phone_number = $customerExistsForUpdate->phone_number;

        if ($request->is_sale_staff == true &&  $customerExistsForUpdate != null &&  $customerExistsForUpdate->sale_staff_id != $request->staff->id) {
            $customerExistsForUpdate->update([
                'sale_staff_id' => null,
                'time_sale_staff' => null,
            ]);
        } else {
            $customerExistsForUpdate->delete();
        }


        SendToWebHookUtils::sendToWebHook(
            $request,
            SendToWebHookUtils::DELETE_CUSTOMER,
            [
                'phone_number' =>  $phone_number
            ]
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }

    /**
     * Xem địa chỉ của khách
     * 
     * 
     * @bodyParam phone_number string sdt khách
     * 
     */
    public function getAddressCustomer(Request $request, $id)
    {

        $phone_number = $request->phone_number;
        $c = Customer::where('phone_number', $phone_number)->where('store_id', $request->store->id)->first();

        $id = $c != null ? $c->id : null;

        $address = CustomerAddress::where('store_id', $request->store->id)
            ->where('customer_id',   $id)
            ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $address,
        ], 200);
    }


    /**
     * Phân loại đối tác bán hàng
     *
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam  sale_type string required 1 cộng tác viên 2 đại lý 0 không gì cả
     * @urlParam  customer_id int customer_id
     * @urlParam agency_type_id (nếu sale type == 2)
     * 
     * 
     */
    public function setSalesPartner(Request $request)
    {
        $customer_id = $request->route()->parameter('customer_id');

        $customerExistsForUpdate = Customer::where('store_id', $request->store->id)
            ->where('id',  $customer_id)->first();

        if ($customerExistsForUpdate == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
            ], 400);
        }

        $requestsAgencyByCustomer = AgencyRegisterRequest::where(
            'store_id',
            $request->store->id
        )
            ->where('customer_id', $customer_id)
            ->whereIn('status', [0, 3]);

        $requestsCollaboratorByCustomer = CollaboratorRegisterRequest::where(
            'store_id',
            $request->store->id
        )
            ->where('customer_id', $customer_id)
            ->whereIn('status', [0, 3]);


        if ($request->sale_type == 0) {

            $agencyExists = Agency::where('store_id', $request->store->id)->where('customer_id',  $customerExistsForUpdate->id)->first();
            if ($agencyExists != null) {
                $agencyExists->update(["status" => 0,]);
            }

            $collaboratorExists = Collaborator::where('store_id', $request->store->id)->where('customer_id',  $customerExistsForUpdate->id)->first();

            if ($collaboratorExists != null) {
                $collaboratorExists->update(["status" => 0,]);
            }
            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $customer_id,
                "Ghi chú",
                "Bạn đang ở vai trò khách hàng",
                TypeFCM::GET_CUSTOMER,
                null
            );
            $customerExistsForUpdate->update([
                'is_agency' =>  false,
                'is_collaborator' => false,
            ]);

            if ($requestsAgencyByCustomer) {

                $requestsAgencyByCustomer->update(['status' => 1]);
            }
            if ($requestsCollaboratorByCustomer) {

                $requestsCollaboratorByCustomer->update(['status' => 1]);
            }
        }

        if ($request->sale_type == 1) {
            $data = [];
            //disable agency

            $agencyExists = Agency::where(
                'store_id',
                $request->store->id
            )->where('customer_id',  $customerExistsForUpdate->id)
                ->first();

            if ($agencyExists != null) {
                $agencyExists->update([
                    "status" => 0,
                ]);

                $data = [
                    'cmnd' => $agencyExists->cmnd,
                    'bank' => $agencyExists->bank,
                    'branch' => $agencyExists->branch,
                    'issued_by' => $agencyExists->issued_by,
                    'back_card' => $agencyExists->back_card,
                    'front_card' => $agencyExists->front_card,
                    'account_name' => $agencyExists->account_name,
                    'account_number' => $agencyExists->account_number,
                    'first_and_last_name' => $agencyExists->first_and_last_name,
                ];
            }

            //allow collaborator
            $collaborator = Collaborator::where('store_id', $request->store->id)
                ->where('customer_id', $customerExistsForUpdate->id)->first();
            if ($collaborator  != null && $collaborator->status !=  1) {
                PushNotificationCustomerJob::dispatch(
                    $request->store->id,
                    $collaborator->customer_id,
                    "Ghi chú",
                    "Bạn đang ở vai trò CTV",
                    TypeFCM::GET_CTV,
                    null
                );
            }
            if ($collaborator  == null) {
                $data = array_merge($data, [
                    'store_id' => $request->store->id,
                    'customer_id' =>  $customerExistsForUpdate->id,
                    "status" => 1,
                ]);

                Collaborator::create($data);
            } else {
                $data = array_merge($data, [
                    "status" => 1,
                ]);

                $collaborator->update($data);
            }

            $customerExistsForUpdate->update([
                'is_agency' => false,
                'is_collaborator' => true,
            ]);

            if ($requestsAgencyByCustomer) {

                $requestsAgencyByCustomer->update(['status' => 1]);
            }

            if ($requestsCollaboratorByCustomer) {

                $requestsCollaboratorByCustomer->update(['status' => 2]);
            }
        }

        $agency_type_name = null;
        if ($request->sale_type == 2) {
            $data = [];
            $allTypeAgency = AgencyType::where('store_id', $request->store->id)->get();
            if (count($allTypeAgency) > 0) {
                $agencyTypeByIdExists = AgencyType::where('id', $request->agency_type_id)
                    ->where('store_id', $request->store->id)
                    ->first();

                if ($agencyTypeByIdExists  == null) {
                    return response()->json([
                        'code' => 404,
                        'success' => false,
                        'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                        'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
                    ], 404);
                }
                $agency_type_name = $agencyTypeByIdExists->name;
            }

            $collaboratorExists = Collaborator::where(
                'store_id',
                $request->store->id
            )->where('customer_id',  $customerExistsForUpdate->id)
                ->first();
            if ($collaboratorExists != null) {
                $collaboratorExists->update([
                    "status" => 0,
                ]);

                $data = [
                    'cmnd' => $collaboratorExists->cmnd,
                    'bank' => $collaboratorExists->bank,
                    'branch' => $collaboratorExists->branch,
                    'issued_by' => $collaboratorExists->issued_by,
                    'back_card' => $collaboratorExists->back_card,
                    'front_card' => $collaboratorExists->front_card,
                    'account_name' => $collaboratorExists->account_name,
                    'account_number' => $collaboratorExists->account_number,
                    'first_and_last_name' => $collaboratorExists->first_and_last_name,
                ];
            }
            //allow agency
            $agency = Agency::where('store_id', $request->store->id)
                ->where('customer_id', $customerExistsForUpdate->id)->first();

            if ($agency != null) {
                PushNotificationCustomerJob::dispatch(
                    $request->store->id,
                    $agency->customer_id,
                    "Ghi chú",
                    $agency_type_name ? "Bạn đang ở vai trò đại lý " . $agency_type_name : "Bạn đang ở vai trò đại lý",
                    TypeFCM::GET_AGENCY,
                    null
                );
            }
            if ($agency  == null) {
                $data = array_merge($data, [
                    'agency_type_id' => $request->agency_type_id,
                    'store_id' => $request->store->id,
                    'customer_id' =>   $customerExistsForUpdate->id,
                    "status" => 1,
                ]);

                Agency::create($data);
            } else {
                $data = array_merge($data, [
                    'status' => 1,
                    'agency_type_id' => $request->agency_type_id,
                ]);

                $agency->update($data);
            }


            $customerExistsForUpdate->update([
                'is_agency' =>  true,
                'is_collaborator' => false,
            ]);

            if ($requestsAgencyByCustomer) {

                $requestsAgencyByCustomer->update(['status' => 2]);
            }

            if ($requestsCollaboratorByCustomer) {

                $requestsCollaboratorByCustomer->update(['status' => 1]);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Customer::where('id', $customerExistsForUpdate->id)->first()
        ], 200);
    }


    /**
     * Phân loại nhiều đối tác bán hàng
     *
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam  sale_type string required 1 cộng tác viên 2 đại lý 0 không gì cả
     * @urlParam  customer_ids int danh sách id của khách hàng
     * @urlParam agency_type_id (nếu sale type == 2)
     * 
     * 
     */

    public function setListSalesPartner(Request $request)
    {
        $customers = Customer::where('store_id', $request->store->id)
            ->whereIn('id',  request("customer_ids"))
            ->get();

        foreach ($customers as $customerExistsForUpdate) {
            DB::transaction(function () use ($customerExistsForUpdate, $request) {
                $customer_id = $customerExistsForUpdate->id;

                $requestsAgencyByCustomer = AgencyRegisterRequest::where('store_id', $request->store->id)
                    ->where('customer_id', $customer_id)
                    ->whereIn('status', [0, 3]);

                $requestsCollaboratorByCustomer = CollaboratorRegisterRequest::where(
                    'store_id',
                    $request->store->id
                )
                    ->where('customer_id', $customer_id)
                    ->whereIn('status', [0, 3]);


                if ($request->sale_type == 0) {

                    $agencyExists = Agency::where('store_id', $request->store->id)->where('customer_id',  $customerExistsForUpdate->id)->first();
                    if ($agencyExists != null) {
                        $agencyExists->update(["status" => 0,]);
                    }

                    $collaboratorExists = Collaborator::where('store_id', $request->store->id)->where('customer_id',  $customerExistsForUpdate->id)->first();

                    if ($collaboratorExists != null) {
                        $collaboratorExists->update(["status" => 0,]);
                    }
                    PushNotificationCustomerJob::dispatch(
                        $request->store->id,
                        $customer_id,
                        "Ghi chú",
                        "Bạn đang ở vai trò khách hàng",
                        TypeFCM::GET_CUSTOMER,
                        null
                    );
                    $customerExistsForUpdate->update([
                        'is_agency' =>  false,
                        'is_collaborator' => false,
                    ]);

                    if ($requestsAgencyByCustomer) {

                        $requestsAgencyByCustomer->update(['status' => 1]);
                    }
                    if ($requestsCollaboratorByCustomer) {

                        $requestsCollaboratorByCustomer->update(['status' => 1]);
                    }
                }

                if ($request->sale_type == 1) {
                    $data = [];
                    //disable agency

                    $agencyExists = Agency::where(
                        'store_id',
                        $request->store->id
                    )->where('customer_id',  $customerExistsForUpdate->id)
                        ->first();

                    if ($agencyExists != null) {
                        $agencyExists->update([
                            "status" => 0,
                        ]);

                        $data = [
                            'cmnd' => $agencyExists->cmnd,
                            'bank' => $agencyExists->bank,
                            'branch' => $agencyExists->branch,
                            'issued_by' => $agencyExists->issued_by,
                            'back_card' => $agencyExists->back_card,
                            'front_card' => $agencyExists->front_card,
                            'account_name' => $agencyExists->account_name,
                            'account_number' => $agencyExists->account_number,
                            'first_and_last_name' => $agencyExists->first_and_last_name,
                        ];
                    }

                    //allow collaborator
                    $collaborator = Collaborator::where('store_id', $request->store->id)
                        ->where('customer_id', $customerExistsForUpdate->id)->first();
                    if ($collaborator  != null && $collaborator->status !=  1) {
                        PushNotificationCustomerJob::dispatch(
                            $request->store->id,
                            $collaborator->customer_id,
                            "Ghi chú",
                            "Bạn đang ở vai trò CTV",
                            TypeFCM::GET_CTV,
                            null
                        );
                    }
                    if ($collaborator  == null) {
                        $data = array_merge($data, [
                            'store_id' => $request->store->id,
                            'customer_id' =>  $customerExistsForUpdate->id,
                            "status" => 1,
                        ]);

                        Collaborator::create($data);
                    } else {
                        $data = array_merge($data, [
                            "status" => 1,
                        ]);

                        $collaborator->update($data);
                    }

                    $customerExistsForUpdate->update([
                        'is_agency' => false,
                        'is_collaborator' => true,
                    ]);

                    if ($requestsAgencyByCustomer) {

                        $requestsAgencyByCustomer->update(['status' => 1]);
                    }

                    if ($requestsCollaboratorByCustomer) {

                        $requestsCollaboratorByCustomer->update(['status' => 2]);
                    }
                }

                $agency_type_name = null;
                if ($request->sale_type == 2) {
                    $data = [];
                    $allTypeAgency = AgencyType::where('store_id', $request->store->id)->get();
                    if (count($allTypeAgency) > 0) {
                        $agencyTypeByIdExists = AgencyType::where('id', $request->agency_type_id)
                            ->where('store_id', $request->store->id)
                            ->first();

                        if ($agencyTypeByIdExists  == null) {
                            return response()->json([
                                'code' => 404,
                                'success' => false,
                                'msg_code' => MsgCode::NO_AGENCY_TYPE_EXISTS[0],
                                'msg' => MsgCode::NO_AGENCY_TYPE_EXISTS[1],
                            ], 404);
                        }
                        $agency_type_name = $agencyTypeByIdExists->name;
                    }

                    $collaboratorExists = Collaborator::where(
                        'store_id',
                        $request->store->id
                    )->where('customer_id',  $customerExistsForUpdate->id)
                        ->first();
                    if ($collaboratorExists != null) {
                        $collaboratorExists->update([
                            "status" => 0,
                        ]);

                        $data = [
                            'cmnd' => $collaboratorExists->cmnd,
                            'bank' => $collaboratorExists->bank,
                            'branch' => $collaboratorExists->branch,
                            'issued_by' => $collaboratorExists->issued_by,
                            'back_card' => $collaboratorExists->back_card,
                            'front_card' => $collaboratorExists->front_card,
                            'account_name' => $collaboratorExists->account_name,
                            'account_number' => $collaboratorExists->account_number,
                            'first_and_last_name' => $collaboratorExists->first_and_last_name,
                        ];
                    }
                    //allow agency
                    $agency = Agency::where('store_id', $request->store->id)
                        ->where('customer_id', $customerExistsForUpdate->id)->first();

                    if ($agency != null) {
                        PushNotificationCustomerJob::dispatch(
                            $request->store->id,
                            $agency->customer_id,
                            "Ghi chú",
                            $agency_type_name ? "Bạn đang ở vai trò đại lý " . $agency_type_name : "Bạn đang ở vai trò đại lý",
                            TypeFCM::GET_AGENCY,
                            null
                        );
                    }
                    if ($agency  == null) {
                        $data = array_merge($data, [
                            'agency_type_id' => $request->agency_type_id,
                            'store_id' => $request->store->id,
                            'customer_id' =>   $customerExistsForUpdate->id,
                            "status" => 1,
                        ]);

                        Agency::create($data);
                    } else {
                        $data = array_merge($data, [
                            'status' => 1,
                            'agency_type_id' => $request->agency_type_id,
                        ]);

                        $agency->update($data);
                    }


                    $customerExistsForUpdate->update([
                        'is_agency' =>  true,
                        'is_collaborator' => false,
                    ]);

                    if ($requestsAgencyByCustomer) {

                        $requestsAgencyByCustomer->update(['status' => 2]);
                    }

                    if ($requestsCollaboratorByCustomer) {

                        $requestsCollaboratorByCustomer->update(['status' => 1]);
                    }
                }
            });
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
