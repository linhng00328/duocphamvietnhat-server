<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\ProductUtils;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\Distribute;
use App\Models\ElementDistribute;
use App\Models\InventoryHistory;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\SubElementDistribute;
use App\Models\TallySheet;
use App\Models\TallySheetItems;
use Illuminate\Http\Request;


/**
 * @group  User/Cấu hình kho
 */

class InventoryController extends Controller
{
    /**
     * Lịch sử kho
     * @urlParam  store_code required Store code
     * @bodyParam product_id int required ID sản phẩm
     * @bodyParam distribute_name Tên phân loại  (có thể để trống thì vào mặc định)
     * @bodyParam element_distribute_name giá trị phân loại (có thể để trống thì vào mặc định)
     * @bodyParam sub_element_distribute_name Giá trị thuộc tính con của phân loại (có thể để trống thì vào mặc định)
     */
    public function inventoryHistory(Request $request)
    {

        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));
        $branch_id  = null;
        $histories = null;

        if ($request->branch != null) {
            $branch_id  = $request->branch->id;
        }


        $product_id = $request->product_id;
        $store_id = $request->store->id;

        $branch = request('branch', $default = null);
        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $branch_ids_input = array();
        if ($branch != null) {
            $branch_ids_input = [$branch->id];
        } else if (count($branch_ids) > 0) {
            $branch_ids_input =  $branch_ids;
        }

        $distribute_name  = $request->distribute_name;
        $element_distribute_name = $request->element_distribute_name;
        $sub_element_distribute_name = $request->sub_element_distribute_name;

        $productExist = Product::where('id', $product_id)->where('store_id', $request->store->id)
            ->first();
        if ($productExist == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        }



        if (!empty($distribute_name) && !empty($element_distribute_name) && !empty($sub_element_distribute_name)) {

            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_name)->where('store_id', $store_id)->first();

            if ($distribute != null) {

                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                if ($ele_distribute != null) {
                    $sub_ele_distribute =    SubElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('element_distribute_id', $ele_distribute->id)
                        ->where('name', $sub_element_distribute_name)->where('store_id', $store_id)->first();

                    if ($sub_ele_distribute  != null) {
                        $histories  =     InventoryHistory::where('store_id', $request->store->id)
                            ->where('element_distribute_id', $ele_distribute->id)
                            ->where('sub_element_distribute_id', $sub_ele_distribute->id)
                            ->when($branch_id != null, function ($query) use ($branch_id) {
                                $query->where('branch_id',  $branch_id);
                            })
                            ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                                $query->whereIn('branch_id', $branch_ids);
                            })
                            ->where('product_id',  $request->product_id);
                    }
                }
            }
        } else if (!empty($distribute_name) && !empty($element_distribute_name)) {

            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_name)->where('store_id', $store_id)->first();

            if ($distribute != null) {
                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                if ($ele_distribute != null) {
                    $histories  =     InventoryHistory::where('store_id', $request->store->id)
                        ->where('element_distribute_id', $ele_distribute->id)
                        ->where('sub_element_distribute_id', null)
                        ->when($branch_id != null, function ($query) use ($branch_id) {
                            $query->where('branch_id',  $branch_id);
                        })
                        ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                            $query->whereIn('branch_id', $branch_ids);
                        })
                        ->where('product_id',  $request->product_id);
                }
            }
        } else {

            $histories  =     InventoryHistory::where('store_id', $request->store->id)
                ->where('element_distribute_id', null)
                ->where('sub_element_distribute_id', null)
                ->when($branch_id != null, function ($query) use ($branch_id) {
                    $query->where('branch_id',  $branch_id);
                })
                ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                    $query->whereIn('branch_id', $branch_ids);
                })
                ->where('product_id',  $request->product_id);
        }

        if ($histories != null) {
            $histories = $histories
                ->orderBy('id', 'desc')
                ->paginate(request('limit') == null ? 20 : request('limit'));
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $histories
        ], 200);
    }


    /**
     * Cấu hình kho cho sản phẩm
     * @urlParam  store_code required Store code
     * @bodyParam product_id int required ID sản phẩm
     * @bodyParam distribute_name Tên phân loại  (có thể để trống thì vào mặc định)
     * @bodyParam element_distribute_name giá trị phân loại (có thể để trống thì vào mặc định)
     * @bodyParam sub_element_distribute_name Giá trị thuộc tính con của phân loại (có thể để trống thì vào mặc định)
     * @bodyParam cost_of_capital double giá vốn 
     * @bodyParam stock int Tồn kho hiện tại
     */
    public function updateInventoryBalance(Request $request)
    {
        $productExist = Product::where('id', $request->product_id)->where('store_id', $request->store->id)
            ->first();

        if ($productExist == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        }




        $product_id = $request->product_id;

        $distribute_name = $request->distribute_name;
        $element_distribute_name = $request->element_distribute_name;
        $sub_element_distribute_name = $request->sub_element_distribute_name;

        $productExist = Product::where('id', $product_id)->where('store_id', $request->store->id)
            ->first();

        if ($productExist == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        }

        $status_stock =  ProductUtils::get_id_distribute_and_stock(
            $request->store->id,
            $request->branch->id,
            $product_id,
            $distribute_name,
            $element_distribute_name,
            $sub_element_distribute_name
        );

        if ($status_stock == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[0],
                'msg' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[1],
            ], 400);
        }

        $tallySheetCreate = TallySheet::create([
            "branch_id" => $request->branch->id,
            "store_id" =>  $request->store->id,
            'code' => Helper::getRandomTallySheetString(),
            'status' => InventoryUtils::STATUS_INVENTORY_BALANCE,
            'note' => $request->note,
            'existing_branch' => $status_stock['stock'],
            'reality_exist' => $request->stock,
            'deviant' => $request->stock - $status_stock['stock'],
            'user_id' => $request->user != null ? $request->user->id : null,
            'staff_id' =>  $request->staff != null ? $request->staff->id : null,
        ]);


        if ($status_stock['type'] == ProductUtils::HAS_SUB) {
            TallySheetItems::create([
                "tally_sheet_id" => $tallySheetCreate->id,
                "branch_id" => $request->branch->id,
                "store_id" =>  $request->store->id,
                'product_id' =>   $product_id,
                'element_distribute_id' => $status_stock['element_distribute_id'] ?? null,
                'sub_element_distribute_id' => $status_stock['sub_element_distribute_id'] ?? null,
                "distribute_name" =>     $distribute_name,
                "element_distribute_name" =>  $element_distribute_name,
                "sub_element_distribute_name" =>   $sub_element_distribute_name,
                "existing_branch"  => $status_stock['stock'],
                "reality_exist" =>  $request->stock,
                "deviant" =>   $request->stock - $status_stock['stock'],
            ]);
        }
        if ($status_stock['type'] == ProductUtils::HAS_ELE) {
            TallySheetItems::create([
                "tally_sheet_id" => $tallySheetCreate->id,
                "branch_id" => $request->branch->id,
                "store_id" =>  $request->store->id,
                'product_id' =>   $product_id,
                'element_distribute_id' => $status_stock['element_distribute_id'],

                "distribute_name" =>     $distribute_name,
                "element_distribute_name" =>  $element_distribute_name,

                "existing_branch"  => $status_stock['stock'],
                "reality_exist" =>  $request->stock,
                "deviant" =>   $request->stock - $status_stock['stock'],
            ]);
        }
        if ($status_stock['type'] == ProductUtils::NO_ELE_SUB) {
            TallySheetItems::create([
                "tally_sheet_id" => $tallySheetCreate->id,
                "branch_id" => $request->branch->id,
                "store_id" =>  $request->store->id,
                'product_id' =>   $product_id,
                "existing_branch"  => $status_stock['stock'],
                "reality_exist" =>  $request->stock,
                "deviant" =>   $request->stock - $status_stock['stock'],
            ]);
        }


        $valueHandle = InventoryUtils::update_cost_of_capital_or_stock_by_id(
            $request->store->id,
            $request->branch->id,
            $productExist->id,
            $status_stock['element_distribute_id'] ?? null,
            $status_stock['sub_element_distribute_id'] ?? null,
            $request->cost_of_capital,
            $request->stock,
            null,
            $tallySheetCreate->id,
            $tallySheetCreate->code
        );


        if ($valueHandle == false) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VERSION_NOT_FOUND_TO_UPDATE[0],
                'msg' => MsgCode::VERSION_NOT_FOUND_TO_UPDATE[1],
                'data' =>  $valueHandle
            ], 400);
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_INVENTORY,
            "Cập nhật sản phẩm tồn kho: " . $productExist->name,
            $productExist->id,
            $productExist->name
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $valueHandle
        ], 200);
    }

    /**
     * Cấu hình kho cho sản phẩm
     * @urlParam  store_code required Store code
     * @bodyParam product_id int required ID sản phẩm
     * @bodyParam distribute_name Tên phân loại  (có thể để trống thì vào mặc định)
     * @bodyParam element_distribute_name giá trị phân loại (có thể để trống thì vào mặc định)
     * @bodyParam sub_element_distribute_name Giá trị thuộc tính con của phân loại (có thể để trống thì vào mặc định)
     * @bodyParam cost_of_capital double giá vốn 
     * @bodyParam stock int Tồn kho hiện tại
     */
    public function updateListInventoryBalance(Request $request)
    {
        $products_inventory = $request->products_inventory;
        $product_inventory_stock = 0;
        $status_inventory_stock = 0;

        foreach ($products_inventory as $product_inventory) {
            $productExist = Product::where('id', $product_inventory['product_id'])
                ->where('store_id', $request->store->id)
                ->first();

            if ($productExist == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                    'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
                ], 404);
            }

            $product_id = $product_inventory['product_id'];
            $distribute_name = $product_inventory['distribute_name'];
            $element_distribute_name = $product_inventory['element_distribute_name'];
            $sub_element_distribute_name = $product_inventory['sub_element_distribute_name'];

            $status_stock =  ProductUtils::get_id_distribute_and_stock(
                $request->store->id,
                $request->branch->id,
                $product_id,
                $distribute_name,
                $element_distribute_name,
                $sub_element_distribute_name
            );

            if ($status_stock == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[0],
                    'msg' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[1],
                ], 400);
            }

            $product_inventory_stock += $product_inventory['stock'];
            $status_inventory_stock += $status_stock['stock'];
        }

        $tallySheetCreate = TallySheet::create([
            "branch_id" => $request->branch->id,
            "store_id" =>  $request->store->id,
            'code' => Helper::getRandomTallySheetString(),
            'status' => InventoryUtils::STATUS_INVENTORY_BALANCE,
            'note' => $request->note,
            'existing_branch' => $status_inventory_stock,
            'reality_exist' => $product_inventory_stock,
            'deviant' => $product_inventory_stock - $status_inventory_stock,
            'user_id' => $request->user != null ? $request->user->id : null,
            'staff_id' =>  $request->staff != null ? $request->staff->id : null,
        ]);

        foreach ($products_inventory as $product_inventory) {
            $productExist = Product::where('id', $product_inventory['product_id'])
                ->where('store_id', $request->store->id)
                ->first();

            $product_id = $product_inventory['product_id'];
            $distribute_name = $product_inventory['distribute_name'];
            $element_distribute_name = $product_inventory['element_distribute_name'];
            $sub_element_distribute_name = $product_inventory['sub_element_distribute_name'];

            $status_stock =  ProductUtils::get_id_distribute_and_stock(
                $request->store->id,
                $request->branch->id,
                $product_id,
                $distribute_name,
                $element_distribute_name,
                $sub_element_distribute_name
            );

            if ($status_stock['type'] == ProductUtils::HAS_SUB) {
                TallySheetItems::create([
                    "tally_sheet_id" => $tallySheetCreate->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id'] ?? null,
                    'sub_element_distribute_id' => $status_stock['sub_element_distribute_id'] ?? null,
                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,
                    "sub_element_distribute_name" =>   $sub_element_distribute_name,
                    "existing_branch"  => $status_stock['stock'],
                    "reality_exist" =>  $product_inventory['stock'],
                    "deviant" =>   $product_inventory['stock'] - $status_stock['stock'],
                ]);
            }

            if ($status_stock['type'] == ProductUtils::HAS_ELE) {
                TallySheetItems::create([
                    "tally_sheet_id" => $tallySheetCreate->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id'],

                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,

                    "existing_branch"  => $status_stock['stock'],
                    "reality_exist" =>  $product_inventory['stock'],
                    "deviant" =>   $product_inventory['stock'] - $status_stock['stock'],
                ]);
            }

            if ($status_stock['type'] == ProductUtils::NO_ELE_SUB) {
                TallySheetItems::create([
                    "tally_sheet_id" => $tallySheetCreate->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    "existing_branch"  => $status_stock['stock'],
                    "reality_exist" =>  $product_inventory['stock'],
                    "deviant" =>   $product_inventory['stock'] - $status_stock['stock'],
                ]);
            }

            $valueHandle = InventoryUtils::update_cost_of_capital_or_stock_by_id(
                $request->store->id,
                $request->branch->id,
                $productExist->id,
                $status_stock['element_distribute_id'] ?? null,
                $status_stock['sub_element_distribute_id'] ?? null,
                $product_inventory['cost_of_capital'],
                $product_inventory['stock'],
                null,
                $tallySheetCreate->id,
                $tallySheetCreate->code
            );

            if ($valueHandle == false) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::VERSION_NOT_FOUND_TO_UPDATE[0],
                    'msg' => MsgCode::VERSION_NOT_FOUND_TO_UPDATE[1],
                    'data' =>  $valueHandle
                ], 400);
            }
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_INVENTORY,
            "Cập nhật " . count($products_inventory) . " sản phẩm tồn kho",
            $tallySheetCreate->id,
            $tallySheetCreate->code
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
