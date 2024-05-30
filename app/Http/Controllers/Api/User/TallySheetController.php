<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\ProductUtils;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\StringUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\TallySheet;
use App\Models\TallySheetItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group  User/Phiếu kiểm kho
 */

class TallySheetController extends Controller
{



    /**
     * Tạo phiếu kiểm hàng
     * 
     *  status int 0 đã kiểm kho, 1 đã cân bằng
     * 
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required branch_id
     * @bodyParam note String ghi chú
     * @bodyParam tally_sheet_items List danh sách check kho [ {reality_exist:1, product_id,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
     */
    public function createTallySheet(Request $request)
    {

        $tally_sheet_items = $request->tally_sheet_items;

        if ($tally_sheet_items == null || !is_array($tally_sheet_items) || count($tally_sheet_items) == 0) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[0],
                'msg' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[1],
            ], 400);
        }

        foreach ($tally_sheet_items  as $tally_sheet_item) {
            $product_id = $tally_sheet_item['product_id'];

            $distribute_name = $tally_sheet_item['distribute_name'] ?? "";
            $element_distribute_name = $tally_sheet_item['element_distribute_name'] ?? "";
            $sub_element_distribute_name = $tally_sheet_item['sub_element_distribute_name'] ?? "";

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

            $status_stock2 =  ProductUtils::get_id_distribute_and_stock(
                $request->store->id,
                $request->branch->id,
                $product_id,
                $distribute_name,
                $element_distribute_name,
                $sub_element_distribute_name
            );

            $status_stock =  InventoryUtils::get_stock_by_distribute_by_id(
                $request->store->id,
                $request->branch->id,
                $product_id,
                $status_stock2['element_distribute_id'] ?? null,
                $status_stock2['sub_element_distribute_id'] ?? null
            );

            if ($status_stock == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[0],
                    'msg' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[1],
                ], 400);
            }
        }

        $tallySheetCreate = TallySheet::create([
            "branch_id" => $request->branch->id,
            "store_id" =>  $request->store->id,
            'code' => Helper::getRandomTallySheetString(),
            'status' => InventoryUtils::STATUS_INVENTORY_CHECKED,
            'note' => $request->note,
            'user_id' => $request->user != null ? $request->user->id : null,
            'staff_id' =>  $request->staff != null ? $request->staff->id : null,
        ]);


        $total_existing_branch = 0;
        $total_reality_exist = 0;

        foreach ($tally_sheet_items  as $tally_sheet_item) {
            $product_id = $tally_sheet_item['product_id'];

            $distribute_name = $tally_sheet_item['distribute_name'] ?? "";
            $element_distribute_name = $tally_sheet_item['element_distribute_name'] ?? "";
            $sub_element_distribute_name = $tally_sheet_item['sub_element_distribute_name'] ?? "";

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

            $status_stock2 =  ProductUtils::get_id_distribute_and_stock(
                $request->store->id,
                $request->branch->id,
                $product_id,
                $distribute_name,
                $element_distribute_name,
                $sub_element_distribute_name
            );

            $status_stock =  InventoryUtils::get_stock_by_distribute_by_id(
                $request->store->id,
                $request->branch->id,
                $product_id,
                $status_stock2['element_distribute_id'] ?? null,
                $status_stock2['sub_element_distribute_id'] ?? null
            );

            if ($status_stock == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[0],
                    'msg' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[1],
                ], 400);
            }

            $total_existing_branch += $status_stock['stock'];
            $total_reality_exist += $tally_sheet_item['reality_exist'];

            if ($status_stock['type'] == ProductUtils::HAS_SUB) {
                TallySheetItems::create([
                    "tally_sheet_id" => $tallySheetCreate->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id'],
                    'sub_element_distribute_id' => $status_stock['sub_element_distribute_id'],
                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,
                    "sub_element_distribute_name" =>   $sub_element_distribute_name,
                    "existing_branch"  => $status_stock['stock'],
                    "reality_exist" =>  $tally_sheet_item['reality_exist'],
                    "deviant" =>  $tally_sheet_item['reality_exist'] - $status_stock['stock'],
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
                    "reality_exist" =>  $tally_sheet_item['reality_exist'],
                    "deviant" =>  $tally_sheet_item['reality_exist'] - $status_stock['stock'],
                ]);
            }
            if ($status_stock['type'] == ProductUtils::NO_ELE_SUB) {
                TallySheetItems::create([
                    "tally_sheet_id" => $tallySheetCreate->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    "existing_branch"  => $status_stock['stock'],
                    "reality_exist" =>  $tally_sheet_item['reality_exist'],
                    "deviant" =>  $tally_sheet_item['reality_exist'] - $status_stock['stock'],
                ]);
            }
        }

        $tallySheetCreate->update([
            'existing_branch' => $total_existing_branch,
            'reality_exist' => $total_reality_exist,
            'deviant' => $total_reality_exist - $total_existing_branch,
        ]);


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_ADD,
            TypeAction::FUNCTION_TYPE_INVENTORY,
            "Tạo phiếu kiểm kho: " . $tallySheetCreate->code,
            $tallySheetCreate->id,
            $tallySheetCreate->code
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $this->oneTallySheetData($request, $tallySheetCreate->id)
        ], 200);
    }

    /**
     * Danh sách phiếu kiểm
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required branch_id
     * @queryParam  search Mã phiếu
     * @queryParam  status int trạng phiếu kiểm
     * 
     */
    function getAllTallySheet(Request $request)
    {

        $search = StringUtils::convert_name(request('search'));
        $status = $request->status;

        $tallySheets = TallySheet::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->orderBy('created_at', 'desc')
            ->when($status !== null && $status !== "", function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', '%' . $search . '%');
                })->orderBy('code', 'ASC');
            })


            ->paginate(request('limit') == null ? 20 : request('limit'));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $tallySheets
        ], 200);
    }

    function oneTallySheetData($request, $tally_sheet_id)
    {

        $tallySheet = TallySheet::where('id', $tally_sheet_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($tallySheet == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TALLY_SHEET_EXISTS[0],
                'msg' => MsgCode::NO_TALLY_SHEET_EXISTS[1],
            ], 404);
        }
        $tallySheet->tally_sheet_items = TallySheetItems::where('tally_sheet_id', $tallySheet->id)->get();
        return $tallySheet;
    }

    /**
     * Thông tin phiếu chi tiết
     * 
     *  status int 0 đã kiểm kho, 1 đã cân bằng
     * 
     * 
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required branch_id
     * @urlParam  tally_sheet_id required Id phiếu kiểm hàng
     */
    function getOneTallySheet(Request $request)
    {

        $tally_sheet_id = $request->route()->parameter('tally_sheet_id');

        $tallySheet = TallySheet::where('id', $tally_sheet_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($tallySheet == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TALLY_SHEET_EXISTS[0],
                'msg' => MsgCode::NO_TALLY_SHEET_EXISTS[1],
            ], 404);
        }
        $tallySheet->tally_sheet_items = TallySheetItems::where('tally_sheet_id', $tallySheet->id)->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $this->oneTallySheetData($request, $tally_sheet_id)
        ], 200);
    }

    /**
     * Xóa phiếu kiểm
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required branch_id
     * @urlParam  tally_sheet_id required Id phiếu kiểm hàng
     */
    function deleteOneTallySheet(Request $request)
    {

        $tally_sheet_id = $request->route()->parameter('tally_sheet_id');

        $tallySheet = TallySheet::where('id', $tally_sheet_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($tallySheet == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TALLY_SHEET_EXISTS[0],
                'msg' => MsgCode::NO_TALLY_SHEET_EXISTS[1],
            ], 404);
        }

        $id_delete =  $tallySheet->id;

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_DELETE,
            TypeAction::FUNCTION_TYPE_INVENTORY,
            "Xóa phiếu kiểm kho: " . $tallySheet->code,
            $tallySheet->id,
            $tallySheet->code
        );

        $tallySheet->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  [
                'idDeleted' =>    $id_delete
            ]
        ], 200);
    }

    /**
     * Cập nhật phiếu kiểm hàng
     * 
     * Có thể truyền 1 trong số này không bắt buộc truyền lên hết
     * 
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required branch_id
     * @bodyParam note String ghi chú
     * @bodyParam tally_sheet_items List (có thể trống) danh sách check kho [ {reality_exist:1, product_id,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
     */
    public function updateOneTallySheet(Request $request)
    {
        $tally_sheet_id = $request->route()->parameter('tally_sheet_id');

        $tallySheetExists = TallySheet::where('id', $tally_sheet_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($tallySheetExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TALLY_SHEET_EXISTS[0],
                'msg' => MsgCode::NO_TALLY_SHEET_EXISTS[1],
            ], 404);
        }

        $tally_sheet_items = $request->tally_sheet_items;

        $total_existing_branch = 0;
        $total_reality_exist = 0;

        if ($tally_sheet_items != null && is_array($tally_sheet_items) && count($tally_sheet_items) > 0) {




            foreach ($tally_sheet_items  as $tally_sheet_item) {
                $product_id = $tally_sheet_item['product_id'];

                $distribute_name = $tally_sheet_item['distribute_name'] ?? "";
                $element_distribute_name = $tally_sheet_item['element_distribute_name'] ?? "";
                $sub_element_distribute_name = $tally_sheet_item['sub_element_distribute_name'] ?? "";

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


                $status_stock2 =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    $request->branch->id,
                    $product_id,
                    $distribute_name,
                    $element_distribute_name,
                    $sub_element_distribute_name
                );

                $status_stock =  InventoryUtils::get_stock_by_distribute_by_id(
                    $request->store->id,
                    $request->branch->id,
                    $product_id,
                    $status_stock2['element_distribute_id'] ?? null,
                    $status_stock2['sub_element_distribute_id'] ?? null
                );



                if ($status_stock == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[0],
                        'msg' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[1],
                    ], 400);
                }
            }


            $tallySheetExists->update([
                'note' => $request->note,
            ]);


            TallySheetItems::where('store_id', $request->store->id)->where('branch_id', $request->branch->id)
                ->where('tally_sheet_id', $tallySheetExists->id)->delete();


            foreach ($tally_sheet_items  as $tally_sheet_item) {
                $product_id = $tally_sheet_item['product_id'];

                $distribute_name = $tally_sheet_item['distribute_name'] ?? "";
                $element_distribute_name = $tally_sheet_item['element_distribute_name'] ?? "";
                $sub_element_distribute_name = $tally_sheet_item['sub_element_distribute_name'] ?? "";

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

                $status_stock2 =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    $request->branch->id,
                    $product_id,
                    $distribute_name,
                    $element_distribute_name,
                    $sub_element_distribute_name
                );

                $status_stock =  InventoryUtils::get_stock_by_distribute_by_id(
                    $request->store->id,
                    $request->branch->id,
                    $product_id,
                    $status_stock2['element_distribute_id'] ?? null,
                    $status_stock2['sub_element_distribute_id'] ?? null
                );

                if ($status_stock == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[0],
                        'msg' => MsgCode::VERSION_NOT_FOUND_TALLY_SHEET[1],
                    ], 400);
                }

                $total_existing_branch += $status_stock['stock'];
                $total_reality_exist += $tally_sheet_item['reality_exist'];

                if ($status_stock['type'] == ProductUtils::HAS_SUB) {
                    TallySheetItems::create([
                        "tally_sheet_id" => $tallySheetExists->id,
                        "branch_id" => $request->branch->id,
                        "store_id" =>  $request->store->id,
                        'product_id' =>   $product_id,
                        'element_distribute_id' => $status_stock['element_distribute_id'],
                        'sub_element_distribute_id' => $status_stock['sub_element_distribute_id'],
                        "distribute_name" =>     $distribute_name,
                        "element_distribute_name" =>  $element_distribute_name,
                        "sub_element_distribute_name" =>   $sub_element_distribute_name,
                        "existing_branch"  => $status_stock['stock'],
                        "reality_exist" =>  $tally_sheet_item['reality_exist'],
                        "deviant" =>  $tally_sheet_item['reality_exist'] - $status_stock['stock'],
                    ]);
                }
                if ($status_stock['type'] == ProductUtils::HAS_ELE) {
                    TallySheetItems::create([
                        "tally_sheet_id" => $tallySheetExists->id,
                        "branch_id" => $request->branch->id,
                        "store_id" =>  $request->store->id,
                        'product_id' =>   $product_id,
                        'element_distribute_id' => $status_stock['element_distribute_id'],

                        "distribute_name" =>     $distribute_name,
                        "element_distribute_name" =>  $element_distribute_name,

                        "existing_branch"  => $status_stock['stock'],
                        "reality_exist" =>  $tally_sheet_item['reality_exist'],
                        "deviant" =>  $tally_sheet_item['reality_exist'] - $status_stock['stock'],
                    ]);
                }
                if ($status_stock['type'] == ProductUtils::NO_ELE_SUB) {
                    TallySheetItems::create([
                        "tally_sheet_id" => $tallySheetExists->id,
                        "branch_id" => $request->branch->id,
                        "store_id" =>  $request->store->id,
                        'product_id' =>   $product_id,
                        "existing_branch"  => $status_stock['stock'],
                        "reality_exist" =>  $tally_sheet_item['reality_exist'],
                        "deviant" =>  $tally_sheet_item['reality_exist'] - $status_stock['stock'],
                    ]);
                }
            }

            $tallySheetExists->update([
                'existing_branch' => $total_existing_branch,
                'reality_exist' => $total_reality_exist,
                'deviant' => $total_reality_exist - $total_existing_branch,
            ]);
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_INVENTORY,
            "Sửa phiếu kiểm kho: " . $tallySheetExists->code,
            $tallySheetExists->id,
            $tallySheetExists->code
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],

        ], 200);
    }

    /**
     * Cân bằng kho từ phiếu kiểm
     * 
     *  @urlParam  branch_id required branch_id
     *  @urlParam  tally_sheet_id required Id phiếu kiểm hàng
     */
    public function balanceTallySheet(Request $request)
    {
        $tally_sheet_id = $request->route()->parameter('tally_sheet_id');

        $tallySheetExists = TallySheet::where('id', $tally_sheet_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($tallySheetExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TALLY_SHEET_EXISTS[0],
                'msg' => MsgCode::NO_TALLY_SHEET_EXISTS[1],
            ], 404);
        }

        if ($tallySheetExists->status == InventoryUtils::STATUS_INVENTORY_BALANCE) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::BALANCED_VOTES[0],
                'msg' => MsgCode::BALANCED_VOTES[1],
            ], 400);
        }

        $tally_sheet_items = TallySheetItems::where('tally_sheet_id', $tallySheetExists->id)->get();

        foreach ($tally_sheet_items as   $tally_sheet_item) {

            InventoryUtils::update_cost_of_capital_or_stock_by_id(
                $request->store->id,
                $request->branch_id,
                $tally_sheet_item->product_id,
                $tally_sheet_item->element_distribute_id,
                $tally_sheet_item->sub_element_distribute_id,
                null,
                $tally_sheet_item->reality_exist,
                InventoryUtils::TYPE_TALLY_SHEET_STOCK,
                $tallySheetExists->id,
                $tallySheetExists->code
            );

            $productExist = DB::table('products')->select('id', 'store_id')->where('id', $tally_sheet_item->product_id)->first();
            if ($productExist != null) {
                InventoryUtils::update_total_stock_all_branch_to_quantity_in_stock_by_id($productExist->store_id,   $productExist->id);
            }
        }

        $tallySheetExists->update([
            'status' => InventoryUtils::STATUS_INVENTORY_BALANCE,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $this->oneTallySheetData($request, $tally_sheet_id)
        ], 200);
    }
}
