<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\ProductUtils;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\StringUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\TransferStock;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\TransferStockItem;
use Illuminate\Http\Request;


/**
 * @group  User/Chuyển kho
 */

class TransferStockController extends Controller
{


    /**
     * Tạo phiếu chuyển hàng
     * 
     *   //0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển
     * 
     * @urlParam  store_code required Store code
     * @bodyParam note String ghi chú
     * @bodyParam transfer_stock_items List danh sách chuyển hàng [ {quantity:1, product_id,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
     * @bodyParam to_branch_id int id Chi nhánh chuyển đén
     */
    public function create(Request $request)
    {

        $transfer_stock_items = $request->transfer_stock_items;

        if ($transfer_stock_items == null || !is_array($transfer_stock_items) || count($transfer_stock_items) == 0) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VERSION_NOT_FOUND_TRANSFER_STOCK[0],
                'msg' => MsgCode::VERSION_NOT_FOUND_TRANSFER_STOCK[1],
            ], 400);
        }

        $branchTo = Branch::where('store_id', $request->store->id)
            ->where('id', $request->to_branch_id)->first();

        if ($branchTo  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_BRANCH_TO[0],
                'msg' => MsgCode::NO_BRANCH_TO[1],
            ], 400);
        }

        if ($request->branch->id == $request->to_branch_id) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Chi nhánh chuyển kho đến không thể giống chi nhánh hiện tại",
            ], 400);
        }

        foreach ($transfer_stock_items  as $transfer_stock_item) {
            $product_id = $transfer_stock_item['product_id'];

            $distribute_name = $transfer_stock_item['distribute_name'] ?? "";
            $element_distribute_name = $transfer_stock_item['element_distribute_name'] ?? "";
            $sub_element_distribute_name = $transfer_stock_item['sub_element_distribute_name'] ?? "";

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
                    'msg_code' => MsgCode::VERSION_NOT_FOUND_TRANSFER_STOCK[0],
                    'msg' => MsgCode::VERSION_NOT_FOUND_TRANSFER_STOCK[1],
                ], 400);
            }
        }

        $transferStockCreate = TransferStock::create([
            "from_branch_id" => $request->branch->id,
            'to_branch_id' => $request->to_branch_id,
            "store_id" =>  $request->store->id,
            'code' => Helper::getRandomTransferStockString(),
            'status' => InventoryUtils::STATUS_TRANSFER_AWAIT,
            'note' => $request->note,
            'user_id' => $request->user != null ? $request->user->id : null,
            'staff_id' =>  $request->staff != null ? $request->staff->id : null,
        ]);


        foreach ($transfer_stock_items  as $transfer_stock_item) {
            $product_id = $transfer_stock_item['product_id'];
            $distribute_name = $transfer_stock_item['distribute_name'] ?? "";
            $element_distribute_name = $transfer_stock_item['element_distribute_name'] ?? "";
            $sub_element_distribute_name = $transfer_stock_item['sub_element_distribute_name'] ?? "";

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
                    'msg_code' => MsgCode::VERSION_NOT_FOUND_TRANSFER_STOCK[0],
                    'msg' => MsgCode::VERSION_NOT_FOUND_TRANSFER_STOCK[1],
                ], 400);
            }


            $quantity = $transfer_stock_item["quantity"] ?? 0;


            if ($status_stock['type'] == ProductUtils::HAS_SUB) {
                TransferStockItem::create([
                    "transfer_stock_id" => $transferStockCreate->id,
                    "from_branch_id" => $request->branch->id,
                    'to_branch_id' => $request->to_branch_id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id']  ?? null,
                    'sub_element_distribute_id' => $status_stock['sub_element_distribute_id']  ?? null,
                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,
                    "sub_element_distribute_name" =>   $sub_element_distribute_name,
                    "quantity" => $quantity,

                ]);
            }
            if ($status_stock['type'] == ProductUtils::HAS_ELE) {
                TransferStockItem::create([
                    "transfer_stock_id" => $transferStockCreate->id,
                    "from_branch_id" => $request->branch->id,
                    'to_branch_id' => $request->to_branch_id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id']  ?? null,

                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,
                    "quantity" => $quantity,
                ]);
            }
            if ($status_stock['type'] == ProductUtils::NO_ELE_SUB) {
                TransferStockItem::create([
                    "transfer_stock_id" => $transferStockCreate->id,
                    "from_branch_id" => $request->branch->id,
                    'to_branch_id' => $request->to_branch_id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    "quantity" => $quantity,
                ]);
            }
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_ADD,
            TypeAction::FUNCTION_TYPE_INVENTORY,
            "Tạo phiếu chuyển kho " . $transferStockCreate->code,
            $transferStockCreate->id,
            $transferStockCreate->code
        );


        return $this->oneData($request, $transferStockCreate->id);
    }

    /**
     * Danh sách phiếu chuyển kho bên chuyển 
     * 
     * 
     *   //0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển
     * 
     * @urlParam  store_code required Store code
     * @queryParam  search Mã phiếu
     * @queryParam  status  //0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển
     * 
     */
    function getAllSender(Request $request)
    {

        $search = StringUtils::convert_name(request('search'));

        $status = request("status");

        $transferStocks = TransferStock::where('store_id', $request->store->id)
            ->where('from_branch_id', $request->branch->id)
            ->when(!empty($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', '%' . $search . '%');
                })->orderBy('code', 'ASC');
            })

            ->paginate(request('limit') == null ? 20 : request('limit'));

        $custom = collect(
            [
                'total_transfered' => TransferStock::where('store_id', $request->store->id)->where('from_branch_id', $request->branch->id)->where('status', 2)->count(),
                'total_wait' => TransferStock::where('store_id', $request->store->id)->where('from_branch_id', $request->branch->id)->where('status', 0)->count(),
                'total_cancel' => TransferStock::where('store_id', $request->store->id)->where('from_branch_id', $request->branch->id)->where('status', 1)->count(),
            ]
        );

        $data =  $custom->merge($transferStocks);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $data
        ], 200);
    }

    /**
     * Danh sách phiếu chuyển kho người nhận
     * 
     *  //0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển
     * 
     * @urlParam  store_code required Store code
     * @queryParam  search Mã phiếu
     * @queryParam  status  //0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển
     * 
     */
    function getAllReceiver(Request $request)
    {

        $search = StringUtils::convert_name(request('search'));

        $status = request("status");

        $transferStocks = TransferStock::where('store_id', $request->store->id)
            ->where('to_branch_id', $request->branch->id)
            ->when(!empty($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
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
            'data' =>  $transferStocks
        ], 200);
    }

    function oneData($request, $transfer_stock_id)
    {


        $transferStock = TransferStock::where('id', $transfer_stock_id)->where('store_id', $request->store->id)
            ->first();

        if ($transferStock == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TRANSFER_EXISTS[0],
                'msg' => MsgCode::NO_TRANSFER_EXISTS[1],
            ], 404);
        }
        $transferStock->transfer_stock_items = TransferStockItem::where('transfer_stock_id', $transferStock->id)->get();


        $custom = collect(
            [
                'total_transfered' => TransferStock::where('store_id', $request->store->id)->where('to_branch_id', $request->branch->id)->where('status', 2)->count(),
                'total_wait' => TransferStock::where('store_id', $request->store->id)->where('to_branch_id', $request->branch->id)->where('status', 0)->count(),
                'total_cancel' => TransferStock::where('store_id', $request->store->id)->where('to_branch_id', $request->branch->id)->where('status', 1)->count(),
            ]
        );

        $data =    $custom->merge($transferStock);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $data
        ], 200);
    }
    /**
     * Thông tin phiếu chuyển hàng
     * 
     * @urlParam  store_code required Store code
     * @urlParam  transfer_stock_id required Id phiếu nhập hàng
     */
    function getOne(Request $request)
    {
        $transfer_stock_id = $request->route()->parameter('transfer_stock_id');
        return $this->oneData($request,  $transfer_stock_id);
    }


    /**
     * Cập nhật phiếu chuyển hàng
     * 
     *  //0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển
     * 
     * @bodyParam note String ghi chú
     * @bodyParam transfer_stock_items List danh sách chuyển hàng [ {quantity:1, product_id,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
     * @bodyParam to_branch_id int id Chi nhánh chuyển đén
     * 
     * 
     *
     */
    public function updateOne(Request $request)
    {
        $transfer_stock_id = $request->route()->parameter('transfer_stock_id');

        $transferStockExists = TransferStock::where('id', $transfer_stock_id)->where('store_id', $request->store->id)->first();

        if ($transferStockExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TRANSFER_EXISTS[0],
                'msg' => MsgCode::NO_TRANSFER_EXISTS[1],
            ], 404);
        }

        $transferStockExists->update([
            'note' => $request->note,
            'to_branch_id' => $request->to_branch_id,
        ]);

        $transfer_stock_items = $request->transfer_stock_items;

        if ($transfer_stock_items == null || !is_array($transfer_stock_items) || count($transfer_stock_items) == 0) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VERSION_NOT_FOUND_TRANSFER_STOCK[0],
                'msg' => MsgCode::VERSION_NOT_FOUND_TRANSFER_STOCK[1],
            ], 400);
        }

        foreach ($transfer_stock_items  as $transfer_stock_item) {
            $product_id = $transfer_stock_item['product_id'];

            $distribute_name = $transfer_stock_item['distribute_name'] ?? "";
            $element_distribute_name = $transfer_stock_item['element_distribute_name'] ?? "";
            $sub_element_distribute_name = $transfer_stock_item['sub_element_distribute_name'] ?? "";

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
                    'msg_code' => MsgCode::VERSION_NOT_FOUND_IMPORT_STOCK[0],
                    'msg' => MsgCode::VERSION_NOT_FOUND_IMPORT_STOCK[1],
                ], 400);
            }
        }


        TransferStockItem::where('store_id', $request->store->id)
            ->where('transfer_stock_id', $transferStockExists->id)->delete();


        foreach ($transfer_stock_items  as $transfer_stock_item) {
            $product_id = $transfer_stock_item['product_id'];

            $distribute_name = $transfer_stock_item['distribute_name'] ?? "";
            $element_distribute_name = $transfer_stock_item['element_distribute_name'] ?? "";
            $sub_element_distribute_name = $transfer_stock_item['sub_element_distribute_name'] ?? "";

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
                    'msg_code' => MsgCode::VERSION_NOT_FOUND_IMPORT_STOCK[0],
                    'msg' => MsgCode::VERSION_NOT_FOUND_IMPORT_STOCK[1],
                ], 400);
            }


            $quantity = $transfer_stock_item["quantity"] ?? 0;

            if ($status_stock['type'] == ProductUtils::HAS_SUB) {
                TransferStockItem::create([
                    "transfer_stock_id" => $transferStockExists->id,
                    "from_branch_id" => $request->branch->id,
                    'to_branch_id' => $request->to_branch_id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id']  ?? null,
                    'sub_element_distribute_id' => $status_stock['sub_element_distribute_id']  ?? null,
                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,
                    "sub_element_distribute_name" =>   $sub_element_distribute_name,
                    "quantity" => $quantity,

                ]);
            }
            if ($status_stock['type'] == ProductUtils::HAS_ELE) {
                TransferStockItem::create([
                    "transfer_stock_id" => $transferStockExists->id,
                    "from_branch_id" => $request->branch->id,
                    'to_branch_id' => $request->to_branch_id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id']  ?? null,

                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,
                    "quantity" => $quantity,
                ]);
            }
            if ($status_stock['type'] == ProductUtils::NO_ELE_SUB) {
                TransferStockItem::create([
                    "transfer_stock_id" => $transferStockExists->id,
                    "from_branch_id" => $request->branch->id,
                    'to_branch_id' => $request->to_branch_id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    "quantity" => $quantity,
                ]);
            }
        }

        return $this->oneData($request,  $transferStockExists->id);
    }


    /**
     * Xử lý phiếu nhập kho
     * 
     *  //0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển
     * 
     * @bodyParam status int (1 hủy phiếu chuyển , 2 đồng ý nhập kho (chỉ khi ở chi nhánh nhận kho))
     * 
     */
    public function updateStatus(Request $request)
    {
        $transfer_stock_id = $request->route()->parameter('transfer_stock_id');

        $transferStockExists = TransferStock::where('id', $transfer_stock_id)->where('store_id', $request->store->id)
            ->first();

        if ($transferStockExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TRANSFER_EXISTS[0],
                'msg' => MsgCode::NO_TRANSFER_EXISTS[1],
            ], 404);
        }

        if ($transferStockExists->status != InventoryUtils::STATUS_TRANSFER_AWAIT) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Phiếu chuyển này đã được xử lý từ trước",
            ], 404);
        }

        if ($request->status == InventoryUtils::STATUS_TRANSFER_CANCEL) {
            $transferStockExists->update([
                'status' => $request->status,
                'handle_time' => Helper::getTimeNowString()
            ]);

            SaveOperationHistoryUtils::save(
                $request,
                TypeAction::OPERATION_ACTION_CANCEL,
                TypeAction::FUNCTION_TYPE_INVENTORY,
                "Hủy phiếu chuyển kho " . $transferStockExists->code,
                $transferStockExists->id,
                $transferStockExists->code
            );
        }


        if ($request->status == InventoryUtils::STATUS_TRANSFER_OK) {

            if ($request->branch->id !=  $transferStockExists->to_branch_id) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Chi nhánh đích mới được chấp nhận chuyển kho",
                ], 404);
            }


            $transferStockExists->update([
                'status' => $request->status,
                'handle_time' => Helper::getTimeNowString()
            ]);
            //Nhập vào kho
            if ($request->status == InventoryUtils::STATUS_TRANSFER_OK) {

                $transfer_stock_items = TransferStockItem::where('transfer_stock_id', $transferStockExists->id)->get();

                foreach ($transfer_stock_items as   $transfer_stock_item) {

                    $distribute_data =  InventoryUtils::get_stock_by_distribute_by_id(
                        $request->store->id,
                        $request->branch->id,
                        $transfer_stock_item->product_id,
                        $transfer_stock_item->element_distribute_id,
                        $transfer_stock_item->sub_element_distribute_id
                    );

                    InventoryUtils::add_sub_stock_by_id(
                        $request->store->id,
                        $transfer_stock_item->from_branch_id,
                        $transfer_stock_item->product_id,
                        $distribute_data['element_distribute_id'] ?? null,
                        $distribute_data['sub_element_distribute_id'] ?? null,
                        -$transfer_stock_item->quantity,
                        InventoryUtils::TYPE_TRANSFER_STOCK_SENDER,
                        $transferStockExists->id,
                        $transferStockExists->code
                    );

                    InventoryUtils::add_sub_stock_by_id(
                        $request->store->id,
                        $transfer_stock_item->to_branch_id,
                        $transfer_stock_item->product_id,
                        $distribute_data['element_distribute_id'] ?? null,
                        $distribute_data['sub_element_distribute_id'] ?? null,
                        $transfer_stock_item->quantity,
                        InventoryUtils::TYPE_TRANSFER_STOCK_RECEIVER,
                        $transferStockExists->id,
                        $transferStockExists->code
                    );
                }

                SaveOperationHistoryUtils::save(
                    $request,
                    TypeAction::OPERATION_ACTION_UPDATE,
                    TypeAction::FUNCTION_TYPE_INVENTORY,
                    "Nhận chuyển kho " . $transferStockExists->code,
                    $transferStockExists->id,
                    $transferStockExists->code
                );
            }
        }




        return $this->oneData($request,  $transferStockExists->id);
    }
}
