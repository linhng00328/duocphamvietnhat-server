<?php

namespace App\Http\Controllers\Api\User;

use App\Exports\RevenueExpendituresExport;
use App\Helper\Helper;
use App\Helper\ProductUtils;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\Distribute;
use App\Models\ElementDistribute;
use App\Models\InventoryEleDis;
use App\Models\InventoryHistory;
use App\Models\InventorySubDis;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ReferralPhoneCustomer;
use App\Models\RevenueExpenditure;
use App\Models\SubElementDistribute;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @group  User/Báo cáo kho
 */
class ReportInventoryController extends Controller
{


    /**
     * Báo cáo thay đổi kho của sản phẩm
     * 
     * status": 0 hiển thị - số còn lại là ẩn
     * has_in_discount: boolean (sp có chuẩn bị và đang diễn ra trong discount)
     * has_in_combo: boolean (sp có chuẩn bị và đang diễn ra trong combo)
     * total_stoking còn hàng
     * total_out_of_stock' hết hàng
     * total_hide' ẩn
     * 
     * @urlParam  store_code required Store code
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: samsung
     * @queryParam  sort_by Sắp xếp theo VD: price
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  category_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  category_children_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  details Filter theo thuộc tính VD: Màu:Đỏ|Size:XL
     * @queryParam  status (0 -1) còn hàng hay không! không truyền lấy cả 2
     * @queryParam  filter_by Chọn trường nào để lấy
     * @queryParam  filter_option Kiểu filter ( > = <)
     * @queryParam  filter_by_value Giá trị trường đó
     * @queryParam  is_get_all boolean Lấy tất cá hay không 
     * @queryParam  limit int Số item 1 trangơ
     * @queryParam  agency_type_id int id Kiểu đại lý
     * @queryParam  is_show_description bool Cho phép trả về mô tả
     */
    public function reportImportExportStock(Request $request, $id)
    {
        $is_show_description = filter_var($request->is_show_description, FILTER_VALIDATE_BOOLEAN); //
        $is_get_all = filter_var($request->is_get_all, FILTER_VALIDATE_BOOLEAN); //

        $categoryIds = request("category_ids") == null ? [] : explode(',', request("category_ids"));
        $categoryChildrenIds = request("category_children_ids") == null ? [] : explode(',', request("category_children_ids"));
        $requestDetails = request("details") == null ? [] : explode('|', request("details"));

        $details = array();
        $distributes = array();


        foreach ($requestDetails as $requestDetail) {

            $requestDetailSplit = explode(':', $requestDetail);

            if ($requestDetailSplit[0] != null &&  $requestDetailSplit[1]) {
                $name = $requestDetailSplit[0];
                $atrribute = explode(',', $requestDetailSplit[1]);

                $details[$name] =  $atrribute;

                $distributes += $atrribute;
            }
        }
        $status = request('status') != null ? (int)request('status') : null;
        $filter_by = request('filter_by');
        $filter_option = request('filter_option');



        $search = request('search');
        $after_res = Product::sortByRelevance(true)
            ->where(
                'store_id',
                $request->store->id
            )
            ->where(
                'status',
                '<>',
                1
            )
            ->when(Product::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when(count($categoryIds) > 0, function ($query) use ($categoryIds) {
                $query->whereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                });
            })

