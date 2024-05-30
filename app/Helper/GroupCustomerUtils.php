<?php

namespace App\Helper;

use App\Models\Customer;
use App\Models\GroupCustomer;
use App\Models\Order;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GroupCustomerUtils
{

    //
    const TYPE_COMPARE_TOTAL_FINAL_COMPLETED = 0; // Tổng mua (Chỉ đơn hoàn thành trừ trả hàng), 
    const TYPE_COMPARE_TOTAL_FINAL_WITH_REFUND = 1; // Tổng mua (Tất cả trạng thái đơn trừ trả hàng), 
    const TYPE_COMPARE_POINT = 2; // Xu hiện tại, 
    const TYPE_COMPARE_COUNT_ORDER = 3; // Số lần mua hàng 
    const TYPE_COMPARE_MONTH_BIRTH = 4; // tháng sinh nhật 
    const TYPE_COMPARE_AGE = 5; // tuổi 
    const TYPE_COMPARE_SEX = 6; // giới tính, 
    const TYPE_COMPARE_PROVINCE = 7; // tỉnh, 
    const TYPE_COMPARE_DATE_REG = 8; // ngày đăng ký
    const TYPE_COMPARE_CTV = 9; // cộng tác viên
    const TYPE_COMPARE_AGENCY = 10; // Đại lý
    const TYPE_COMPARE_GROUP_CUSTOMER = 11; // Theo nhóm khách hàng
    const TYPE_COMPARE_CUSTOMER_NORMAL = 12; //Khách lẻ bình thường 

    static function check_valid_ok_customer(
        $request,
        $group_customer_in_program,
        $agency_type_id_in_program,
        $group_type_id_in_program,
        $customer,
        $store_id,
        $group_customers_in_program,
        $agency_types_in_program,
        $group_types_in_program
    ) {

        $ok_customer = Cache::remember(
            json_encode([
                "check_valid_ok_customer", 'user_id', $request->user == null ? null : $request->user->id,
                'group_customer_in_program', $group_customer_in_program,
                'agency_type_id_in_program', $agency_type_id_in_program,
                'group_type_id_in_program', $group_type_id_in_program,
                'customer', $customer == null ? null : $customer->id,
                'store_id', $store_id,
                'group_customers_in_program', $group_customers_in_program,
                'agency_types_in_program', $agency_types_in_program,
                'group_types_in_program', $group_types_in_program,
            ]),
            6,
            function () use (
                $request,
                $customer,
                $store_id,
                $agency_type_id_in_program,
                $group_type_id_in_program,
                $group_customer_in_program,
                $group_customers_in_program,
                $agency_types_in_program,
                $group_types_in_program
            ) {
                if ($group_customers_in_program && is_array($group_customers_in_program) && count($group_customers_in_program) > 0) {

                    if (in_array(CustomerUtils::GROUP_CUSTOMER_ALL, $group_customers_in_program)) {

                        return true;
                    }

                    if (!in_array(CustomerUtils::GROUP_CUSTOMER_ALL, $group_customers_in_program)) {

                        if ($customer != null) {

                            $is_normal_guest = false;
                            $is_ctv = false;
                            $is_agency = false;
                            $is_group_condition = false;

                            if (in_array(CustomerUtils::GROUP_CUSTOMER_NORMAL_GUEST, $group_customers_in_program) && !CollaboratorUtils::isCollaborator($customer->id,  $store_id) && !AgencyUtils::isAgencyByCustomerId($customer->id)) {

                                $is_normal_guest = true;
                            }

                            if (in_array(CustomerUtils::GROUP_CUSTOMER_CTV, $group_customers_in_program) && CollaboratorUtils::isCollaborator($customer->id,  $store_id)) {

                                $is_ctv = true;
                            }

                            if (is_array($agency_types_in_program) && count($agency_types_in_program) > 0) {

                                $agency_type_ids = Arr::pluck($agency_types_in_program, 'id');
                                $is_agency = in_array(CustomerUtils::GROUP_CUSTOMER_AGENCY, $group_customers_in_program) && AgencyUtils::isAgencyByIdsAndLever($customer->id, $agency_type_ids);
                            }

                            if (is_array($group_types_in_program) && count($group_types_in_program) > 0) {

                                $group_type_ids = Arr::pluck($group_types_in_program, 'id');
                                $is_group_condition = in_array(CustomerUtils::GROUP_CUSTOMER_BY_CONDITION, $group_customers_in_program) && GroupCustomerUtils::check_valid_list_condition_ids($customer, $group_type_ids);
                            }

                            return $is_normal_guest || $is_ctv || $is_agency || $is_group_condition;
                        } else {
                            if (in_array(CustomerUtils::GROUP_CUSTOMER_NOT_LOGGED_IN, $group_customers_in_program)) {
                                return true;
                            }
                        }
                    }
                } else {

                    if ($group_customer_in_program == CustomerUtils::GROUP_CUSTOMER_ALL) {
                        return true;
                    }
                    if ($customer != null && $group_customer_in_program == CustomerUtils::GROUP_CUSTOMER_NORMAL_GUEST && !CollaboratorUtils::isCollaborator($customer->id,  $store_id) && !AgencyUtils::isAgencyByCustomerId($customer->id)) {
                        return true;
                    }
                    if ($group_customer_in_program != CustomerUtils::GROUP_CUSTOMER_ALL) {
                        if ($customer != null) {
                            return ($group_customer_in_program == CustomerUtils::GROUP_CUSTOMER_ALL ||
                                ($group_customer_in_program == CustomerUtils::GROUP_CUSTOMER_CTV && $customer != null && CollaboratorUtils::isCollaborator($customer->id,  $store_id))
                                ||
                                ($group_customer_in_program == CustomerUtils::GROUP_CUSTOMER_AGENCY && $customer != null && AgencyUtils::isAgencyByIdAndLever($customer->id, $agency_type_id_in_program))
                                ||
                                ($group_customer_in_program == CustomerUtils::GROUP_CUSTOMER_BY_CONDITION && $customer != null && GroupCustomerUtils::check_valid_list_condition($customer, $group_type_id_in_program))
                            );
                        }
                    }
                }

                return false;
            }
        );


        return  $ok_customer;
    }

    static function check_valid_list_condition($customer, $group_customer_id)
    {
        $hasCTVorAgencyorCustomerNomal = false;
        $valid = true;

        $groupCustomerExist = GroupCustomer::where('id', $group_customer_id)->first();

        if ($groupCustomerExist && $groupCustomerExist->group_type == GroupCustomer::GROUP_TYPE_LIST_CUSTOMER) {
            $customersInGroupExist = $groupCustomerExist->customers()
                ->where('customer_id', $customer->id)
                ->first();

            $valid = $customersInGroupExist ? true : false;
        } else {

            $condition_items = DB::table('group_customer_condition_items')->select('type_compare', 'comparison_expression', 'value_compare')
                ->where([
                    ['group_customer_id', $group_customer_id],
                ])
                ->get();


            if (count($condition_items) > 0) {
                foreach ($condition_items as $condition_item) {

                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_COMPLETED) {
                        $total_final = DB::table("orders")->where([
                            ['store_id', $customer->store_id],
                            ['customer_id', $customer->id],
                            ['order_status', StatusDefineCode::COMPLETED],
                        ])->sum("total_final");

                        $total_refund = DB::table("orders")->where([
                            ['store_id', $customer->store_id],
                            ['customer_id', $customer->id],
                            ['order_status', StatusDefineCode::CUSTOMER_HAS_RETURNS],
                        ])->sum("total_final");


                        $valid = GroupCustomerUtils::compare_va($total_final - $total_refund, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_COMPLETED);
                    }

                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_WITH_REFUND) {

                        $total_final = DB::table("orders")->where([
                            ['store_id', $customer->store_id],
                            ['customer_id', $customer->id],
                            ['order_status', '!=', StatusDefineCode::CUSTOMER_HAS_RETURNS]
                        ])->sum("total_final");

                        $valid = GroupCustomerUtils::compare_va($total_final, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_WITH_REFUND);
                    }


                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_POINT) {
                        $points = DB::table("customers")->where([
                            ['store_id', $customer->store_id],
                            ['id', $customer->id],
                        ])->sum("points");

                        $valid = GroupCustomerUtils::compare_va($points, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_POINT);
                    }


                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_COUNT_ORDER) {
                        $count_order = DB::table("orders")->where([
                            ['store_id', $customer->store_id],
                            ['customer_id', $customer->id],
                        ])->count();

                        $valid = GroupCustomerUtils::compare_va($count_order, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_COUNT_ORDER);
                    }

                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_MONTH_BIRTH) {

                        $date_of_birth = DB::table('customers')
                            ->where([
                                ['store_id', $customer->store_id],
                                ['id', $customer->id],
                            ])->take(1)->pluck('date_of_birth')->first();

                        if ($date_of_birth == null) {
                            $valid = false;
                        } else {
                            try {
                                $birth = Carbon::parse($date_of_birth);

                                $month_birth =  $birth->month;
                                $valid = GroupCustomerUtils::compare_va($month_birth, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_MONTH_BIRTH);
                            } catch (Exception $e) {
                                $valid = false;
                            }
                        }
                    }


                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_SEX) {
                        $sex = DB::table('customers')
                            ->where([
                                ['store_id', $customer->store_id],
                                ['id', $customer->id],
                            ])->take(1)->pluck('sex')->first();
                        $valid = GroupCustomerUtils::compare_va($sex, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_SEX);
                    }

                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_PROVINCE) {
                        $province = DB::table('customers')
                            ->where([
                                ['store_id', $customer->store_id],
                                ['id', $customer->id],
                            ])->take(1)->pluck('province')->first();
                        $valid = GroupCustomerUtils::compare_va($province, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_PROVINCE);
                    }

                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_DATE_REG) {
                        $created_at = DB::table('customers')
                            ->where([
                                ['store_id', $customer->store_id],
                                ['id', $customer->id],
                            ])->take(1)->pluck('created_at')->first();
                        $valid = GroupCustomerUtils::compare_va($created_at, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_DATE_REG);
                    }

                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_AGE) {
                        $date_of_birth = DB::table('customers')
                            ->where([
                                ['store_id', $customer->store_id],
                                ['id', $customer->id],
                            ])->take(1)->pluck('date_of_birth')->first();

                        try {
                            $now = new DateTime(Helper::getTimeNowString());
                            $date_of_birth = new DateTime($date_of_birth);

                            $since_start =  $now->diff($date_of_birth);

                            $age = $since_start->y;

                            $valid = GroupCustomerUtils::compare_va($age, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_AGE);
                        } catch (Exception $e) {
                            $valid = false;
                        }
                    }

                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_CTV) {
                        $collaborator = DB::table('collaborators')->where('customer_id', $customer->id)->where('status', 1)->where('store_id', $customer->store_id)->first();
                        $valid =  $collaborator != null;
                    }

                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_CUSTOMER_NORMAL) {
                        $valid = !CollaboratorUtils::isCollaborator($customer->id,  $customer->store_id) && !AgencyUtils::isAgencyByCustomerId($customer->id);
                    }


                    if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_AGENCY) {
                        if ($condition_item->value_compare == 0) {
                            $valid = true;
                        } else {
                            $valid = AgencyUtils::isAgencyByIdAndLever($customer->id, $condition_item->value_compare);
                        }
                    }



                    if (($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_CTV)  ||
                        ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_CUSTOMER_NORMAL) ||
                        ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_AGENCY)
                    ) {
                        if ($valid == true) {
                            $hasCTVorAgencyorCustomerNomal = true;
                        } else {
                            if ($hasCTVorAgencyorCustomerNomal  == true) {
                                $valid = true;
                            }
                        }
                    }

                    if ($valid == false) {
                        break;
                    }
                }
            }
        }


        return $valid;
    }

    static function check_valid_list_condition_ids($customer, $group_customer_ids)
    {
        $list_valid = [];
        if (count($group_customer_ids) > 0) {

            foreach ($group_customer_ids as $group_customer_id) {
                $valid = true;
                $hasCTVorAgencyorCustomerNomal = false;

                $groupCustomerExist = GroupCustomer::where('id', $group_customer_id)->first();

                if ($groupCustomerExist && $groupCustomerExist->group_type == GroupCustomer::GROUP_TYPE_LIST_CUSTOMER) {
                    $customersInGroupExist = $groupCustomerExist->customers()
                        ->where('customer_id', $customer->id)
                        ->first();

                    $valid = $customersInGroupExist ? true : false;
                } else {
                    $condition_items = DB::table('group_customer_condition_items')->select('type_compare', 'comparison_expression', 'value_compare')
                        ->where([
                            ['group_customer_id', $group_customer_id],
                        ])
                        ->get();

                    if (count($condition_items) > 0) {

                        foreach ($condition_items as $condition_item) {

                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_COMPLETED) {
                                $total_final = DB::table("orders")->where([
                                    ['store_id', $customer->store_id],
                                    ['customer_id', $customer->id],
                                    ['order_status', StatusDefineCode::COMPLETED],
                                ])->sum("total_final");

                                $total_refund = DB::table("orders")->where([
                                    ['store_id', $customer->store_id],
                                    ['customer_id', $customer->id],
                                    ['order_status', StatusDefineCode::CUSTOMER_HAS_RETURNS],
                                ])->sum("total_final");


                                $valid = GroupCustomerUtils::compare_va($total_final - $total_refund, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_COMPLETED);
                            }

                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_WITH_REFUND) {

                                $total_final = DB::table("orders")->where([
                                    ['store_id', $customer->store_id],
                                    ['customer_id', $customer->id],
                                    ['order_status', '!=', StatusDefineCode::CUSTOMER_HAS_RETURNS]
                                ])->sum("total_final");

                                $valid = GroupCustomerUtils::compare_va($total_final, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_WITH_REFUND);
                            }


                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_POINT) {
                                $points = DB::table("customers")->where([
                                    ['store_id', $customer->store_id],
                                    ['id', $customer->id],
                                ])->sum("points");

                                $valid = GroupCustomerUtils::compare_va($points, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_POINT);
                            }


                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_COUNT_ORDER) {
                                $count_order = DB::table("orders")->where([
                                    ['store_id', $customer->store_id],
                                    ['customer_id', $customer->id],
                                ])->count();

                                $valid = GroupCustomerUtils::compare_va($count_order, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_COUNT_ORDER);
                            }

                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_MONTH_BIRTH) {

                                $date_of_birth = DB::table('customers')
                                    ->where([
                                        ['store_id', $customer->store_id],
                                        ['id', $customer->id],
                                    ])->take(1)->pluck('date_of_birth')->first();

                                if ($date_of_birth == null || (preg_match("/^0000-00-00/", $date_of_birth))) {
                                    $valid = false;
                                } else {
                                    try {
                                        $birth = Carbon::parse($date_of_birth);

                                        $month_birth =  $birth->month;
                                        $valid = GroupCustomerUtils::compare_va($month_birth, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_MONTH_BIRTH);
                                    } catch (Exception $e) {
                                        $valid = false;
                                    }
                                }
                            }


                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_SEX) {
                                $sex = DB::table('customers')
                                    ->where([
                                        ['store_id', $customer->store_id],
                                        ['id', $customer->id],
                                    ])->take(1)->pluck('sex')->first();
                                $valid = GroupCustomerUtils::compare_va($sex, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_SEX);
                            }

                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_PROVINCE) {
                                $province = DB::table('customers')
                                    ->where([
                                        ['store_id', $customer->store_id],
                                        ['id', $customer->id],
                                    ])->take(1)->pluck('province')->first();
                                $valid = GroupCustomerUtils::compare_va($province, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_PROVINCE);
                            }

                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_DATE_REG) {
                                $created_at = DB::table('customers')
                                    ->where([
                                        ['store_id', $customer->store_id],
                                        ['id', $customer->id],
                                    ])->take(1)->pluck('created_at')->first();
                                $valid = GroupCustomerUtils::compare_va($created_at, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_DATE_REG);
                            }

                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_AGE) {
                                $date_of_birth = DB::table('customers')
                                    ->where([
                                        ['store_id', $customer->store_id],
                                        ['id', $customer->id],
                                    ])->take(1)->pluck('date_of_birth')->first();

                                try {
                                    $now = new DateTime(Helper::getTimeNowString());
                                    $date_of_birth = new DateTime($date_of_birth);

                                    $since_start =  $now->diff($date_of_birth);

                                    $age = $since_start->y;

                                    $valid = GroupCustomerUtils::compare_va($age, $condition_item->comparison_expression,  $condition_item->value_compare,  GroupCustomerUtils::TYPE_COMPARE_AGE);
                                } catch (Exception $e) {
                                    $valid = false;
                                }
                            }

                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_CTV) {
                                $collaborator = DB::table('collaborators')->where('customer_id', $customer->id)->where('status', 1)->where('store_id', $customer->store_id)->first();
                                $valid =  $collaborator != null;
                            }

                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_CUSTOMER_NORMAL) {
                                $valid = !CollaboratorUtils::isCollaborator($customer->id,  $customer->store_id) && !AgencyUtils::isAgencyByCustomerId($customer->id);
                            }


                            if ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_AGENCY) {
                                if ($condition_item->value_compare == 0) {
                                    $valid = true;
                                } else {
                                    $valid = AgencyUtils::isAgencyByIdAndLever($customer->id, $condition_item->value_compare);
                                }
                            }



                            if (($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_CTV)  ||
                                ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_CUSTOMER_NORMAL) ||
                                ($condition_item->type_compare == GroupCustomerUtils::TYPE_COMPARE_AGENCY)
                            ) {
                                if ($valid == true) {
                                    $hasCTVorAgencyorCustomerNomal = true;
                                } else {
                                    if ($hasCTVorAgencyorCustomerNomal  == true) {
                                        $valid = true;
                                    }
                                }
                            }

                            if ($valid == false) {
                                break;
                            }
                        }
                    }
                }


                array_push($list_valid, $valid);
                if ($valid == true) {

                    break;
                }
            }
        }


        return in_array(true, $list_valid);
    }

    static function compare_va($value1, $comparison_expression, $value2, $typeCompare)
    {
        // const TYPE_COMPARE_TOTAL_FINAL_COMPLETED = 0; // Tổng mua (Chỉ đơn hoàn thành), 
        // const TYPE_COMPARE_TOTAL_FINAL_WITH_REFUND = 1; // Tổng mua (Tất cả trạng thái đơn), 
        // const TYPE_COMPARE_POINT = 2; // Xu hiện tại, 
        // const TYPE_COMPARE_COUNT_ORDER = 3; // Số lần mua hàng 
        // const TYPE_COMPARE_MONTH_BIRTH = 4; // tháng sinh nhật 
        // const TYPE_COMPARE_AGE = 5; // tuổi 
        // const TYPE_COMPARE_SEX = 6; // giới tính, 
        // const TYPE_COMPARE_PROVINCE = 7; // tỉnh, 
        // const TYPE_COMPARE_DATE_REG = 8; // ngày đăng ký

        //  (>,>=,=,<,<=)

        if (
            $typeCompare == GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_COMPLETED ||
            $typeCompare == GroupCustomerUtils::TYPE_COMPARE_TOTAL_FINAL_WITH_REFUND ||
            $typeCompare == GroupCustomerUtils::TYPE_COMPARE_POINT ||
            $typeCompare == GroupCustomerUtils::TYPE_COMPARE_COUNT_ORDER ||
            $typeCompare == GroupCustomerUtils::TYPE_COMPARE_MONTH_BIRTH ||
            $typeCompare == GroupCustomerUtils::TYPE_COMPARE_AGE ||
            $typeCompare == GroupCustomerUtils::TYPE_COMPARE_SEX ||
            $typeCompare == GroupCustomerUtils::TYPE_COMPARE_PROVINCE ||
            $typeCompare == GroupCustomerUtils::TYPE_COMPARE_DATE_REG
        ) {


            if ($typeCompare == GroupCustomerUtils::TYPE_COMPARE_DATE_REG) {
                try {
                    $value1 = new DateTime($value1);
                    $value2 = new DateTime($value2);

                    $value1 = new DateTime($value1->format('Y-m-d'));
                    $value2 = new DateTime($value2->format('Y-m-d'));
                } catch (Exception $e) {
                    return false;
                }
            } else {
                $value1 = (int)$value1;
                $value2 = (int)$value2;
            }



            if ($comparison_expression == ">") {
                return $value1 >  $value2;
            }
            if ($comparison_expression == ">=") {
                return $value1 >=  $value2;
            }
            if ($comparison_expression == "=") {

                return $value1 ==  $value2;
            }
            if ($comparison_expression == "<") {
                return $value1 <  $value2;
            }
            if ($comparison_expression == "<=") {
                return $value1 <=  $value2;
            }
        }

        return true;
    }
}
