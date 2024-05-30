<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\GroupCustomer;
use App\Models\GroupCustomerConditionItem;
use App\Models\MsgCode;
use Illuminate\Http\Request;


/**
 * @group User/Nhóm khách hàng
 * 
 */
class GroupControllerController extends Controller
{
    /**
     * Tạo nhóm khách hàng
     * 
     * @bodyParam name string required Tên nhóm khách hàng
     * @bodyParam note file required Ghi chú
     * @bodyParam list {type_compare,comparison_expression,value_compare)
     * 
     * @bodyParam type_compare Kiểu so sánh //0 Tổng mua (Chỉ đơn hoàn thành), 1 tổng bán, 2 Xu hiện tại, 3 Số lần mua hàng 4, tháng sinh nhật 5, tuổi 6, giới tính, 7 tỉnh, 8 ngày đăng ký
     * @bodyParam comparison_expression Biểu thức so sánh  (>,>=,=,<,<=)
     * @bodyParam value_compare Giá trị so sánh so sánh
     * @bodyParam group_type Loại nhóm khách hàng //0 theo điều kiện //1 theo danh sách
     * 
     */
    public function create(Request $request)
    {


        $checkGroupExists = GroupCustomer::where(
            'name',
            $request->name
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkGroupExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }

        if ($request->group_type == GroupCustomer::GROUP_TYPE_LIST_CUSTOMER) {
            $groupCustomerCreate = GroupCustomer::create(
                [

                    'name' => $request->name,
                    'store_id' => $request->store->id,
                    'note' => $request->note,
                    'group_type' => GroupCustomer::GROUP_TYPE_LIST_CUSTOMER,
                ]
            );

            if ($request->customer_ids != null && is_array($request->customer_ids) && count($request->customer_ids) > 0) {
                $groupCustomerCreate->customers()->attach($request->customer_ids, [
                    'store_id' => $request->store->id
                ]);
            }

            return response()->json([
                'code' => 201,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $groupCustomerCreate
            ], 201);
        }

        $groupCustomerCreate = GroupCustomer::create(
            [

                'name' => $request->name,
                'store_id' => $request->store->id,
                'note' => $request->note,
                'group_type' => GroupCustomer::GROUP_TYPE_CONDITION,
            ]
        );



        if ($request->condition_items != null && is_array($request->condition_items)) {
            foreach ($request->condition_items as $item) {
                GroupCustomerConditionItem::create([
                    'store_id' => $request->store->id,
                    'group_customer_id' =>    $groupCustomerCreate->id,
                    'type_compare' => $item['type_compare'],
                    'comparison_expression' => $item['comparison_expression'],
                    'value_compare' => $item['value_compare'],
                ]);
            }
        }
        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $groupCustomerCreate
        ], 201);
    }


    /**
     * Danh sách nhóm khách hàng
     * @urlParam  store_code required Store code
     */
    public function getAll(Request $request)
    {

        $groupCustomers = GroupCustomer::where('store_id', $request->store->id)
            ->orderBy('id', 'desc')
            ->paginate($request->limit ?: 20);

        foreach ($groupCustomers as $groupCustomer) {

            if ($groupCustomer->group_type == GroupCustomer::GROUP_TYPE_LIST_CUSTOMER) {
                $groupCustomer->count_customers = count($groupCustomer->customers()->get());
            } else {
                $conditionItems = json_encode($groupCustomer->groupCustomerConditionItems()->get());

                $request->merge([
                    "group_customer_id" => $groupCustomer->id,
                    "condition_items" => $conditionItems,
                    "count_customer" => true
                ]);

                $countListCustomers = (new CustomerController)->getAll($request);
                $groupCustomer->count_customers = $countListCustomers ?? 0;
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $groupCustomers,
        ], 200);
    }

    /**
     * Danh sách khách hàng theo nhóm
     * @urlParam  store_code required Store code
     * @parameter group_customer_id required Id của nhóm khách hàng
     * @bodyParam condition_items {type_compare,comparison_expression,value_compare) điều kiện lọc của khách hàng
     */
    public function getListCustomersByGroup(Request $request)
    {
        $id = $request->route()->parameter('group_customer_id');
        $groupExists = GroupCustomer::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($groupExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_GROUP_CUSTOMER[0],
                'msg' => MsgCode::NO_GROUP_CUSTOMER[1],
            ], 404);
        }

        $conditionItems = json_encode($groupExists->groupCustomerConditionItems()->get());

        $request->merge([
            "group_customer_id" => $groupExists->id,
            "group_customer_type" => $groupExists->group_type,
            "condition_items" => $conditionItems,
            "customer_group_ids" => $groupExists->customers()->get()->pluck('id')->toArray(),
        ]);

        $listCustomers = (new CustomerController)->getAll($request);

        return $listCustomers;
    }



    /**
     * update một GroupCustomer
     * 
     * @bodyParam name string required Tên nhóm khách hàng
     * @bodyParam note file required Ghi chú
     * @bodyParam list {type_compare,comparison_expression,value_compare)
     * 
     * @bodyParam type_compare Kiểu so sánh //0 Tổng mua (Chỉ đơn hoàn thành), 1 tổng bán, 2 Xu hiện tại, 3 Số lần mua hàng 4, tháng sinh nhật 5, tuổi 6, giới tính, 7 tỉnh, 8 ngày đăng ký, 9 CTV, 10 AGENCY
     * @bodyParam comparison_expression Biểu thức so sánh  (>,>=,=,<,<=)
     * @bodyParam value_compare Giá trị so sánh so sánh (đối với ctv vs agency 0 là tất cả)
     */
    public function update(Request $request)
    {

        $id = $request->route()->parameter('group_customer_id');
        $checkGroupCustomerExists = GroupCustomer::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkGroupCustomerExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_GROUP_CUSTOMER[0],
                'msg' => MsgCode::NO_GROUP_CUSTOMER[1],
            ], 404);
        }

        $checkGroupCustomerExists2 = GroupCustomer::where(
            'name',
            $request->name
        )->where(
            'store_id',
            $request->store->id
        )->where(
            'id',
            '<>',
            $id
        )->first();

        if ($checkGroupCustomerExists2 != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NO_GROUP_CUSTOMER[1],
            ], 400);
        }

        $checkGroupCustomerExists->update(Helper::sahaRemoveItemArrayIfNullValue([
            'name' => $request->name,
            'store_id' => $request->store->id,
            'note' => $request->note,
            'group_type' => $request->group_type,

        ]));

        if ($checkGroupCustomerExists->group_type == GroupCustomer::GROUP_TYPE_LIST_CUSTOMER) {

            if ($request->customer_ids != null && is_array($request->customer_ids) && count($request->customer_ids) > 0) {
                $checkGroupCustomerExists->customers()->detach();
                $checkGroupCustomerExists->customers()->attach($request->customer_ids, [
                    'store_id' => $request->store->id
                ]);
            }
        } else {
            GroupCustomerConditionItem::where([
                'store_id' => $request->store->id,
                'group_customer_id' => $checkGroupCustomerExists->id,
            ])->delete();

            if ($request->condition_items != null && is_array($request->condition_items)) {
                foreach ($request->condition_items as $item) {
                    GroupCustomerConditionItem::create([
                        'store_id' => $request->store->id,
                        'group_customer_id' =>    $checkGroupCustomerExists->id,
                        'type_compare' => $item['type_compare'],
                        'comparison_expression' => $item['comparison_expression'],
                        'value_compare' => $item['value_compare'],
                    ]);
                }
            }
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => GroupCustomer::where('id', $id)->first(),
        ], 200);
    }


    /**
     * remove một GroupCustomer
     * 
     */
    public function delete(Request $request)
    {


        $id = $request->route()->parameter('group_customer_id');
        $checkGroupCustomerExists = GroupCustomer::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkGroupCustomerExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_GROUP_CUSTOMER[0],
                'msg' => MsgCode::NO_GROUP_CUSTOMER[1],
            ], 404);
        }
        $checkGroupCustomerExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * thông tin 1 nhóm khách hàng
     * 
     */
    public function getOne(Request $request)
    {


        $id = $request->route()->parameter('group_customer_id');
        $checkGroupCustomerExists = GroupCustomer::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkGroupCustomerExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_GROUP_CUSTOMER[0],
                'msg' => MsgCode::NO_GROUP_CUSTOMER[1],
            ], 404);
        }


        $checkGroupCustomerExists->condition_items = GroupCustomerConditionItem::select('type_compare', 'comparison_expression', 'value_compare')
            ->where([
                ['group_customer_id', $checkGroupCustomerExists->id],
                ['store_id', $request->store->id]
            ])
            ->get();
        $checkGroupCustomerExists->customers;



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $checkGroupCustomerExists
        ], 200);
    }
}