            ->when(count(
                $categoryChildrenIds
            ) > 0, function ($query) use ($categoryChildrenIds) {
                $query->whereHas('category_children', function ($query) use ($categoryChildrenIds) {
                    $query->whereIn('category_children.id',  $categoryChildrenIds);
                });
            })
            ->when(request('sort_by') == null && (request('search') == null || request('search') == ""), function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->when(count($details) > 0, function ($query) use ($details, $distributes) {
                $query->whereHas('details', function ($query) use ($details, $distributes) {
                    $query->whereIn('product_details.name', array_keys($details))
                        ->when(count($distributes) > 0, function ($query) use ($distributes) {
                            $query->whereHas('distributes', function ($query) use ($distributes) {
                                $query->whereIn('distributes.name',  $distributes);
                            });
                        });
                });
            })->search(request('search'));

        $r =  clone $after_res;
        $res_products = $r
            ->when($status !== null, function ($query) use ($status) {
                $query->where('status', $status);
            });


        $r =  clone $after_res;
        $total_stoking = $r
            ->where('quantity_in_stock', '!=', 0)
            ->where('status', 0)->count();

        $r =  clone $after_res;
        $total_out_of_stock = $r
            ->where('quantity_in_stock', '==', 0)
            ->where('status', 0)->count();

        $r =  clone $after_res;
        $total_hide = $r
            ->where('status', '!=', 0)->count();

        //get tat ca

        if ($is_get_all == true) {
            $res_products =  $res_products->paginate(100000);
        } else {
            $res_products =  $res_products->paginate(request('limit') == null ? 20 : request('limit'));
        }


        $custom = collect(
            [
                'total_stoking' => $total_stoking,
                'total_out_of_stock' => $total_out_of_stock,
                'total_hide' => $total_hide
            ]
        );


        if ($is_show_description) {
            $productDB = DB::table('products')

                ->where(
                    'store_id',
                    $request->store->id
                )
                ->where(
                    'status',
                    '<>',
                    1
                )
                ->orderBy('id', 'desc')
                ->get();

            foreach ($res_products as $product) {


                if ($is_show_description) {
                    $des = null;
                    foreach ($productDB  as $pro) {
                        if ($pro->id == $product->id) {
                            $des = $pro->description;
                            break;
                        }
                    }
                    $product->full_description = $des;
                }
            }
        }



        $data = $custom->merge($res_products);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $data,
        ], 200);
    }


    function res_products($request)
    {
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $categoryIds = request("category_ids") == null ? [] : explode(',', request("category_ids"));
        $categoryChildrenIds = request("category_children_ids") == null ? [] : explode(',', request("category_children_ids"));
        $requestDetails = request("details") == null ? [] : explode('|', request("details"));

        $details = array();
        $distributes = array();


        foreach ($requestDetails as $requestDetail) {

            $requestDetailSplit = explode(':', $requestDetail);

            if ($requestDetailSplit[0] != null &&  $requestDetailSplit[1]) {
                $name = $requestDetailSplit[0];
                $atrribute = explode(',', $requestDetailSplit[1]);

                $details[$name] =  $atrribute;

                $distributes += $atrribute;
            }
        }
        $status = request('status') != null ? (int)request('status') : null;
        $filter_by = request('filter_by');
        $filter_option = request('filter_option');



        $search = request('search');
        $after_res = Product::sortByRelevance(true)
            ->where(
                'store_id',
                $request->store->id
            )
            ->where(
                'status',
                '<>',
                1
            )
            ->when(Product::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when(count($categoryIds) > 0, function ($query) use ($categoryIds) {
                $query->whereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                });
            })

            ->when(count(
                $categoryChildrenIds
            ) > 0, function ($query) use ($categoryChildrenIds) {
                $query->whereHas('category_children', function ($query) use ($categoryChildrenIds) {
                    $query->whereIn('category_children.id',  $categoryChildrenIds);
                });
            })
            ->when(request('sort_by') == null && (request('search') == null || request('search') == ""), function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->when(count($details) > 0, function ($query) use ($details, $distributes) {
                $query->whereHas('details', function ($query) use ($details, $distributes) {
                    $query->whereIn('product_details.name', array_keys($details))
                        ->when(count($distributes) > 0, function ($query) use ($distributes) {
                            $query->whereHas('distributes', function ($query) use ($distributes) {
                                $query->whereIn('distributes.name',  $distributes);
                            });
                        });
                });
            })


            ->search(request('search'));

        $r =  clone $after_res;
        $res_products = $r
            ->when($status !== null, function ($query) use ($status) {
                $query->where('status', $status);
            });



        $res_products =  $res_products->paginate(request('limit') == null ? 20 : request('limit'));

        return  $res_products;
    }

    /**
     * Báo cáo nhập xuất tồn
     * 
     * status": 0 hiển thị - số còn lại là ẩn
     * 
     * import_export nhập xuất tồn
     * 
     * 
     * has_in_discount: boolean (sp có chuẩn bị và đang diễn ra trong discount)
     * 
     * has_in_combo: boolean (sp có chuẩn bị và đang diễn ra trong combo)
     * 
     * total_stoking còn hàng
     * 
     * total_out_of_stock' hết hàng
     * 
     * total_hide' ẩn
     * 
     * @urlParam  store_code required Store code
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: samsung
     * @queryParam  sort_by Sắp xếp theo VD: price
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  category_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  category_children_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  details Filter theo thuộc tính VD: Màu:Đỏ|Size:XL
     * @queryParam  status (0 -1) còn hàng hay không! không truyền lấy cả 2
     * @queryParam  filter_by Chọn trường nào để lấy
     * @queryParam  filter_option Kiểu filter ( > = <)
     * @queryParam  filter_by_value Giá trị trường đó
     * @queryParam  is_get_all boolean Lấy tất cá hay không 
     * @queryParam  limit int Số item 1 trangơ
     * @queryParam  agency_type_id int id Kiểu đại lý
     * @queryParam  is_show_description bool Cho phép trả về mô tả
     * 
     * 
     */
    public function product_import_export_stock(Request $request, $id)
    {
        $branch = request('branch', $default = null);
        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $branch_ids_input = array();
        if ($branch != null) {
            $branch_ids_input = [$branch->id];
        } else if (count($branch_ids) > 0) {
            $branch_ids_input =  $branch_ids;
        }

        //nho them ton dau ky nhe  
        // total_amount_begin


        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';


        $handle_products = Product::where('status', '!=', '1')
            ->where('store_id', $request->store->id)
            ->where('check_inventory', true)
            ->orderBy('id', 'desc')
            ->get();




        $total_amount_begin = 0;
        $stock_count_end = 0;  //tồn kho cuối kì
        $cost_of_capital_end = 0; //giá vốn cuối kỳ
        $import_price_end = 0; //giá nhập cuối kỳ

        $stock_count_begin = 0; //tồn kho đầu kì
        $cost_of_capital_begin = 0; //giá vốn đầu kì
        $import_price_begin = 0; //giá nhập đầu kì


        foreach ($handle_products as $product) {

            $data = $this->get_data_inventory_begin_end_history_of_product(
                $dateFrom,
                $dateTo,
                $request,
                $product,
                $branch_ids_input
            );

            $stock_count_end +=  $data['stock_count_end'];
            $cost_of_capital_end +=  $data['cost_of_capital_end'];
            $import_price_end +=  $data['import_price_end'];

            $stock_count_begin +=  $data['stock_count_begin'];
            $cost_of_capital_begin +=  $data['cost_of_capital_begin'];
            $import_price_begin +=  $data['import_price_begin'];
        }



        $all3 = InventoryHistory::where('store_id', $request->store->id)
            ->whereIn('branch_id',  $branch_ids_input)
            ->where('updated_at', '>=',  $dateFrom)
            ->where('updated_at', '<', $dateTo)->get();




        $dateFromBegin = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';



        $data3 = $this->total_history_stock($all3);


        //Tính đầu kỳ
        $total_amount_begin = 0;
        foreach ($handle_products as $product) {
            $data = $this->get_last_stock_in_date_with_branch_ids($dateFromBegin, $request, $product);
            $total_amount_begin +=  $data['total_value_stock'];
        }

        //Tính cuối kì
        $total_amount_end = 0;
        foreach ($handle_products as $product) {
            $data = $this->get_last_stock_in_date_with_branch_ids($dateTo, $request, $product);
            $total_amount_end +=  $data['total_value_stock'];
        }

        $custom = collect(
            [
                "import_count_stock" => $data3['import_count_stock'],
                "import_total_amount" => $data3['import_total_amount'],

                "export_count_stock" => $data3['export_count_stock'],
                "export_total_amount" => $data3['export_total_amount'],

                "stock_count_end"  =>  $stock_count_end,
                "cost_of_capital_end" => $cost_of_capital_end,
                "import_price_end" => $import_price_end,

                "stock_count_begin" =>  $stock_count_begin,
                "cost_of_capital_begin" =>  $cost_of_capital_begin,
                "import_price_begin"  => $import_price_begin,

                "total_amount_begin" => $total_amount_begin,
                "total_amount_end" => $total_amount_end
            ]
        );


        $res_products = DB::table('products')->select('id', 'name')
            ->where('status', '!=', '1')
            ->where('store_id', $request->store->id)
            ->where('check_inventory', true)
            ->orderBy('id', 'desc')
            ->paginate(request('limit') == null ? 20 : request('limit'));

        foreach ($res_products as $product) {
            $data_history = $this->get_stock_import_export_with_branch_ids($product->id, $branch_ids_input, $dateFrom, $dateTo, $request);
            $product->images = ProductImage::where('product_id', $product->id)->get();

            $product->main_import_count_stock = $data_history['main_import_count_stock'];
            $product->main_import_total_amount = $data_history['main_import_total_amount'];
            $product->main_export_count_stock = $data_history['main_export_count_stock'];
            $product->main_export_total_amount =  $data_history['main_export_total_amount'];

            $product->distribute_import_export =  $data_history['distribute'];
        }

        $data = $custom->merge($res_products);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $data,
        ], 200);
    }

    /**
     * Báo cáo  tồn kho
     * 
     * status": 0 hiển thị - số còn lại là ẩn
     * 
     * import_export nhập xuất tồn
     * 
     * has_in_discount: boolean (sp có chuẩn bị và đang diễn ra trong discount)
     * 
     * has_in_combo: boolean (sp có chuẩn bị và đang diễn ra trong combo)
     * 
     * total_stoking còn hàng
     * 
     * total_out_of_stock' hết hàng
     * 
     * total_hide' ẩn
     * 
     * @urlParam  store_code required Store code
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: samsung
     * @queryParam  sort_by Sắp xếp theo VD: price
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  category_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  category_children_ids Thuộc category id nào VD: 1,2,3
     * @queryParam  details Filter theo thuộc tính VD: Màu:Đỏ|Size:XL
     * @queryParam  status (0 -1) còn hàng hay không! không truyền lấy cả 2
     * @queryParam  filter_by Chọn trường nào để lấy
     * @queryParam  filter_option Kiểu filter ( > = <)
     * @queryParam  filter_by_value Giá trị trường đó
     * @queryParam  is_get_all boolean Lấy tất cá hay không 
     * @queryParam  limit int Số item 1 trangơ
     * @queryParam  agency_type_id int id Kiểu đại lý
     * @queryParam  is_show_description bool Cho phép trả về mô tả
     * @queryParam  date Date Ngày xem
     * 
     */
    public function product_last_inventory(Request $request, $id)
    {


        $branch = request('branch', $default = null);
        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $branch_ids_input = array();
        if ($branch != null) {
            $branch_ids_input = [$branch->id];
        } else if (count($branch_ids) > 0) {
            $branch_ids_input =  $branch_ids;
        }

        $date = request('date');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($date);


        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 23:59:59';

        // $res_products = $this->res_products($request);

        $handle_products = Product::where('status', '<>', 1)
            ->where('store_id', $request->store->id)
            ->where('check_inventory', true)
            ->orderBy('id', 'desc')
            ->get();


        $total_stock = 0;
        $total_value_stock = 0;
        foreach ($handle_products as $product) {

            $data = Cache::remember(json_encode(["get_last_stock_in_date_with_branch_ids", $dateTo, "-", $product->id]), 30, function ()  use ($dateTo, $request, $product) {
                $data = $this->get_last_stock_in_date_with_branch_ids($dateTo, $request, $product);
                return  $data;
            });
            $total_stock +=  $data['total_stock'];
            $total_value_stock +=  $data['total_value_stock'];
        }


        $res_products = Product::where('status', '<>', 1)
            ->where('store_id', $request->store->id)
            ->where('check_inventory', true)
            ->orderBy('id', 'desc')->paginate(request('limit') == null ? 20 : request('limit'));

        foreach ($res_products as $product) {
            $data = Cache::remember(json_encode(["get_last_stock_in_date_with_branch_ids", $dateTo, "-", $product->id]), 30, function ()  use ($dateTo, $request, $product) {
                $data = $this->get_last_stock_in_date_with_branch_ids($dateTo, $request, $product);
                return  $data;
            });

            $product->images = ProductImage::where('product_id', $product->id)->get();
            $product->distribute_stock =  $data['distribute'];
            $product->main_stock =  $data['main_stock'];
        }



        $custom = collect(
            [
                "total_stock" => $total_stock,
                "total_value_stock" => $total_value_stock,
            ]
        );

        $data = $custom->merge($res_products);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $data,
        ], 200);
    }

    //Ham xu ly mang
    function total_history_stock($histories)
    {
        $import_cost_of_capital = 0;
        $import_import_price = 0;
        $import_count_stock = 0;
        $import_total_amount = 0;

        $export_cost_of_capital = 0;
        $export_import_price = 0;
        $export_count_stock = 0;
        $export_total_amount = 0;


        foreach ($histories as $history) {


            $checkProductExists = Product::where(
                'id',
                $history->product_id
            )->first();

            if ($checkProductExists->check_inventory == true) {
                if ($history->change_money > 0) {
                    $import_total_amount = $import_total_amount + abs($history->change_money);
                }
                if ($history->change > 0) {
                    $import_count_stock =  $import_count_stock + abs($history->change);
                }


                if ($history->change_money < 0) {
                    $export_total_amount = $export_total_amount + abs($history->change_money);
                }

                if ($history->change < 0) {
                    $export_count_stock =   $export_count_stock + abs($history->change);
                }
            }
        }

        return [
            "import_count_stock" => $import_count_stock,
            "import_total_amount" => $import_total_amount,

            "export_count_stock" => $export_count_stock,
            "export_total_amount" => $export_total_amount,
        ];
    }




    /**
     * Danh sách phiếu thu chi
     * 
     * @urlParam  store_code required Store code
     * @queryParam recipient_group int id Nhóm khách hàng
     * @queryParam recipient_references_id int id ID chủ thể
     * @queryParam  search Mã phiếu
     * @queryParam is_revenue boolean Phải thu không
     * 
     */
    function revenue_expenditure(Request $request)
    {

        $is_revenue = request('is_revenue');

        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';

        $branch = request('branch', $default = null);
        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $branch_ids_input = array();
        if ($branch != null) {
            $branch_ids_input = [$branch->id];
        } else if (count($branch_ids) > 0) {
            $branch_ids_input =  $branch_ids;
        }

        $search = request('search');
        $revenueExpenditures = RevenueExpenditure::where('store_id', $request->store->id)
            ->whereIn('branch_id', $branch_ids_input)
            ->where('created_at', '>=',  $dateFrom)
            ->where('created_at', '<', $dateTo)
            ->orderBy('id', 'desc')
            ->when($is_revenue  !== null && $is_revenue !== '', function ($query) use ($is_revenue) {
                $query->where('is_revenue', filter_var($is_revenue, FILTER_VALIDATE_BOOLEAN));
            })
            ->search($search)
            ->paginate(request('limit') == null ? 20 : request('limit'));


        $renvenure = RevenueExpenditure::where('store_id', $request->store->id)
            ->whereIn('branch_id', $branch_ids_input)
            ->where('created_at', '>=',  $dateFrom)
            ->where('created_at', '<', $dateTo)
            ->where('is_revenue', true)
            ->sum('change_money');

        $expenditure = RevenueExpenditure::where('store_id', $request->store->id)
            ->whereIn('branch_id', $branch_ids_input)
            ->where('created_at', '>=',  $dateFrom)
            ->where('created_at', '<', $dateTo)
            ->where('is_revenue', false)
            ->sum('change_money');



        $custom = collect(
            [
                "renvenure" =>   $renvenure,
                "expenditure" =>  $expenditure,
                "reserve" => $renvenure - $expenditure
            ]
        );

        $data = $custom->merge($revenueExpenditures);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data
        ], 200);
    }



    /**
     * Báo cáo nhập xuất theo thời gian
     * 
     */
    public function inventory_histories(Request $request, $id)
    {

        $branch = request('branch', $default = null);
        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $branch_ids_input = array();
        if ($branch != null) {
            $branch_ids_input = [$branch->id];
        } else if (count($branch_ids) > 0) {
            $branch_ids_input =  $branch_ids;
        }

        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';

        $id_products = Product::where('status', '!=', '1')
            ->where('store_id', $request->store->id)
            ->where('check_inventory', true)
            ->orderBy('id', 'desc')
            ->pluck('id')->toArray();


        $histories = InventoryHistory::whereIn('branch_id', $branch_ids_input)
            ->where('store_id', $request->store->id)
            ->where('created_at', '>=',  $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->whereIn('product_id',    $id_products)
            ->orderBy('id', 'desc')
            ->paginate(request('limit') == null ? 20 : request('limit'));

        $count_import = 0;
        $count_export = 0;

        $import_value = 0;
        $export_value = 0;



        $handle_histories = InventoryHistory::where('store_id', $request->store->id)
            ->whereIn('branch_id', $branch_ids_input)
            ->whereIn('product_id',    $id_products)
            ->where('created_at', '>=',  $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->get();

        $arr_product_with_type = [];


        //tính tổng
        foreach ($handle_histories as    $handle_history) {


            $product = null;
            if (!isset($arr_product_with_type[$handle_history->product_id])) {
                $product = Product::where('id', $handle_history->product_id)->first();
                $arr_product_with_type[$handle_history->product_id] = $product;
            } else {
                $product = $arr_product_with_type[$handle_history->product_id];
            }

            $type_product = ProductUtils::check_type_distribute($product);

            $valid1 =  $type_product == ProductUtils::HAS_SUB && $handle_history->element_distribute_id != null && $handle_history->sub_element_distribute_id != null;
            $valid2 =  $type_product == ProductUtils::HAS_ELE && $handle_history->element_distribute_id != null && $handle_history->sub_element_distribute_id == null;
            $valid3 =  $type_product == ProductUtils::NO_ELE_SUB && $handle_history->element_distribute_id == null && $handle_history->sub_element_distribute_id == null;
            $valid4 =  $product->check_inventory;

            if (($valid1  || $valid2 ||  $valid3) &&  $valid4) {
                if ($handle_history->change_money > 0) {
                    $import_value += ($handle_history->change_money);
                }
                if ($handle_history->change > 0) {
                    $count_import += $handle_history->change;
                }
                if ($handle_history->change_money < 0) {
                    $export_value += (abs($handle_history->change_money));
                }
                if ($handle_history->change < 0) {
                    $count_export += abs($handle_history->change);
                }
            }
        }



        $custom = collect(
            [
                "count_import" =>   $count_import,
                "count_export" =>  $count_export,
                "import_value" => $import_value,
                "export_value" => $export_value,
            ]
        );

        $data = $custom->merge($histories);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $data,
        ], 200);
    }

    /**
     * Export excel
     */
    public function link_export(Request $request)
    {
        $page = request('page');
        $search = request('search');
        $limit = request('limit');
        $is_revenue = request('is_revenue');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $link_export = "page=" . $page . '&search=' . $search . '&limit=' . $limit . '&is_revenue=' . $is_revenue . '&date_from=' . $dateFrom . '&date_to=' . $dateTo;
        $md5_params = md5($link_export);
        $base64Date = base64_encode($carbon->addMinutes(3)->format('Y-m-d H:i:s'));

        $link_export = url()->current() . '?' . $link_export . '&en=' . $md5_params . '&ex=' . $base64Date;
        $link_export = str_replace('link_export', 'export', $link_export);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $link_export
        ], 200);
    }

    public function export(Request $request)
    {
        try {
            $revenueExpendituresExport = new RevenueExpendituresExport;
            return Excel::download($revenueExpendituresExport, 'thu_chi.xlsx');
        } catch (Exception $e) {
            echo ($e->getMessage());
        }
    }

    //Hàm tổng nhập xuất theo ngày - đến ngày
    function get_stock_import_export_with_branch_ids($product_id, $branch_ids, $dateFrom, $dateTo, $request)
    {
        $data = [];

        $distributes = [];

        $product = Product::where('id', $product_id)->first();
        $check_inventory =  $product->check_inventory;

        $type_product = ProductUtils::check_type_distribute($product);
        $distributes_db = Distribute::where('product_id', $product_id)->take(1)->get();

        if ($branch_ids != null) {

            if ($distributes_db != null && count($distributes_db) > 0) {


                foreach ($distributes_db as $distribute) {

                    $element_distributes = [];
                    foreach ($distribute->element_distributes as $element_distribute) {

                        $sub_element_distributes = [];
                        foreach ($element_distribute->sub_element_distributes as $sub_element_distribute) {

                            $data = null;

                            if ($type_product == ProductUtils::HAS_SUB ||   $check_inventory == true) {
                                $all = InventoryHistory::where('store_id', $request->store->id)
                                    ->whereIn('branch_id', $branch_ids)
                                    ->where('updated_at', '>=',  $dateFrom)
                                    ->where('product_id', $product_id)
                                    ->where('element_distribute_id', $element_distribute->id)
                                    ->where('sub_element_distribute_id', $sub_element_distribute->id)
                                    ->where('updated_at', '<', $dateTo)->get();
                                $data = $this->total_history_stock($all);
                            }


                            array_push(
                                $sub_element_distributes,
                                [
                                    "id" => $sub_element_distribute->id,
                                    "element_distribute_id" => $sub_element_distribute->element_distribute_id,
                                    "name" => $sub_element_distribute->name,
                                    "quantity_in_stock" =>  $sub_element_distribute->quantity_in_stock,

                                    "import_count_stock" =>  $data == null ? 0 : $data['import_count_stock'],
                                    "import_total_amount" => $data == null ? 0 : $data['import_total_amount'],

                                    "export_count_stock" => $data == null ? 0 : $data['export_count_stock'],
                                    "export_total_amount" => $data == null ? 0 : $data['export_total_amount'],
                                ]
                            );
                        }
                        $data2 = null;
                        if ($type_product == ProductUtils::HAS_ELE ||   $check_inventory == true) {
                            $all2 = InventoryHistory::where('store_id', $request->store->id)
                                ->where('element_distribute_id', $element_distribute->id)
                                ->where('sub_element_distribute_id', null)
                                ->whereIn('branch_id', $branch_ids)
                                ->where('product_id', $product_id)
                                ->where('updated_at', '>=',  $dateFrom)
                                ->where('updated_at', '<', $dateTo)->get();

                            $data2 = $this->total_history_stock($all2);
                        }

                        array_push(
                            $element_distributes,
                            [
                                "name" =>  $element_distribute->name,
                                "id" =>  $element_distribute->id,
                                "quantity_in_stock" =>  $element_distribute->quantity_in_stock,
                                "sub_element_distributes" =>   $sub_element_distributes,

                                "import_count_stock" => $data2 == null ? 0 : $data2['import_count_stock'],
                                "import_total_amount" =>  $data2 == null ? 0 : $data2['import_total_amount'],

                                "export_count_stock" =>  $data2 == null ? 0 : $data2['export_count_stock'],
                                "export_total_amount" =>  $data2 == null ? 0 : $data2['export_total_amount'],
                            ]
                        );
                    }

                    $object = json_decode(json_encode((object) [
                        "id" => $distribute->id,
                        "name" => $distribute->name,
                        "sub_element_distribute_name" =>  $distribute->sub_element_distribute_name,
                        "element_distributes" =>  $element_distributes
                    ]), FALSE);

                    $distribute->element_distributes = [];

                    // $element_distributes

                    $distributes = [
                        $object
                    ];
                }
            }
        }

        $data3 = null;
        if ($type_product == ProductUtils::NO_ELE_SUB ||   $check_inventory == true) {
            $all4 = InventoryHistory::where('store_id', $request->store->id)
                ->whereIn('branch_id', $branch_ids)
                ->where('updated_at', '>=',  $dateFrom)
                ->where('product_id', $product_id)
                ->where('element_distribute_id', null)
                ->where('sub_element_distribute_id', null)
                ->where('updated_at', '<', $dateTo)->get();

            $data3 = $this->total_history_stock($all4);
        }

        return [
            'distribute' =>  $distributes,
            "main_import_count_stock" => $data3 == null ? 0 : $data3['import_count_stock'],
            "main_import_total_amount" => $data3 == null ? 0 : $data3['import_total_amount'],
            "main_export_count_stock" => $data3 == null ? 0 : $data3['export_count_stock'],
            "main_export_total_amount" =>  $data3 == null ? 0 : $data3['export_total_amount'],

        ];
    }

    //Hàm lấy kho cuối cùng theo ngày
    function get_last_stock_in_date_with_branch_ids($dateTo, $request, $product)
    {

        $branch = request('branch', $default = null);
        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $branch_ids_input = array();
        if ($branch != null) {
            $branch_ids_input = [$branch->id];
        } else if (count($branch_ids) > 0) {
            $branch_ids_input =  $branch_ids;
        }

        $type_product = ProductUtils::check_type_distribute($product);

        $total_stock = 0;
        $total_value_stock = 0;
        $data = [];

        $distributes_db = Distribute::where('product_id', $product->id)->take(1)->get();
        $distributes = [];
        if ($request->branch != null) {

            if ($distributes_db != null && count($distributes_db) > 0) {


                foreach ($distributes_db as $distribute) {

                    $element_distributes = [];
                    foreach ($distribute->element_distributes as $element_distribute) {

                        $sub_element_distributes = [];
                        foreach ($element_distribute->sub_element_distributes as $sub_element_distribute) {
                            $inventoryHistory = null;

                            //Lấy lịch sử của sub

                            if ($type_product  == ProductUtils::HAS_SUB) {
                                $inventoryHistory =  $this->get_data_inventory_begin_end_history(
                                    $request->store->id,
                                    $branch_ids_input,
                                    $product->id,
                                    $distribute->name,
                                    $element_distribute->name,
                                    $sub_element_distribute->name,
                                    $dateTo,
                                    $dateTo
                                );
                            }

                            $stock = $inventoryHistory == null ? 0 : $inventoryHistory['stock_count_end'];
                            $cost_of_capital = $inventoryHistory == null ? 0 : $inventoryHistory['cost_of_capital_end'];
                            $import_price = $inventoryHistory == null ? 0 : $inventoryHistory['import_price_end'];
                            array_push(
                                $sub_element_distributes,
                                [
                                    "id" => $sub_element_distribute->id,
                                    "element_distribute_id" => $sub_element_distribute->element_distribute_id,
                                    "name" => $sub_element_distribute->name,

                                    "stock" =>  $stock,
                                    "cost_of_capital" => $cost_of_capital,
                                    "import_price" => $import_price,
                                ]
                            );

                            $total_stock += $stock;
                            $total_value_stock += ($stock * $cost_of_capital);
                        }

                        $inventoryHistory = null;
                        if ($type_product  == ProductUtils::HAS_ELE) {
                            $inventoryHistory = InventoryHistory::where('store_id', $request->store->id)
                                ->whereIn('branch_id',  $branch_ids_input)
                                ->where('product_id', $product->id)
                                ->where('element_distribute_id', $element_distribute->id)
                                ->take(1)
                                ->orderBy('id', 'desc')
                                ->where('updated_at', '<', $dateTo)->first();
                        }

                        $stock = $inventoryHistory == null ? 0 : $inventoryHistory['stock'];
                        $cost_of_capital = $inventoryHistory == null ? 0 : $inventoryHistory['cost_of_capital'];
                        $import_price = $inventoryHistory == null ? 0 : $inventoryHistory['import_price'];

                        $total_stock += $stock;
                        $total_value_stock += ($stock * $cost_of_capital);

                        array_push(
                            $element_distributes,
                            [
                                "name" =>  $element_distribute->name,
                                "id" =>  $element_distribute->id,
                                "sub_element_distributes" =>   $sub_element_distributes,

                                "stock" =>  $stock,
                                "cost_of_capital" =>  $cost_of_capital,
                                "import_price" => $import_price,
                            ]
                        );
                    }

                    $object = json_decode(json_encode((object) [
                        "id" => $distribute->id,
                        "name" => $distribute->name,
                        "sub_element_distribute_name" =>  $distribute->sub_element_distribute_name,
                        "element_distributes" =>  $element_distributes
                    ]), FALSE);

                    $distribute->element_distributes = [];

                    // $element_distributes

                    $distributes = [
                        $object
                    ];
                }
            }
        }

        $mainProductInventory = null;

        if ($type_product  == ProductUtils::NO_ELE_SUB) {
            $mainProductInventory = $this->get_data_inventory_begin_end_history($request->store->id, $branch_ids_input, $product->id, null, null, null, $dateTo, $dateTo);
        }

        $stock = $mainProductInventory == null ? 0 : $mainProductInventory['stock_count_end'];
        $cost_of_capital = $mainProductInventory == null ? 0 : $mainProductInventory['cost_of_capital_end'];
        $import_price = $mainProductInventory == null ? 0 : $mainProductInventory['import_price_end'];

        $total_stock += $stock;
        $total_value_stock += ($stock * $cost_of_capital);


        return [
            'distribute' => $distributes,
            'main_stock' => [
                "stock" => $stock,
                "cost_of_capital" => $cost_of_capital,
                "import_price" =>  $import_price,
            ],
            "total_stock" => $total_stock,
            "total_value_stock" => $total_value_stock
        ];
    }


    //hàm lấy giá trị nhập xuất tài thời điểm đầu kỳ cuối kỳ (date from - date to) tất cả dữ liệu của sản phẩm
    function get_data_inventory_begin_end_history_of_product($dateFrom, $dateTo,  $request, $product, $branch_ids)
    {


        $total_stock = 0;
        $total_value_stock = 0;
        $data = [];
        $stock_count_end = 0;

        $distributes_db = Distribute::where('product_id', $product->id)->take(1)->get();

        $type_product = ProductUtils::check_type_distribute($product);

        $distributes = [];
        if ($request->branch != null) {

            if ($distributes_db != null && count($distributes_db) > 0) {


                foreach ($distributes_db as $distribute) {

                    $element_distributes = [];
                    foreach ($distribute->element_distributes as $element_distribute) {

                        //Sub
                        $sub_element_distributes = [];
                        foreach ($element_distribute->sub_element_distributes as $sub_element_distribute) {
                            $inventoryHistory = null;

                            if ($type_product == ProductUtils::HAS_SUB) {


                                $inventoryHistory =  $this->get_data_inventory_begin_end_history(
                                    $request->store->id,
                                    $branch_ids,
                                    $product->id,
                                    $distribute->name,
                                    $element_distribute->name,
                                    $sub_element_distribute->name,
                                    $dateFrom,
                                    $dateTo
                                );
                            }
                            $stock_count_end += $inventoryHistory == null ? 0 : $inventoryHistory['stock_count_end'];
                            $stock = $inventoryHistory == null ? 0 : $inventoryHistory['stock_count_end'];
                            $cost_of_capital = $inventoryHistory == null ? 0 : $inventoryHistory['cost_of_capital_end'];
                            $import_price = $inventoryHistory == null ? 0 : $inventoryHistory['import_price_end'];
                            array_push(
                                $sub_element_distributes,
                                [
                                    "id" => $sub_element_distribute->id,
                                    "element_distribute_id" => $sub_element_distribute->element_distribute_id,
                                    "name" => $sub_element_distribute->name,

                                    "stock" =>  $stock,
                                    "cost_of_capital" => $cost_of_capital,
                                    "import_price" => $import_price,
                                ]
                            );

                            $total_stock += $stock;
                            $total_value_stock += ($stock * $cost_of_capital);
                        }

                        //Ele
                        $eleProductInventory = null;

                        if ($type_product == ProductUtils::HAS_ELE) {
                            $eleProductInventory = $this->get_data_inventory_begin_end_history(
                                $request->store->id,
                                $branch_ids,
                                $product->id,
                                $distribute->name,
                                $element_distribute->name,
                                null,
                                $dateFrom,
                                $dateTo
                            );
                        }
                        $stock_count_end += $eleProductInventory == null ? 0 : $eleProductInventory['stock_count_end'];
                        $stock = $eleProductInventory == null ? 0 : $eleProductInventory['stock_count_end'];
                        $cost_of_capital = $eleProductInventory == null ? 0 : $eleProductInventory['cost_of_capital_end'];
                        $import_price = $eleProductInventory == null ? 0 : $eleProductInventory['import_price_end'];

                        $total_stock += $stock;
                        $total_value_stock += ($stock * $cost_of_capital);

                        array_push(
                            $element_distributes,
                            [
                                "name" =>  $element_distribute->name,
                                "id" =>  $element_distribute->id,
                                "sub_element_distributes" =>   $sub_element_distributes,

                                "stock" =>  $stock,
                                "cost_of_capital" =>  $cost_of_capital,
                                "import_price" => $import_price,
                            ]
                        );
                    }

                    $object = json_decode(json_encode((object) [
                        "id" => $distribute->id,
                        "name" => $distribute->name,
                        "sub_element_distribute_name" =>  $distribute->sub_element_distribute_name,
                        "element_distributes" =>  $element_distributes
                    ]), FALSE);

                    $distribute->element_distributes = [];

                    // $element_distributes

                    $distributes = [
                        $object
                    ];
                }
            }
        }

        if ($type_product == ProductUtils::NO_ELE_SUB) {

            $mainProductInventory = $this->get_data_inventory_begin_end_history($request->store->id, $branch_ids, $product->id, null, null, null, $dateFrom, $dateTo);

            $stock_count_end = $mainProductInventory == null ? 0 : $mainProductInventory['stock_count_end'];
            $cost_of_capital_end = $mainProductInventory == null ? 0 : $mainProductInventory['cost_of_capital_end'];
            $import_price_end = $mainProductInventory == null ? 0 : $mainProductInventory['import_price_end'];

            $stock_count_begin = $mainProductInventory == null ? 0 : $mainProductInventory['stock_count_begin'];
            $cost_of_capital_begin = $mainProductInventory == null ? 0 : $mainProductInventory['cost_of_capital_begin'];
            $import_price_begin = $mainProductInventory == null ? 0 : $mainProductInventory['import_price_begin'];
        }

        return [
            'distribute' => $distributes,

            "stock_count_end" =>  $stock_count_end ?? 0,
            "cost_of_capital_end" => $cost_of_capital_end ?? 0,
            "import_price_end" => $import_price_end ?? 0,

            "stock_count_begin" => $stock_count_begin ?? 0,
            "cost_of_capital_begin" => $cost_of_capital_begin ?? 0,
            "import_price_begin" => $import_price_begin ?? 0,
        ];
    }

    //hàm lấy giá trị nhập xuất tài thời điểm đầu kỳ cuối kỳ (date from - date to) moi ele
    function get_data_inventory_begin_end_history($store_id, $branch_ids, $product_id, $distribute_name, $element_distribute_name, $sub_element_distribute_name, $dateFrom, $dateTo)
    {

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
                        $inventorySubBegin = InventoryHistory::where('element_distribute_id', $ele_distribute->id)
                            ->where('sub_element_distribute_id', $sub_ele_distribute->id)
                            ->where('created_at', '>', $dateFrom)
                            ->orderBy('id', 'asc')
                            ->where('product_id', $product_id)
                            ->whereIn('branch_id', $branch_ids)
                            ->first();

                        $inventorySubEnd = InventoryHistory::where('element_distribute_id', $ele_distribute->id)
                            ->where('sub_element_distribute_id', $sub_ele_distribute->id)
                            ->where('created_at', '<', $dateTo)
                            ->orderBy('id', 'desc')
                            ->where('product_id', $product_id)
                            ->whereIn('branch_id', $branch_ids)
                            ->first();


                        return  [

                            'stock_count_begin' =>  $inventorySubBegin == null ? 0 : $inventorySubBegin->stock,
                            'cost_of_capital_begin' => $inventorySubBegin == null ? 0 : $inventorySubBegin->cost_of_capital,
                            'import_price_begin' => $inventorySubBegin == null ? 0 : $inventorySubBegin->import_price,

                            'stock_count_end' =>   $inventorySubEnd == null ? 0 : $inventorySubEnd->stock,
                            'cost_of_capital_end' => $inventorySubEnd == null ? 0 : $inventorySubEnd->cost_of_capital,
                            'import_price_end' =>  $inventorySubEnd == null ? 0 : $inventorySubEnd->import_price,
                        ];
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

                    $inventoryEleBegin = InventoryHistory::where('element_distribute_id', $ele_distribute->id)
                        ->where('created_at', '>', $dateFrom)
                        ->orderBy('id', 'asc')
                        ->where('sub_element_distribute_id', null)
                        ->where('element_distribute_id',  $ele_distribute->id)
                        ->where('product_id', $product_id)
                        ->whereIn('branch_id', $branch_ids)
                        ->first();

                    $inventoryEleEnd = InventoryHistory::where('element_distribute_id', $ele_distribute->id)
                        ->where('created_at', '<', $dateTo)
                        ->orderBy('id', 'desc')
                        ->where('sub_element_distribute_id', null)
                        ->where('element_distribute_id',  $ele_distribute->id)
                        ->where('product_id', $product_id)
                        ->whereIn('branch_id', $branch_ids)
                        ->first();


                    return  [

                        'stock_count_begin' =>  $inventoryEleBegin == null ? 0 : $inventoryEleBegin->stock,
                        'cost_of_capital_begin' => $inventoryEleBegin == null ? 0 : $inventoryEleBegin->cost_of_capital,
                        'import_price_begin' => $inventoryEleBegin == null ? 0 : $inventoryEleBegin->import_price,

                        'stock_count_end' =>   $inventoryEleEnd == null ? 0 : $inventoryEleEnd->stock,
                        'cost_of_capital_end' => $inventoryEleEnd == null ? 0 : $inventoryEleEnd->cost_of_capital,
                        'import_price_end' =>  $inventoryEleEnd == null ? 0 : $inventoryEleEnd->import_price,
                    ];
                }
            }
        } else {

            $inventoryBegin = InventoryHistory::where('created_at', '>', $dateFrom)
                ->orderBy('id', 'asc')
                ->where('sub_element_distribute_id', null)
                ->where('element_distribute_id', null)
                ->where('product_id', $product_id)
                ->whereIn('branch_id', $branch_ids)
                ->first();

            $inventoryEnd = InventoryHistory::where('created_at', '<=', $dateTo)

                ->where('sub_element_distribute_id', null)
                ->where('element_distribute_id', null)
                ->where('product_id', $product_id)
                ->orderBy('id', 'desc')
                ->whereIn('branch_id', $branch_ids)
                ->take(1)
                ->first();



            return  [

                'stock_count_begin' =>  $inventoryBegin == null ? 0 : $inventoryBegin->stock,
                'cost_of_capital_begin' => $inventoryBegin == null ? 0 : $inventoryBegin->cost_of_capital,
                'import_price_begin' => $inventoryBegin == null ? 0 : $inventoryBegin->import_price,

                'stock_count_end' =>   $inventoryEnd == null ? 0 : $inventoryEnd->stock,
                'cost_of_capital_end' => $inventoryEnd == null ? 0 : $inventoryEnd->cost_of_capital,
                'import_price_end' =>  $inventoryEnd == null ? 0 : $inventoryEnd->import_price,
            ];
        }

        return null;
    }
}
