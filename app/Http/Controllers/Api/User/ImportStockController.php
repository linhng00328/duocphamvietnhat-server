<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\ProductUtils;
use App\Helper\RevenueExpenditureUtils;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\StringUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\HistoryPayImportStock;
use App\Models\ImportStock;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\ImportStockItem;
use App\Models\ImportTimeHistory;
use App\Models\RevenueExpenditure;
use App\Models\Store;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @group  User/Nhập kho
 */

class ImportStockController extends Controller
{


    /**
     * Tạo phiếu nhập hàng
     * 
     * payment_status  Trạng tháng thanh toán (0 chưa thanh toán, 1 thanh toán 1 phần, 2 đã thanh toán)
     * 
     * @urlParam  store_code required Store code
     * @bodyParam note String ghi chú
     * @bodyParam import_stock_items List danh sách nhập hàng [ {reality_exist:1, product_id,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
     * @bodyParam supplier_id int id nhà cung cấp
     * @bodyParam tax double thuế
     * @bodyParam cost double chi phí
     * @bodyParam vat double thuế
     * @bodyParam total_payment double tổng tiền thanh toán
     * @bodyParam discount double chi phí
     * total_amount giá tính từ tất cả sản phâm
     */
    public function create(Request $request)
    {
        $import_stock_items = $request->import_stock_items;

        if ($import_stock_items == null || !is_array($import_stock_items) || count($import_stock_items) == 0) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VERSION_NOT_FOUND_IMPORT_STOCK[0],
                'msg' => MsgCode::VERSION_NOT_FOUND_IMPORT_STOCK[1],
            ], 400);
        }


        $supplier_id = $request->supplier_id;

        $supplierExists = Supplier::where('store_id', $request->store->id)
            ->where('id', $supplier_id)
            ->first();

        if ($supplierExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_SUPPLIER_EXISTS[0],
                'msg' => MsgCode::NO_SUPPLIER_EXISTS[1],
            ], 400);
        }


        foreach ($import_stock_items  as $import_stock_item) {
            $product_id = $import_stock_item['product_id'];

            $distribute_name = $import_stock_item['distribute_name'] ?? "";
            $element_distribute_name = $import_stock_item['element_distribute_name'] ?? "";
            $sub_element_distribute_name = $import_stock_item['sub_element_distribute_name'] ?? "";

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

        $importStockCreate = ImportStock::create([
            "branch_id" => $request->branch->id,
            "store_id" =>  $request->store->id,
            'supplier_id' => $request->supplier_id,
            'code' => Helper::getRandomImportStockString(),
            // 'status' => InventoryUtils::STATUS_INVENTORY_BALANCE,
            "existing_branch" => 0,
            "status" => $request->status !== null ? $request->status : InventoryUtils::STATUS_IMPORT_STOCK_ORDER,
            "discount" => $request->discount,
            "total_final" => 0,
            "total_number" => 0,
            "total_amount" => 0,
            "tax" => $request->tax,
            "cost" => $request->cost,
            "vat" => $request->vat,
            "total_payment" => $request->total_payment,
            'note' => $request->note,
            'user_id' => $request->user != null ? $request->user->id : null,
            'staff_id' =>  $request->staff != null ? $request->staff->id : null,
            'payment_method' => $request->payment_method !== null ? $request->payment_method : RevenueExpenditureUtils::PAYMENT_TYPE_CASH
        ]);
        $this->addHistoryImportStock($request, $request->status, $importStockCreate->id);


        $total_final = 0;
        $total_amount = 0;

        foreach ($import_stock_items  as $import_stock_item) {
            $product_id = $import_stock_item['product_id'];
            $distribute_name = $import_stock_item['distribute_name'] ?? "";
            $element_distribute_name = $import_stock_item['element_distribute_name'] ?? "";
            $sub_element_distribute_name = $import_stock_item['sub_element_distribute_name'] ?? "";

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


            $quantity = $import_stock_item["quantity"] ?? 0;
            $import_price = $import_stock_item["import_price"] ?? 0;
            $total_amount =  $total_amount +  ($import_price * $quantity);


            if ($status_stock['type'] == ProductUtils::HAS_SUB) {
                ImportStockItem::create([
                    "import_stock_id" => $importStockCreate->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id']  ?? null,
                    'sub_element_distribute_id' => $status_stock['sub_element_distribute_id']  ?? null,
                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,
                    "sub_element_distribute_name" =>   $sub_element_distribute_name,
                    "existing_branch"  => $status_stock['stock'] ?? 0,
                    "import_price"  => $import_price,
                    "discount"  => $import_stock_item["discount"] ?? 0,
                    "tax_percent" =>  $import_stock_item["tax_percent"] ?? 0,
                    "quantity" => $quantity,

                ]);
            }
            if ($status_stock['type'] == ProductUtils::HAS_ELE) {
                ImportStockItem::create([
                    "import_stock_id" => $importStockCreate->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id']  ?? null,

                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,

                    "existing_branch"  => $status_stock['stock'] ?? 0,
                    "import_price"  => $import_price,
                    "discount"  => $import_stock_item["discount"] ?? 0,
                    "tax_percent" =>  $import_stock_item["tax_percent"] ?? 0,
                    "quantity" => $quantity,
                ]);
            }
            if ($status_stock['type'] == ProductUtils::NO_ELE_SUB) {
                ImportStockItem::create([
                    "import_stock_id" => $importStockCreate->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    "existing_branch"  => $status_stock['stock'] ?? 0,
                    "import_price"  => $import_price,
                    "discount"  => $import_stock_item["discount"] ?? 0,
                    "tax_percent" =>  $import_stock_item["tax_percent"] ?? 0,
                    "quantity" => $quantity,
                ]);
            }
        }

        $total_final  =  $total_amount  + doubleval($request->cost) + doubleval($request->vat) - doubleval($request->discount);
        $importStockCreate->update([
            'total_final' => $total_final,
            'remaining_amount' => $total_final - doubleval($request->total_payment),
            'total_amount' => $total_amount,
            'cost' => $request->cost,
            'vat' => $request->vat,
            'total_payment' => $request->total_payment,
            'discount' => $request->discount,
            'tax' => $request->tax,

        ]);

        if ($request->status == InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED) {

            if (doubleval($request->total_payment) > 0 && doubleval($request->total_payment) > $total_final) {
                // HistoryPayImportStock::create([
                //     "store_id" => $request->store->id,
                //     "branch_id" => $request->branch->id,
                //     "import_stock_id" => $importStockCreate->id,
                //     "payment_method" => $request->payment_method !== null ? $request->payment_method : RevenueExpenditureUtils::PAYMENT_TYPE_CASH,
                //     "money" => $request->total_payment,
                //     "remaining_amount" => $importStockCreate->remaining_amount - doubleval($request->total_payment)
                // ]);
                
                RevenueExpenditureUtils::add_new_revenue_expenditure(
                    $request,
                    RevenueExpenditureUtils::TYPE_PRODUCTION_COST,
                    RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
                    $importStockCreate->supplier_id,
                    null,
                    null,
                    $importStockCreate->code,
                    null,
                    RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_EXPENDITURE,
                    doubleval($request->total_payment) - $total_final,
                    false,
                    "Tạo phiếu chi trả tiền hàng tự động",
                    $request->payment_method !== null ? $request->payment_method : RevenueExpenditureUtils::PAYMENT_TYPE_CASH
                );
            } else {

                RevenueExpenditureUtils::add_new_revenue_expenditure(
                    $request,
                    RevenueExpenditureUtils::TYPE_PRODUCTION_COST,
                    RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
                    $importStockCreate->supplier_id,
                    null,
                    null,
                    $importStockCreate->code,
                    null,
                    RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_REVENUE,
                    $importStockCreate->total_final - doubleval($request->total_payment),
                    true,
                    "Tạo phiếu thu từ nhập kho tự động",
                    $request->payment_method !== null ? $request->payment_method : RevenueExpenditureUtils::PAYMENT_TYPE_CASH
                );
            }

            $import_stock_items = ImportStockItem::where('import_stock_id', $importStockCreate->id)->get();

            foreach ($import_stock_items as   $import_stock_item) {

                $distribute_data =  InventoryUtils::get_stock_by_distribute_by_id(
                    $request->store->id,
                    $request->branch->id,
                    $import_stock_item->product_id,
                    $import_stock_item->element_distribute_id,
                    $import_stock_item->sub_element_distribute_id
                );

                $current_stock =   $distribute_data['stock'] ?? 0;
                $current_cost_of_capital = $distribute_data['cost_of_capital'] ?? 0;

                if ($current_stock +  $import_stock_item->quantity != 0) {
                    $new_cost_of_capital = (($current_stock *  $current_cost_of_capital) + ($import_stock_item->quantity * $import_stock_item->import_price)) / ($current_stock +  $import_stock_item->quantity);
                } else {
                    $new_cost_of_capital =   ($current_cost_of_capital +  $import_stock_item->import_price) / 2;
                }

                InventoryUtils::add_sub_stock_by_id(
                    $request->store->id,
                    $request->branch_id,
                    $import_stock_item->product_id,
                    $distribute_data['element_distribute_id'] ?? null,
                    $distribute_data['sub_element_distribute_id'] ?? null,
                    $import_stock_item->quantity,
                    InventoryUtils::TYPE_IMPORT_STOCK,
                    $importStockCreate->id,
                    $importStockCreate->code
                );

                InventoryUtils::update_cost_of_capital_or_stock_by_id(
                    $request->store->id,
                    $request->branch_id,
                    $import_stock_item->product_id,
                    $distribute_data['element_distribute_id'] ?? null,
                    $distribute_data['sub_element_distribute_id'] ?? null,
                    $new_cost_of_capital,
                    null,
                    InventoryUtils::TYPE_IMPORT_AUTO_CHANGE_COST_OF_CAPITAL,
                    $importStockCreate->id,
                    $importStockCreate->code
                );

                $productExist = DB::table('products')->select('id', 'store_id')->where('id', $import_stock_item->product_id)->first();
                if ($productExist != null) {
                    InventoryUtils::update_total_stock_all_branch_to_quantity_in_stock_by_id($productExist->store_id,   $productExist->id);
                }
            }
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_ADD,
            TypeAction::FUNCTION_TYPE_INVENTORY,
            "Nhập hàng " . $importStockCreate->code,
            $importStockCreate->id,
            $importStockCreate->code
        );

        return $this->oneData($request, $importStockCreate->id);
    }

    /**
     * Danh sách phiếu nhập
     * 
     * @urlParam  store_code required Store code
     * @queryParam supplier_id int id nhà cung cấp
     * @queryParam  search Mã phiếu
     * @queryParam  status_list List danh sách trạng thái VD: 0,1,2
     * 
     */
    function getAll(Request $request)
    {

        $supplier_id = request('supplier_id');

        $search = StringUtils::convert_name(request('search'));

        $status_list = request("status_list") == null ? [] : explode(',', request("status_list"));

        $importStocks = ImportStock::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->when($supplier_id != null, function ($query) use ($supplier_id) {
                $query->where('supplier_id', '=', $supplier_id);
            })
            ->when(count($status_list) > 0, function ($query) use ($status_list) {
                $query->whereIn('status', $status_list);
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
            'data' =>  $importStocks
        ], 200);
    }

    function oneData($request, $import_stock_id)
    {


        $importStock = ImportStock::where('id', $import_stock_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($importStock == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TALLY_SHEET_EXISTS[0],
                'msg' => MsgCode::NO_TALLY_SHEET_EXISTS[1],
            ], 404);
        }
        $importStock->import_stock_items = ImportStockItem::where('import_stock_id', $importStock->id)->get();

        $importStock->history_pay_import_stock = HistoryPayImportStock::where('import_stock_id', $importStock->id)->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $importStock
        ], 200);
    }
    /**
     * Thông tin phiếu nhập hàng
     * 
     * total_amount tổng số tiền nhập hàng
     * 
     * total_number số lượng sp nhập hàng
     * 
     * status int (0 đặt hàng, 1 duyệt, 2 nhập kho, 3 hoàn thành, 4 đã hủy)
     *
     * remaining_amount thanh toán còn lại
     * 
     * history_pay_import_stock danh sách lịch sử thanh toán
     * 
     * 
     * @urlParam  store_code required Store code
     * @urlParam  import_stock_id required Id phiếu nhập hàng
     */
    function getOne(Request $request)
    {
        $import_stock_id = $request->route()->parameter('import_stock_id');
        return $this->oneData($request,  $import_stock_id);
    }


    /**
     * Cập nhật phiếu nhập hàng
     * 
     * Có thể truyền 1 trong số này không bắt buộc truyền lên hết
     * 
     * refund_received_money biến này lưu số tiền đã nhận hoàn nếu đơn đã nhập thì trở thành tổng của các đơn hoàn
     * 
     * @urlParam  store_code required Store code
     * @bodyParam note String ghi chú
     * @bodyParam status int 0 đã kiểm kho, 1 đã cân bằng
     * @bodyParam import_stock_items List danh sách check kho [ {reality_exist:1, product_id,import_price,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
     * @bodyParam tax double thuế
     * @bodyParam cost double chi phí
     * @bodyParam vat double thuế
     * @bodyParam discount double chi phí
     * total_amount giá tính từ tất cả sản phâm
     * 
     * 
     *
     */
    public function updateOne(Request $request)
    {
        $import_stock_id = $request->route()->parameter('import_stock_id');

        $importStockExists = ImportStock::where('id', $import_stock_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($importStockExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_IMPORT_STOCK_EXISTS[0],
                'msg' => MsgCode::NO_IMPORT_STOCK_EXISTS[1],
            ], 404);
        }

        $supplier_id = $request->supplier_id;

        $supplierExists = Supplier::where('store_id', $request->store->id)
            ->where('id', $supplier_id)
            ->first();

        if ($supplierExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_SUPPLIER_EXISTS[0],
                'msg' => MsgCode::NO_SUPPLIER_EXISTS[1],
            ], 400);
        }

        $import_stock_items = $request->import_stock_items;

        if ($import_stock_items == null || !is_array($import_stock_items) || count($import_stock_items) == 0) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VERSION_NOT_FOUND_IMPORT_STOCK[0],
                'msg' => MsgCode::VERSION_NOT_FOUND_IMPORT_STOCK[1],
            ], 400);
        }

        foreach ($import_stock_items  as $import_stock_item) {
            $product_id = $import_stock_item['product_id'];

            $distribute_name = $import_stock_item['distribute_name'] ?? "";
            $element_distribute_name = $import_stock_item['element_distribute_name'] ?? "";
            $sub_element_distribute_name = $import_stock_item['sub_element_distribute_name'] ?? "";

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


        ImportStockItem::where('store_id', $request->store->id)->where('branch_id', $request->branch->id)
            ->where('import_stock_id', $importStockExists->id)->delete();


        $total_final = 0;
        $total_amount = 0;

        foreach ($import_stock_items  as $import_stock_item) {
            $product_id = $import_stock_item['product_id'];

            $distribute_name = $import_stock_item['distribute_name'] ?? "";
            $element_distribute_name = $import_stock_item['element_distribute_name'] ?? "";
            $sub_element_distribute_name = $import_stock_item['sub_element_distribute_name'] ?? "";

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


            $quantity = $import_stock_item["quantity"] ?? 0;
            $import_price = (int)($import_stock_item["import_price"] ?? 0);
            $total_amount =  $total_amount +  ($import_price * $quantity);

            if ($status_stock['type'] == ProductUtils::HAS_SUB) {
                ImportStockItem::create([
                    "import_stock_id" => $importStockExists->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id']  ?? null,
                    'sub_element_distribute_id' => $status_stock['sub_element_distribute_id']  ?? null,
                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,
                    "sub_element_distribute_name" =>   $sub_element_distribute_name,
                    "existing_branch"  => $status_stock['stock'] ?? 0,
                    "import_price"  => $import_stock_item["import_price"] ?? 0,
                    "discount"  => $import_stock_item["discount"] ?? 0,
                    "tax_percent" =>  $import_stock_item["tax_percent"] ?? 0,
                    "quantity" => $import_stock_item["quantity"] ?? 1
                ]);
            }
            if ($status_stock['type'] == ProductUtils::HAS_ELE) {
                ImportStockItem::create([
                    "import_stock_id" => $importStockExists->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    'element_distribute_id' => $status_stock['element_distribute_id'],

                    "distribute_name" =>     $distribute_name,
                    "element_distribute_name" =>  $element_distribute_name,

                    "existing_branch"  => $status_stock['stock'] ?? 0,
                    "import_price"  => $import_stock_item["import_price"] ?? 0,
                    "discount"  => $import_stock_item["discount"] ?? 0,
                    "tax_percent" =>  $import_stock_item["tax_percent"] ?? 0,
                    "quantity" => $import_stock_item["quantity"] ?? 1,
                ]);
            }
            if ($status_stock['type'] == ProductUtils::NO_ELE_SUB) {
                ImportStockItem::create([
                    "import_stock_id" => $importStockExists->id,
                    "branch_id" => $request->branch->id,
                    "store_id" =>  $request->store->id,
                    'product_id' =>   $product_id,
                    "existing_branch"  => $status_stock['stock'] ?? 0,
                    "import_price"  => $import_stock_item["import_price"] ?? 0,
                    "discount"  => $import_stock_item["discount"] ?? 0,
                    "tax_percent" =>  $import_stock_item["tax_percent"] ?? 0,
                    "quantity" => $import_stock_item["quantity"] ?? 1,
                ]);
            }
        }

        if ($request->status == InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED) {
            if ($importStockExists->status != InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED) {
                RevenueExpenditureUtils::add_new_revenue_expenditure(
                    $request,
                    RevenueExpenditureUtils::TYPE_PRODUCTION_COST,
                    RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
                    $importStockExists->supplier_id,
                    null,
                    null,
                    $importStockExists->code,
                    null,
                    RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_REVENUE,
                    $importStockExists->total_final,
                    true,
                    "Tạo phiếu thu từ nhập kho tự động",
                    $importStockExists->payment_method
                );

                $import_stock_items = ImportStockItem::where('import_stock_id', $importStockExists->id)->get();

                foreach ($import_stock_items as   $import_stock_item) {

                    $distribute_data =  InventoryUtils::get_stock_by_distribute_by_id(
                        $request->store->id,
                        $request->branch->id,
                        $import_stock_item->product_id,
                        $import_stock_item->element_distribute_id,
                        $import_stock_item->sub_element_distribute_id
                    );

                    $current_stock =   $distribute_data['stock'] ?? 0;
                    $current_cost_of_capital = $distribute_data['cost_of_capital'] ?? 0;

                    if ($current_stock +  $import_stock_item->quantity != 0) {
                        $new_cost_of_capital = (($current_stock *  $current_cost_of_capital) + ($import_stock_item->quantity * $import_stock_item->import_price)) / ($current_stock +  $import_stock_item->quantity);
                    } else {
                        $new_cost_of_capital =   ($current_cost_of_capital +  $import_stock_item->import_price) / 2;
                    }

                    InventoryUtils::add_sub_stock_by_id(
                        $request->store->id,
                        $request->branch_id,
                        $import_stock_item->product_id,
                        $distribute_data['element_distribute_id'] ?? null,
                        $distribute_data['sub_element_distribute_id'] ?? null,
                        $import_stock_item->quantity,
                        InventoryUtils::TYPE_IMPORT_STOCK,
                        $importStockExists->id,
                        $importStockExists->code
                    );

                    InventoryUtils::update_cost_of_capital_or_stock_by_id(
                        $request->store->id,
                        $request->branch_id,
                        $import_stock_item->product_id,
                        $distribute_data['element_distribute_id'] ?? null,
                        $distribute_data['sub_element_distribute_id'] ?? null,
                        $new_cost_of_capital,
                        null,
                        InventoryUtils::TYPE_IMPORT_AUTO_CHANGE_COST_OF_CAPITAL,
                        $importStockExists->id,
                        $importStockExists->code
                    );

                    $productExist = DB::table('products')->select('id', 'store_id')->where('id', $import_stock_item->product_id)->first();
                    if ($productExist != null) {
                        InventoryUtils::update_total_stock_all_branch_to_quantity_in_stock_by_id($productExist->store_id,   $productExist->id);
                    }
                }
            }

            if (doubleval($request->total_payment) > 0) {
                $revenueExpenditureExists = RevenueExpenditure::where('store_id', $request->store->id)
                    ->where('branch_id', $request->branch->id)
                    ->where('recipient_group', RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER)
                    ->where('recipient_references_id', $importStockExists->supplier_id)
                    ->where('references_value', $importStockExists->code)
                    ->where('is_revenue', false)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($revenueExpenditureExists != null) {
                    $current_money = 0;
                    $debt = 0;
                    $supplier = Supplier::where('id', $importStockExists->supplier_id)->first();
                    if ($supplier) {
                        $current_money = $supplier->debt;
                        $debt =  $current_money + $importStockExists->total_payment - $request->total_payment;
                        $supplier->update([
                            "debt"  => $debt
                        ]);
                    }

                    $revenueExpenditureExists->update([
                        'change_money' => round($request->total_payment),
                        'current_money' => $debt
                    ]);
                } else {
                    RevenueExpenditureUtils::add_new_revenue_expenditure(
                        $request,
                        RevenueExpenditureUtils::TYPE_PRODUCTION_COST,
                        RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
                        $importStockExists->supplier_id,
                        null,
                        null,
                        $importStockExists->code,
                        null,
                        RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_EXPENDITURE,
                        $request->total_payment,
                        false,
                        "Tạo phiếu chi trả tiền hàng tự động",
                        $importStockExists->payment_method
                    );
                }
            }
        }

        $total_final  =  $total_amount  + doubleval($request->cost) + doubleval($request->vat) - doubleval($request->discount);
        $importStockExists->update([
            'total_final' => (int) $total_final,
            'remaining_amount' => (int)$total_final - doubleval($request->total_payment),
            'total_amount' => (int)$total_amount,
            'note' => $request->note,
            'cost' => $request->cost,
            'vat' => $request->vat,
            'total_payment' => $request->total_payment > 0 ? $request->total_payment : $importStockExists->total_payment,
            'discount' => $request->discount,
            'tax' => $request->tax,
            'status' => $request->status ? $request->status : $importStockExists->status
        ]);

        return $this->oneData($request,  $importStockExists->id);
    }


    /**
     * Hoàn trả phiếu nhập hàng
     * 
     * refund_received_money biến này lưu số tiền đã nhận hoàn nếu đơn đã nhập thì trở thành tổng của các đơn hoàn
     * 
     * 
     * @urlParam  store_code required Store code
     * @bodyParam refund_line_items List danh sách trả hàng [ {  "line_item_id":25, "quantity":1 } ]
     * @bodyParam refund_money_paid  Model Số liệu tiền hoàn trả của NCC (ko trả tiền truyền null) {amount_money, payment_method}
     * 
     */
    public function refund(Request $request)
    {
        $import_stock_id = $request->route()->parameter('import_stock_id');

        $importStockExists = ImportStock::where('id', $import_stock_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($importStockExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_IMPORT_STOCK_EXISTS[0],
                'msg' => MsgCode::NO_IMPORT_STOCK_EXISTS[1],
            ], 404);
        }
        if (
            $importStockExists->status !=  InventoryUtils::STATUS_IMPORT_STOCK_WAREHOUSE
            &&
            $importStockExists->status !=  InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_RESTOCKING_IS_NON_REFUNDABLE[0],
                'msg' => MsgCode::NO_RESTOCKING_IS_NON_REFUNDABLE[1],
            ], 400);
        }

        $import_stock_items = ImportStockItem::where('import_stock_id', $importStockExists->id)->get();


        //Lấy 2 danh sách để xử lý
        $arr_id_line_items =  $import_stock_items->pluck('id')->toArray();
        foreach ($import_stock_items->toArray() as $item) {

            $arr_id_with_import_price[$item['id']] = $item['import_price'];
            $arr_id_with_quantity[$item['id']] = $item['quantity'];
            $arr_id_with_total_refund[$item['id']] = $item['total_refund'];
            $arr_id_with_product_id[$item['id']] = $item['product']['id'];
            $arr_id_with_affter_refund[$item['id']] =  $item['quantity'] - $item['total_refund'];
        }

        $has_refund_money_paid = false;

        if (!is_null($request->refund_money_paid) && isset($request->refund_money_paid['amount_money']) && isset($request->refund_money_paid['payment_method'])) {
            $has_refund_money_paid  = true;
        }

        $refund_line_items = [];
        foreach ($import_stock_items as $import_stock_item) {
            array_push($refund_line_items, [
                'line_item_id' => $import_stock_item->id,
                'quantity' => $import_stock_item->quantity,
            ]);
        }

        $request->merge([
            'refund_line_items' => $refund_line_items
        ]);

        if ($has_refund_money_paid  == false && ($request->refund_line_items == null || count($request->refund_line_items) == 0)) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_LINE_ITEMS_REFUND[0],
                'msg' => MsgCode::NO_LINE_ITEMS_REFUND[1],
            ], 400);
        }




        if ($has_refund_money_paid  == true) {
            $paid  = ($importStockExists->total_final - $importStockExists->remaining_amount);
            $refund_received_money = $importStockExists->refund_received_money;

            if ($request->refund_money_paid['amount_money'] > ($paid - $refund_received_money)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PAYMENT_AMOUNT_CANNOT_GREATER_THAN_AMOUNT_PAID[0],
                    'msg' => MsgCode::PAYMENT_AMOUNT_CANNOT_GREATER_THAN_AMOUNT_PAID[1],
                ], 400);
            }
        }

        $total_amount = 0;

        if ($request->refund_line_items != null && count($request->refund_line_items) > 0) {
            foreach ($request->refund_line_items  as $line_item_refund) {

                if (!in_array($line_item_refund['line_item_id'], $arr_id_line_items)) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_LINE_ITEMS_REFUND[0],
                        'msg' => MsgCode::NO_LINE_ITEMS_REFUND[1],
                    ], 400);
                }

                $arr_id_with_affter_refund[$line_item_refund['line_item_id']]
                    = $arr_id_with_affter_refund[$line_item_refund['line_item_id']]  - $line_item_refund['quantity'];

                $quantity_refund = $arr_id_with_quantity[$line_item_refund['line_item_id']] +
                    -
                    ($line_item_refund['quantity'] + $arr_id_with_total_refund[$line_item_refund['line_item_id']]);

                $total_amount += ($line_item_refund['quantity'] *  $arr_id_with_import_price[$line_item_refund['line_item_id']]);

                if ($quantity_refund  < 0) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::REFUND_AMOUNT_CANNOT_GREATER_THAN_CURRENT_QUANTITY[0],
                        'msg' => MsgCode::REFUND_AMOUNT_CANNOT_GREATER_THAN_CURRENT_QUANTITY[1],
                    ], 400);
                }
            }
        }

        $is_refund_all = true;
        foreach ($import_stock_items->toArray() as $item) {
            if ($arr_id_with_affter_refund[$item['id']] > 0) {
                $is_refund_all = false;
            };
        }


        $total_final = $total_amount;
        // $discount = ($total_amount *
        //     ($importStockExists->discount / $importStockExists->total_amount)) || 0;
        $discount = ($importStockExists->total_amount != 0) ? 
            ($total_amount * ($importStockExists->discount / $importStockExists->total_amount)) : 
            0;

        /// kiểm tra chiết khấu
        if ($importStockExists->discount > 0) {
            $total_final  = $total_amount -  $discount;
        }
        //Kiểm tra thuế vat
        if ($importStockExists->vat > 0) {
            $total_final  = $total_final +  $importStockExists->vat;
        }

        $order_code = Helper::getRandomImportStockString();
        $orderNew = $importStockExists->replicate();

        $orderNew->code = $order_code;
        $orderNew->import_stock_code_refund = $importStockExists->code;
        $orderNew->import_stock_id_refund = $importStockExists->id;
        $orderNew->remaining_amount = 0;
        $orderNew->total_amount = $total_amount;
        $orderNew->total_final = $total_final;
        $orderNew->discount = $discount;

        $orderNew->save();

        $total_item_money_refund = 0;
        //Xu ly
        if ($request->refund_line_items != null && count($request->refund_line_items) > 0) {
            //Xử lý lại
            foreach ($request->refund_line_items  as $line_item_refund) {

                $line_item_id = $line_item_refund['line_item_id'];

                $quantity_new =  $line_item_refund['quantity'];

                $lineItemBefore = ImportStockItem::where('id', $line_item_id)->first();
                $lineItemBefore->update([
                    "total_refund" => $lineItemBefore->total_refund +  $quantity_new
                ]);
                $total_item_money_refund +=  ($lineItemBefore->import_price * $quantity_new);

                InventoryUtils::add_sub_stock_by_id(
                    $request->store->id,
                    $request->branch_id,
                    $lineItemBefore->product_id,
                    $lineItemBefore->element_distribute_id,
                    $lineItemBefore->sub_element_distribute_id,
                    -$quantity_new,
                    InventoryUtils::TYPE_REFUND_IMPORT_STOCK,
                    $importStockExists->id,
                    $importStockExists->code
                );

                $itemLineNew = $lineItemBefore->replicate();
                $itemLineNew->import_stock_id = $orderNew->id;
                $itemLineNew->quantity = $quantity_new;
                $itemLineNew->save();
            }
        }




        if ($has_refund_money_paid  == true) {

            $amount_money = $request->refund_money_paid['amount_money'];
            $refund_received_money_current = $importStockExists->refund_received_money;

            $orderNew->update(
                [
                    "refund_received_money" =>  $amount_money,
                    "payment_method" => $request->refund_money_paid['payment_method']
                ]
            );

            $importStockExists->update([
                "refund_received_money" =>   $refund_received_money_current +  $amount_money
            ]);
        }


        $total_refund_in_time = $orderNew->total_final;

        if ($importStockExists->has_refunded == true) {
            $orderNew->update([
                'cost' => 0,
            ]);
        } else {
            $importStockExists->update([
                "has_refunded" => true
            ]);

            $total_refund_in_time = $orderNew->total_final + $importStockExists->cost;
        }

        $orderNew->update(
            [
                'total_final' =>  $total_refund_in_time,
                "status" => InventoryUtils::STATUS_IMPORT_STOCK_REFUND
            ]
        );

        //thanh toán đơn cũ
        //0 chưa thanh toán, 1 thanh toán 1 phần, 2 đã thanh toán
        $paid = 0;
        $payment_status = 0;

        if ($total_refund_in_time >= $importStockExists->remaining_amount) {
            $paid = $total_refund_in_time;
            $payment_status = 2;
        } else if ($total_refund_in_time < $importStockExists->remaining_amount) {
            $paid = $total_refund_in_time;
            $payment_status = 1;
        }



        if ($total_refund_in_time > 0) {
            if ($importStockExists->total_payment > 0) {
                $total_refund_in_time = $total_refund_in_time - $importStockExists->total_payment;
            }

            RevenueExpenditureUtils::add_new_revenue_expenditure(
                $request,
                RevenueExpenditureUtils::TYPE_PRODUCTION_COST,
                RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
                $importStockExists->supplier_id,
                null,
                null,
                $importStockExists->code,
                null,
                RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_REFUND_EXPENDITURE,
                $total_refund_in_time,
                false,
                "Tạo phiếu chi trả hàng đã nhập kho tự động",
                $importStockExists->payment_method
            );

            // HistoryPayImportStock::create([
            //     "store_id" => $request->store->id,
            //     "branch_id" => $request->branch->id,
            //     "import_stock_id" => $importStockExists->id,
            //     "payment_method" => $request->payment_method,
            //     "money" => $paid,
            //     "remaining_amount" => $importStockExists->remaining_amount - $paid
            // ]);

            // RevenueExpenditureUtils::add_new_revenue_expenditure(
            //     $request,
            //     RevenueExpenditureUtils::TYPE_PRODUCTION_COST,
            //     RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
            //     $importStockExists->supplier_id,
            //     null,
            //     null,
            //     $importStockExists->code,
            //     null,
            //     RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_EXPENDITURE,
            //     $total_refund_in_time,
            //     false,
            //     "Tạo phiếu chi trả tiền hàng tự động",
            //     RevenueExpenditureUtils::PAYMENT_TYPE_CASH
            // );
        }

        $importStockExists->update([
            'payment_status' => $payment_status,
            'remaining_amount' =>  $importStockExists->remaining_amount - $paid,
        ]);

        if ($is_refund_all == true) {
            $importStockExists->update([
                'status' => InventoryUtils::STATUS_IMPORT_STOCK_REFUNDED, //da hoan het
            ]);
        }
        // // // // // // // // // // // // // // //


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Thay đổi trạng thái
     * 
     * @bodyParam status int (0 đặt hàng, 1 duyệt, 2 nhập kho, 3 hoàn thành, 4 đã hủy)
     * 
     */
    public function updateStatusImportStock(Request $request)
    {
        $import_stock_id = $request->route()->parameter('import_stock_id');

        $importStockExists = ImportStock::where('id', $import_stock_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($importStockExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_IMPORT_STOCK_EXISTS[0],
                'msg' => MsgCode::NO_IMPORT_STOCK_EXISTS[1],
            ], 404);
        }

        $list_status = [
            0, 1, 2, 3, 4, 5, 6, 7
        ];

        if (!in_array($request->status, $list_status)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_EXISTS[1],
            ], 400);
        }

        if ($request->status <= $importStockExists->status) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CANNOT_CHANGE_OLD_STATUS[0],
                'msg' => MsgCode::CANNOT_CHANGE_OLD_STATUS[1],
            ], 400);
        }

        // if ($request->status == InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED && $importStockExists->remaining_amount > 0) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::NOT_FULLY_PAID_CAN_NOT_BE_COMPLETED[0],
        //         'msg' => MsgCode::NOT_FULLY_PAID_CAN_NOT_BE_COMPLETED[1],
        //     ], 400);
        // }


        $this->addHistoryImportStock($request, $request->status, $importStockExists->id);

        $importStockExists->update([
            'status' => $request->status,
        ]);


        //Nhập vào kho và xử lý lại giá vốn
        if ($request->status == InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED) {
            RevenueExpenditureUtils::add_new_revenue_expenditure(
                $request,
                RevenueExpenditureUtils::TYPE_PRODUCTION_COST,
                RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
                $importStockExists->supplier_id,
                null,
                null,
                $importStockExists->code,
                null,
                RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_REVENUE,
                $importStockExists->total_final,
                true,
                "Tạo phiếu thu từ nhập kho tự động",
                $importStockExists->payment_method
            );

            $import_stock_items = ImportStockItem::where('import_stock_id', $importStockExists->id)->get();

            foreach ($import_stock_items as   $import_stock_item) {

                $distribute_data =  InventoryUtils::get_stock_by_distribute_by_id(
                    $request->store->id,
                    $request->branch->id,
                    $import_stock_item->product_id,
                    $import_stock_item->element_distribute_id,
                    $import_stock_item->sub_element_distribute_id
                );

                $current_stock =   $distribute_data['stock'] ?? 0;
                $current_cost_of_capital = $distribute_data['cost_of_capital'] ?? 0;

                if ($current_stock +  $import_stock_item->quantity != 0) {
                    $new_cost_of_capital = (($current_stock *  $current_cost_of_capital) + ($import_stock_item->quantity * $import_stock_item->import_price)) / ($current_stock +  $import_stock_item->quantity);
                } else {
                    $new_cost_of_capital =   ($current_cost_of_capital +  $import_stock_item->import_price) / 2;
                }


                InventoryUtils::add_sub_stock_by_id(
                    $request->store->id,
                    $request->branch_id,
                    $import_stock_item->product_id,
                    $distribute_data['element_distribute_id'] ?? null,
                    $distribute_data['sub_element_distribute_id'] ?? null,
                    $import_stock_item->quantity,
                    InventoryUtils::TYPE_IMPORT_STOCK,
                    $importStockExists->id,
                    $importStockExists->code
                );

                InventoryUtils::update_cost_of_capital_or_stock_by_id(
                    $request->store->id,
                    $request->branch_id,
                    $import_stock_item->product_id,
                    $distribute_data['element_distribute_id'] ?? null,
                    $distribute_data['sub_element_distribute_id'] ?? null,
                    $new_cost_of_capital,
                    null,
                    InventoryUtils::TYPE_IMPORT_AUTO_CHANGE_COST_OF_CAPITAL,
                    $importStockExists->id,
                    $importStockExists->code
                );

                $productExist = DB::table('products')->select('id', 'store_id')->where('id', $import_stock_item->product_id)->first();
                if ($productExist != null) {
                    InventoryUtils::update_total_stock_all_branch_to_quantity_in_stock_by_id($productExist->store_id,   $productExist->id);
                }
            }

            if (doubleval($importStockExists->total_payment) > 0) {
                RevenueExpenditureUtils::add_new_revenue_expenditure(
                    $request,
                    RevenueExpenditureUtils::TYPE_PRODUCTION_COST,
                    RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
                    $importStockExists->supplier_id,
                    null,
                    null,
                    $importStockExists->code,
                    null,
                    RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_EXPENDITURE,
                    $importStockExists->total_payment,
                    false,
                    "Tạo phiếu chi trả tiền hàng tự động",
                    $importStockExists->payment_method
                );
            }
        }

        if ($request->status == InventoryUtils::STATUS_IMPORT_STOCK_CANCELED) {
            SaveOperationHistoryUtils::save(
                $request,
                TypeAction::OPERATION_ACTION_CANCEL,
                TypeAction::FUNCTION_TYPE_INVENTORY,
                "Hủy hủy phiếu nhập kho " . $importStockExists->code,
                $importStockExists->id,
                $importStockExists->code
            );
        }

        return $this->oneData($request,  $importStockExists->id);
    }

    /**
     * Thanh toán đơn nhập hàng
     * 
     *  @bodyParam amount_money double số tiền thanh toán  (truyền lên đầy đủ sẽ tự động thanh toán, truyền 1 phần tự động chuyển thanh trạng thái thanh toán 1 phần, truyền 0 chưa thanh toán)
     *  @bodyParam payment_method int phương thức thanh toán
     
     */
    public function updatePayImportStock(Request $request)
    {
        $import_stock_id = $request->route()->parameter('import_stock_id');

        $importStockExists = ImportStock::where('id', $import_stock_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($importStockExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_IMPORT_STOCK_EXISTS[0],
                'msg' => MsgCode::NO_IMPORT_STOCK_EXISTS[1],
            ], 404);
        }

        if ($request->amount_money == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_VALUE[0],
                'msg' => MsgCode::INVALID_VALUE[1],
            ], 400);
        }
        if ($request->amount_money > $importStockExists->remaining_amount) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PAYMENT_AMOUNT_CANNOT_BE_GREATER_THAN_THE_REMAINING_AMOUNT[0],
                'msg' => MsgCode::PAYMENT_AMOUNT_CANNOT_BE_GREATER_THAN_THE_REMAINING_AMOUNT[1],
            ], 400);
        }

        //0 chưa thanh toán, 1 thanh toán 1 phần, 2 đã thanh toán
        $paid = 0;
        $payment_status = 0;

        if ($request->amount_money >= $importStockExists->remaining_amount) {
            $paid = $request->amount_money;
            $payment_status = 2;
        } else if ($request->amount_money < $importStockExists->remaining_amount) {
            $paid = $request->amount_money;
            $payment_status = 1;
        }


        if ($payment_status == 1 || $payment_status == 2) {
            if ($request->status == InventoryUtils::STATUS_IMPORT_STOCK_WAREHOUSE) {
                RevenueExpenditureUtils::add_new_revenue_expenditure(
                    $request,
                    RevenueExpenditureUtils::TYPE_PRODUCTION_COST,
                    RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
                    $importStockExists->supplier_id,
                    $importStockExists->id,
                    null,
                    $importStockExists->code,
                    null,
                    RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_EXPENDITURE,
                    $request->amount_money,
                    true,
                    "Tạo phiếu chi trả tiền hàng nhập kho tự động",
                    RevenueExpenditureUtils::PAYMENT_TYPE_CASH
                );
            }
        }


        if ($request->amount_money > 0) {
            HistoryPayImportStock::create([
                "store_id" => $request->store->id,
                "branch_id" => $request->branch->id,
                "import_stock_id" => $importStockExists->id,
                "payment_method" => $request->payment_method,
                "money" => $paid,
                "remaining_amount" => $importStockExists->remaining_amount - $paid
            ]);

            RevenueExpenditureUtils::add_new_revenue_expenditure(
                $request,
                RevenueExpenditureUtils::TYPE_PRODUCTION_COST,
                RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
                $importStockExists->supplier_id,
                null,
                null,
                $importStockExists->code,
                null,
                RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_EXPENDITURE,
                $request->amount_money,
                false,
                "Tạo phiếu chi trả tiền hàng tự động",
                RevenueExpenditureUtils::PAYMENT_TYPE_CASH
            );
        }

        $importStockExists->update([
            'payment_status' => $payment_status,
            'remaining_amount' =>   $importStockExists->remaining_amount - $paid,
        ]);

        if ($importStockExists->remaining_amount == 0 && $importStockExists->status == InventoryUtils::STATUS_IMPORT_STOCK_WAREHOUSE) {
            $this->addHistoryImportStock($request, InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED, $importStockExists->id);

            $importStockExists->update([
                "status" => InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    static function addHistoryImportStock($request, $status, $import_stock_id)
    {

        if ($status < 3) {
            for ($i = 0; $i <= ($status ?? 0); $i++) {
                $his =     ImportTimeHistory::where('import_stock_id', $import_stock_id)
                    ->where('status', $i ?? 0)->first();

                if ($his != null) {
                    // $his->update([
                    //     "time_handle" => Helper::getTimeNowString()
                    // ]);
                } else {
                    ImportTimeHistory::create([
                        "store_id" => $request->store->id,
                        "branch_id" => $request->branch->id,
                        "import_stock_id" => $import_stock_id,
                        "status" => $i,
                        "time_handle" => Helper::getTimeNowString()
                    ]);
                }
            }
        } else {
            $his =     ImportTimeHistory::where('import_stock_id', $import_stock_id)
                ->where('status', $status ?? 0)->first();

            if ($his != null) {
                // $his->update([
                //     "time_handle" => Helper::getTimeNowString()
                // ]);
            } else {
                ImportTimeHistory::create([
                    "store_id" => $request->store->id,
                    "branch_id" => $request->branch->id,
                    "import_stock_id" => $import_stock_id,
                    "status" => $status,
                    "time_handle" => Helper::getTimeNowString()
                ]);
            }
        }
    }

    /**
     * In phiếu nhập hàng
     * 
     * @urlParam  store_code required Store code
     * @urlParam  import_stock_id required Id phiếu nhập hàng
     */
    public function printImportStock(Request $request)
    {
        $store_code = request('store_code');
        $store = Store::where('store_code', $store_code)
            ->first();
        $branch_id = request('branch_id');

        $import_stock_id = request('import_stock_id', null);
        $importStock = ImportStock::where('id', $import_stock_id)
            ->where('store_id', $store->id)
            ->where('branch_id', $branch_id)
            ->first();

        if ($importStock == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TALLY_SHEET_EXISTS[0],
                'msg' => MsgCode::NO_TALLY_SHEET_EXISTS[1],
            ], 404);
        }
        $importStock->import_stock_items = ImportStockItem::where('import_stock_id', $importStock->id)->get();

        $importStock->history_pay_import_stock = HistoryPayImportStock::where('import_stock_id', $importStock->id)->get();

        return view('prints.import_stock', [
            'import_stock' => $importStock
        ]);
    }
}
