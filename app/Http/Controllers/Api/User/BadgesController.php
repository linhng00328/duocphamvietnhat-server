<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\ProductUtils;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\CollaboratorsConfig;
use App\Models\Combo;
use App\Models\ConfigUserVip;
use App\Models\DateTimekeepingHistory;
use App\Models\Discount;
use App\Models\ImportStock;
use App\Models\ListCart;
use App\Models\MsgCode;
use App\Models\NotificationUser;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReviews;
use App\Models\RoomChat;
use App\Models\SaleVisitAgency;
use App\Models\Staff;
use App\Models\Store;
use App\Models\StoreAddress;
use App\Models\TallySheet;
use App\Models\User;
use App\Models\Voucher;
use App\Models\WebTheme;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * @group  User/Chỉ số
 */
class BadgesController extends Controller
{

    static function data_badges($store_id, $user_id, $staff_id, $branch_id)
    {
        $now = Helper::getTimeNowDateTime();
        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $orders = Order::where(
            'store_id',
            $store_id
        )
            ->when($branch_id != null, function ($query) use ($branch_id) {
                $query->where(
                    'branch_id',
                    $branch_id
                );
            })
            ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                $query->whereIn('branch_id', $branch_ids);
            })
            ->where('created_at', '>=',  $now->format('Y-m-d 00:00:00'))
            ->where('created_at', '<', $now->format('Y-m-d 23:59:59'))
            ->get();

        //total orders
        $orders_waitting_for_progressing = 0;
        $orders_packing = 0;
        $orders_shipping = 0;
        $orders_refunds = 0; //đơn hoàn trả
        $total_orders = 0;
        $total_final_in_day = 0;
        $total_orders_in_day = 0;
        $total_orders_completed_in_day = 0;

        $total_staff_online = 0;
        $total_tally_sheet_checked = 0;
        $total_import_not_completed = 0;
        $total_product_or_discount_nearly_out_stock = 0;  //sản phẩm sắp hết hàng

        $staff_has_checkin = false;

        //Sản phẩm gần hết hàng

        $total_product_or_discount_nearly_out_stock =  0;
        // if ($branch_id != null) {
        //     $handle_products = Product::where('status', '<>', 1)
        //         ->where('store_id', $store_id)
        //         ->where('check_inventory', true)
        //         ->orderBy('id', 'desc');
        //     $arr_out_stock =  ProductUtils::arr_list_product_out_of_stock($store_id, $handle_products);
        //     $total_product_or_discount_nearly_out_stock = count($arr_out_stock);
        // }

        //ĐƠn lưu tạm
        $temporary_order = 0;
        $temporary_order = ListCart::when($branch_id != null, function ($query) use ($branch_id) {
            $query->where(
                'branch_id',
                $branch_id
            );
        })->when($user_id != null, function ($query) use ($user_id) {
            $query->where(
                'user_id',
                $user_id
            );
        })
            ->when($staff_id != null, function ($query) use ($staff_id) {
                $query->where(
                    'staff_id',
                    $staff_id
                );
            })
            ->where('edit_order_code', null)
            ->where('is_default', false)
            ->where('store_id',  $store_id)
            ->count();





        foreach ($orders as $order) {
            $total_orders += 1;
            $total_orders_in_day += 1;




            if ($order->order_status == StatusDefineCode::COMPLETED) {
                $total_orders_completed_in_day++;
                $total_final_in_day += $order->total_final;
            }
            if ($order->order_status == StatusDefineCode::WAITING_FOR_PROGRESSING) {
                $orders_waitting_for_progressing++;
            }
            if ($order->order_status == StatusDefineCode::PACKING) {
                $orders_packing++;
            }
            if ($order->order_status == StatusDefineCode::SHIPPING) {
                $orders_shipping++;
            }
            if ($order->payment_status == StatusDefineCode::PAY_REFUNDS && $order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {
                $orders_refunds++;
                $total_final_in_day -= $order->total_final;
            }

            // if ($order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {
            //     $total_final_in_day -= $order->total_final;
            // }
        }

        //staff_has_checkin
        $date = Carbon::now('Asia/Ho_Chi_Minh');
        $date = $date->year . '-' . $date->month . '-' . $date->day;

        if ($branch_id && $staff_id != null) {
            $last_check = DateTimekeepingHistory::where('store_id', $store_id)
                ->where('branch_id', $branch_id)
                ->where('staff_id', $staff_id)
                ->orderBy('id', 'desc')
                ->where('date', $date)->first();

            if ($last_check != null) {
                $staff_has_checkin  = true;
            }
        }



        //total chat
        $lastRooms = RoomChat::where('store_id', $store_id)
            ->get();

        $chats_unread = 0;
        foreach ($lastRooms as $lastRoom) {
            if ($lastRoom->user_unread) {
                $chats_unread += $lastRoom->user_unread;
            }
        }

        //notification_unread
        $notification_unread = 0;

        $notification_unread = NotificationUser::where('store_id', $store_id)
            ->where(function ($query) use ($branch_id) {
                $query->where(
                    'branch_id',
                    $branch_id
                )->orWhere('branch_id', "=", null);
            })
            ->where('unread', true)->count();

        //total voucher
        $now = Helper::getTimeNowString();
        $voucher_total = Voucher::where('store_id', $store_id,)
            ->where('is_end', false)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->whereRaw('vouchers.is_show_voucher = true AND (vouchers.amount - vouchers.used > 0 OR vouchers.set_limit_amount = false)')
            ->count();

        //total_combo
        $now = Helper::getTimeNowDateTime();
        $combo_total = Combo::where('store_id', $store_id,)
            ->where('is_end', false)
            ->where('end_time', '>=', $now)
            ->orderBy('created_at', 'desc')
            ->count();

        //$products_discount 
        $products_discount = Discount::where('store_id', $store_id,)
            ->where('is_end', false)
            ->where('start_time', '<', $now)
            ->where('end_time', '>', $now)
            ->orderBy('created_at', 'desc')
            ->whereRaw('(discounts.amount - discounts.used > 0 OR discounts.set_limit_amount = false)')
            ->count();

        //total review

        $reviews_no_process = ProductReviews::where(
            'store_id',
            $store_id
        )->where(
            'status',
            0
        )->count();

        //Phan quyen
        $columns = Schema::getColumnListing('decentralizations');

        $list_vip = [
            "onsale" => false,
            "train" => false,
            "timekeeping" => false,
            "community" => false,
        ];

        $store = Store::where('id', $store_id)->first();

        foreach ($store->user->functions as $func) {
            if ($func == "onsale") {
                $list_vip['onsale'] = true;
            }
            if ($func == "train") {
                $list_vip['train'] = true;
            }
            if ($func == "timekeeping") {
                $list_vip['timekeeping'] = true;
            }
            if ($func == "community") {
                $list_vip['community'] = true;
            }
        }


        $user = null;
        $staff = null;

        if ($user_id != null) {
            $user = User::where('id', $user_id)->first();
        } else  if ($staff_id != null) {
            $staff = Staff::where('id', $staff_id)->where('store_id', $store_id)->first();
        }


        $decentralization = [];
        foreach ($columns as $column) {
            if (
                $column == 'id' ||
                $column == 'store_id' ||
                $column == 'created_at' ||
                $column == 'updated_at' ||
                $column == 'name' ||
                $column == 'description'
            ) {
                continue;
            }

            if ($user != null && $user->is_block == true) {
                $decentralization[$column] = false;
                continue;
            }

            if ($store->store_code == 'chinhbv' &&  ($column == 'ecommerce_products' ||  $column == 'ecommerce_connect'  || $column == 'ecommerce_orders'  ||  $column == 'ecommerce_inventory')) {
                $decentralization[$column] = true;
                continue;
            }
            if ($store->store_code != 'chinhbv' &&  ($column == 'ecommerce_products'  || $column == 'ecommerce_connect'  || $column == 'ecommerce_orders' || $column == 'ecommerce_inventory')) {
                $decentralization[$column] = false;
                continue;
            }

            if ($user_id != null) {
                $decentralization[$column] = true;
            } else  if ($staff_id != null) {
                if ($staff->decentralization == null) {
                    $decentralization[$column] = false;
                    continue;
                }
                $decentralization[$column] = filter_var($staff->decentralization->$column, FILTER_VALIDATE_BOOLEAN);
            }


            if ($column == 'onsale' && ($list_vip['onsale'] != true ||  $decentralization[$column] != true)) {
                $decentralization[$column] = false;
            }
            if ($column == 'train' && ($list_vip['train'] != true ||  $decentralization[$column]  != true)) {
                $decentralization[$column] = false;
            }
            if ($column == 'timekeeping' && ($list_vip['timekeeping'] != true ||  $decentralization[$column]  != true)) {
                $decentralization[$column] = false;
            }
            if ($column == 'community' && ($list_vip['community'] != true ||  $decentralization[$column] != true)) {
                $decentralization[$column] = false;
            }
        }

        $decentralization['sale_share'] = true;

        //User vip
        $config_user_vip = null;
        if ($store->user->is_vip == true) {
            $config_user_vip = ConfigUserVip::where('user_id', $store->user->id)->first();
        }

        $addressPickupExists = StoreAddress::where(
            'store_id',
            $store_id
        )->where('is_default_pickup', true)->first();


        //Staff online
        $staffs = Staff::where('store_id', $store_id)
            // ->when($request->branch != null, function ($query) use ($request) {
            //     $query->where(
            //         'branch_id',
            //         $branch_id
            //     );
            // })
            ->get();
        foreach ($staffs as $staffO) {
            if ($staffO->isOnline()) {
                $total_staff_online += 1;
            }
        }

        ///phiếu kiểm
        $total_tally_sheet_checked
            = TallySheet::where('store_id', $store_id)
            ->where('status', InventoryUtils::STATUS_INVENTORY_CHECKED)
            ->when($branch_id != null, function ($query) use ($branch_id) {
                $query->where(
                    'branch_id',
                    $branch_id
                );
            })
            ->count();

        ///phiếu nhập chưa xử lý
        $total_import_not_completed
            = ImportStock::where('store_id', $store_id)
            ->where('status', '!=', InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED)
            ->where('status', '!=', InventoryUtils::STATUS_IMPORT_STOCK_REFUND)
            ->where('status', '!=', InventoryUtils::STATUS_IMPORT_STOCK_CANCELED)
            ->where('status', '!=', InventoryUtils::STATUS_IMPORT_STOCK_PAUSE)
            ->when($branch_id != null, function ($query) use ($branch_id) {
                $query->where(
                    'branch_id',
                    $branch_id
                );
            })
            ->count();

        $webThemeExists = WebTheme::where(
            'store_id',
            $store->id
        )->select('domain')->first();

        $domain = ($webThemeExists->domain ?? "");
        $domain  = str_replace("http://", "", $domain);
        $domain  = str_replace("https://", "", $domain);
        $domain  = "https://" . $domain;

        $data = [
            "store_code" => $store->store_code,
            "store_name" => $store->name,
            'domain_customer' => $webThemeExists == null || empty($webThemeExists->domain) ? "https://" . $store->store_code . ".ikitech.vn" : $webThemeExists->domain,
            "is_staff" => $staff != null, // phải staff không
            "is_sale" => ($staff != null && $staff->is_sale), // phải staff không
            "total_orders" => $total_orders,
            "total_orders_in_day" => $total_orders_in_day,
            "temporary_order" => $temporary_order, //Tổng đơn lưu tạm
            "orders_waitting_for_progressing" => $orders_waitting_for_progressing, //sô đơn đang xử lý
            "orders_refunds" => $orders_refunds,
            "orders_packing" => $orders_packing,
            "orders_shipping" => $orders_shipping,
            "total_staff_online" => $total_staff_online,
            "total_import_not_completed" => $total_import_not_completed,
            "total_tally_sheet_checked" => $total_tally_sheet_checked, //tổng phiếu đang kiểm
            "total_product_or_discount_nearly_out_stock" => $total_product_or_discount_nearly_out_stock, //sản phẩm hoặc phân loại sắp hết hàng
            "chats_unread" =>  $chats_unread,
            "voucher_total" => $voucher_total,
            "combo_total" => $combo_total,
            "products_discount" => $products_discount,
            "reviews_no_process" =>  $reviews_no_process,
            "total_final_in_day" => $total_final_in_day,
            "total_orders_completed_in_day" => $total_orders_completed_in_day,
            "notification_unread" => $notification_unread,
            "decentralization" => $decentralization,
            "config_user_vip" => $config_user_vip,
            "address_pickup" =>    $addressPickupExists,
            "staff_has_checkin" =>  $staff_has_checkin,
            "allow_semi_negative" =>   GeneralSettingController::defaultOfStoreID($store_id)['allow_semi_negative'],

        ];

        return $data;
    }

    static function data_badgesV1($store_id, $user_id, $staff_id, $branch_id)
    {
        $now = Helper::getTimeNowDateTime();
        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $orders = Order::where(
            'store_id',
            $store_id
        )
            ->when($branch_id != null, function ($query) use ($branch_id) {
                $query->where(
                    'branch_id',
                    $branch_id
                );
            })
            ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                $query->whereIn('branch_id', $branch_ids);
            })
            ->where('completed_at', '>=',  $now->format('Y-m-d 00:00:00'))
            ->where('completed_at', '<', $now->format('Y-m-d 23:59:59'))
            ->get();

        //total orders
        $orders_waitting_for_progressing = 0;
        $orders_packing = 0;
        $orders_shipping = 0;
        $orders_refunds = 0; //đơn hoàn trả
        $total_orders = 0;
        $total_final_in_day = 0;
        $total_orders_in_day = 0;
        $total_orders_completed_in_day = 0;

        $total_staff_online = 0;
        $total_tally_sheet_checked = 0;
        $total_import_not_completed = 0;
        $total_product_or_discount_nearly_out_stock = 0;  //sản phẩm sắp hết hàng

        $staff_has_checkin = false;

        //Sản phẩm gần hết hàng

        $total_product_or_discount_nearly_out_stock =  0;
        // if ($branch_id != null) {
        //     $handle_products = Product::where('status', '<>', 1)
        //         ->where('store_id', $store_id)
        //         ->where('check_inventory', true)
        //         ->orderBy('id', 'desc');
        //     $arr_out_stock =  ProductUtils::arr_list_product_out_of_stock($store_id, $handle_products);
        //     $total_product_or_discount_nearly_out_stock = count($arr_out_stock);
        // }

        //ĐƠn lưu tạm
        $temporary_order = 0;
        $temporary_order = ListCart::when($branch_id != null, function ($query) use ($branch_id) {
            $query->where(
                'branch_id',
                $branch_id
            );
        })->when($user_id != null, function ($query) use ($user_id) {
            $query->where(
                'user_id',
                $user_id
            );
        })
            ->when($staff_id != null, function ($query) use ($staff_id) {
                $query->where(
                    'staff_id',
                    $staff_id
                );
            })
            ->where('edit_order_code', null)
            ->where('is_default', false)
            ->where('store_id',  $store_id)
            ->count();





        foreach ($orders as $order) {
            $total_orders += 1;
            $total_orders_in_day += 1;




            if (($order->order_status == StatusDefineCode::COMPLETED || $order->order_status == StatusDefineCode::RECEIVED_PRODUCT)   &&
                $order->payment_status == StatusDefineCode::PAID
            ) {
                $total_orders_completed_in_day++;
                $total_final_in_day += $order->total_final;
            }
            if ($order->order_status == StatusDefineCode::WAITING_FOR_PROGRESSING) {
                $orders_waitting_for_progressing++;
            }
            if ($order->order_status == StatusDefineCode::PACKING) {
                $orders_packing++;
            }
            if ($order->order_status == StatusDefineCode::SHIPPING) {
                $orders_shipping++;
            }
            if ($order->payment_status == StatusDefineCode::PAY_REFUNDS && $order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {
                $orders_refunds++;
                if ($total_final_in_day >= $order->total_final) {
                    $total_final_in_day -= $order->total_final;
                }
            }

            // if ($order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {
            //     $total_final_in_day -= $order->total_final;
            // }
        }

        //staff_has_checkin
        $date = Carbon::now('Asia/Ho_Chi_Minh');
        $date = $date->year . '-' . $date->month . '-' . $date->day;

        if ($branch_id && $staff_id != null) {
            $last_check = DateTimekeepingHistory::where('store_id', $store_id)
                ->where('branch_id', $branch_id)
                ->where('staff_id', $staff_id)
                ->orderBy('id', 'desc')
                ->where('date', $date)->first();

            if ($last_check != null) {
                $staff_has_checkin  = true;
            }
        }



        //total chat
        $lastRooms = RoomChat::where('store_id', $store_id)
            ->get();

        $chats_unread = 0;
        foreach ($lastRooms as $lastRoom) {
            if ($lastRoom->user_unread) {
                $chats_unread += $lastRoom->user_unread;
            }
        }

        //notification_unread
        $notification_unread = 0;

        $notification_unread = NotificationUser::where('store_id', $store_id)
            ->where(function ($query) use ($branch_id) {
                $query->where(
                    'branch_id',
                    $branch_id
                )->orWhere('branch_id', "=", null);
            })
            ->where('unread', true)->count();

        //total voucher
        $now = Helper::getTimeNowString();
        $voucher_total = Voucher::where('store_id', $store_id,)
            ->where('is_end', false)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->whereRaw('vouchers.is_show_voucher = true AND (vouchers.amount - vouchers.used > 0 OR vouchers.set_limit_amount = false)')
            ->count();

        //total_combo
        $now = Helper::getTimeNowDateTime();
        $combo_total = Combo::where('store_id', $store_id,)
            ->where('is_end', false)
            ->where('end_time', '>=', $now)
            ->orderBy('created_at', 'desc')
            ->count();

        //$products_discount 
        $products_discount = Discount::where('store_id', $store_id,)
            ->where('is_end', false)
            ->where('start_time', '<', $now)
            ->where('end_time', '>', $now)
            ->orderBy('created_at', 'desc')
            ->whereRaw('(discounts.amount - discounts.used > 0 OR discounts.set_limit_amount = false)')
            ->count();

        //total review

        $reviews_no_process = ProductReviews::where(
            'store_id',
            $store_id
        )->where(
            'status',
            0
        )->count();

        //Phan quyen
        $columns = Schema::getColumnListing('decentralizations');

        $list_vip = [
            "onsale" => false,
            "train" => false,
            "timekeeping" => false,
            "community" => false,
        ];

        $store = Store::where('id', $store_id)->first();

        foreach ($store->user->functions as $func) {
            if ($func == "onsale") {
                $list_vip['onsale'] = true;
            }
            if ($func == "train") {
                $list_vip['train'] = true;
            }
            if ($func == "timekeeping") {
                $list_vip['timekeeping'] = true;
            }
            if ($func == "community") {
                $list_vip['community'] = true;
            }
        }


        $user = null;
        $staff = null;

        if ($user_id != null) {
            $user = User::where('id', $user_id)->first();
        } else  if ($staff_id != null) {
            $staff = Staff::where('id', $staff_id)->where('store_id', $store_id)->first();
        }


        $decentralization = [];
        foreach ($columns as $column) {
            if (
                $column == 'id' ||
                $column == 'store_id' ||
                $column == 'created_at' ||
                $column == 'updated_at' ||
                $column == 'name' ||
                $column == 'description'
            ) {
                continue;
            }

            if ($user != null && $user->is_block == true) {
                $decentralization[$column] = false;
                continue;
            }

            if ($store->store_code == 'chinhbv' &&  ($column == 'ecommerce_products' ||  $column == 'ecommerce_connect'  || $column == 'ecommerce_orders'  ||  $column == 'ecommerce_inventory')) {
                $decentralization[$column] = true;
                continue;
            }
            if ($store->store_code != 'chinhbv' &&  ($column == 'ecommerce_products'  || $column == 'ecommerce_connect'  || $column == 'ecommerce_orders' || $column == 'ecommerce_inventory')) {
                $decentralization[$column] = false;
                continue;
            }

            if ($user_id != null) {
                $decentralization[$column] = true;
            } else  if ($staff_id != null) {
                if ($staff->decentralization == null) {
                    $decentralization[$column] = false;
                    continue;
                }
                $decentralization[$column] = filter_var($staff->decentralization->$column, FILTER_VALIDATE_BOOLEAN);
            }


            if ($column == 'onsale' && ($list_vip['onsale'] != true ||  $decentralization[$column] != true)) {
                $decentralization[$column] = false;
            }
            if ($column == 'train' && ($list_vip['train'] != true ||  $decentralization[$column]  != true)) {
                $decentralization[$column] = false;
            }
            if ($column == 'timekeeping' && ($list_vip['timekeeping'] != true ||  $decentralization[$column]  != true)) {
                $decentralization[$column] = false;
            }
            if ($column == 'community' && ($list_vip['community'] != true ||  $decentralization[$column] != true)) {
                $decentralization[$column] = false;
            }
        }

        $decentralization['sale_share'] = true;

        //User vip
        $config_user_vip = null;
        if ($store->user->is_vip == true) {
            $config_user_vip = ConfigUserVip::where('user_id', $store->user->id)->first();
        }

        $addressPickupExists = StoreAddress::where(
            'store_id',
            $store_id
        )->where('is_default_pickup', true)->first();


        //Staff online
        $staffs = Staff::where('store_id', $store_id)
            // ->when($request->branch != null, function ($query) use ($request) {
            //     $query->where(
            //         'branch_id',
            //         $branch_id
            //     );
            // })
            ->get();
        foreach ($staffs as $staffO) {
            if ($staffO->isOnline()) {
                $total_staff_online += 1;
            }
        }

        ///phiếu kiểm
        $total_tally_sheet_checked
            = TallySheet::where('store_id', $store_id)
            ->where('status', InventoryUtils::STATUS_INVENTORY_CHECKED)
            ->when($branch_id != null, function ($query) use ($branch_id) {
                $query->where(
                    'branch_id',
                    $branch_id
                );
            })
            ->count();

        ///phiếu nhập chưa xử lý
        $total_import_not_completed
            = ImportStock::where('store_id', $store_id)
            ->where('status', '!=', InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED)
            ->where('status', '!=', InventoryUtils::STATUS_IMPORT_STOCK_REFUND)
            ->where('status', '!=', InventoryUtils::STATUS_IMPORT_STOCK_CANCELED)
            ->where('status', '!=', InventoryUtils::STATUS_IMPORT_STOCK_PAUSE)
            ->when($branch_id != null, function ($query) use ($branch_id) {
                $query->where(
                    'branch_id',
                    $branch_id
                );
            })
            ->count();

        $webThemeExists = WebTheme::where(
            'store_id',
            $store->id
        )->select('domain')->first();

        $domain = ($webThemeExists->domain ?? "");
        $domain  = str_replace("http://", "", $domain);
        $domain  = str_replace("https://", "", $domain);
        $domain  = "https://" . $domain;

        $data = [
            "store_code" => $store->store_code,
            "store_name" => $store->name,
            'domain_customer' => $webThemeExists == null || empty($webThemeExists->domain) ? "https://" . $store->store_code . ".ikitech.vn" : $webThemeExists->domain,
            "is_staff" => $staff != null, // phải staff không
            "is_sale" => ($staff != null && $staff->is_sale), // phải staff không
            "total_orders" => $total_orders,
            "total_orders_in_day" => $total_orders_in_day,
            "temporary_order" => $temporary_order, //Tổng đơn lưu tạm
            "orders_waitting_for_progressing" => $orders_waitting_for_progressing, //sô đơn đang xử lý
            "orders_refunds" => $orders_refunds,
            "orders_packing" => $orders_packing,
            "orders_shipping" => $orders_shipping,
            "total_staff_online" => $total_staff_online,
            "total_import_not_completed" => $total_import_not_completed,
            "total_tally_sheet_checked" => $total_tally_sheet_checked, //tổng phiếu đang kiểm
            "total_product_or_discount_nearly_out_stock" => $total_product_or_discount_nearly_out_stock, //sản phẩm hoặc phân loại sắp hết hàng
            "chats_unread" =>  $chats_unread,
            "voucher_total" => $voucher_total,
            "combo_total" => $combo_total,
            "products_discount" => $products_discount,
            "reviews_no_process" =>  $reviews_no_process,
            "total_final_in_day" => $total_final_in_day,
            "total_orders_completed_in_day" => $total_orders_completed_in_day,
            "notification_unread" => $notification_unread,
            "decentralization" => $decentralization,
            "config_user_vip" => $config_user_vip,
            "address_pickup" =>    $addressPickupExists,
            "staff_has_checkin" =>  $staff_has_checkin,
            "allow_semi_negative" =>   GeneralSettingController::defaultOfStoreID($store_id)['allow_semi_negative'],

        ];

        return $data;
    }

    /**
     * Lấy tất cả chỉ số đếm
     * 
     * Khách hàng chat cho user
     * Nhận badges realtime
     * var socket = io("http://localhost:6441")
     * socket.on("badges:badges_user:1", function(data) {
     *   console.log(data)
     *   })
     *  1 là user_id
     */
    public function getBadges(Request $request, $id)
    {


        $store_id = $request->store != null ? $request->store->id : null;
        $user_id = $request->user !=  null ? $request->user->id : null;
        $staff_id = $request->staff !=  null ? $request->staff->id : null;
        $branch_id = $request->branch != null ? $request->branch->id : null;

        $data = BadgesController::data_badges($store_id, $user_id, $staff_id, $branch_id);



        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $data,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    public function getBadgesV1(Request $request, $id)
    {


        $store_id = $request->store != null ? $request->store->id : null;
        $user_id = $request->user !=  null ? $request->user->id : null;
        $staff_id = $request->staff !=  null ? $request->staff->id : null;
        $branch_id = $request->branch != null ? $request->branch->id : null;

        $data = BadgesController::data_badgesV1($store_id, $user_id, $staff_id, $branch_id);



        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $data,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    public  function getAmountProductNearlyOutStock(Request $request)
    {
        $total_product_or_discount_nearly_out_stock =  0;
        $store_id = $request->store != null ? $request->store->id : null;
        $branch_id = $request->branch != null ? $request->branch->id : null;
        $total_product_or_discount_nearly_out_stock = 0;

        if ($branch_id != null) {
            $handle_products = Product::where('status', '<>', 1)
                ->where('store_id', $store_id)
                ->where('check_inventory', true)
                ->orderBy('id', 'desc');
            $arr_out_stock =  ProductUtils::arr_list_product_out_of_stock($store_id, $handle_products);
            $total_product_or_discount_nearly_out_stock = count($arr_out_stock);
        }

        $data = [
            'total_product_or_discount_nearly_out_stock' => $total_product_or_discount_nearly_out_stock
        ];

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $data,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
