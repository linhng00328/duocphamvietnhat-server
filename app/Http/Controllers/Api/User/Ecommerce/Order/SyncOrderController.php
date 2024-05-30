<?php

namespace App\Http\Controllers\Api\User\Ecommerce\Order;

use App\Helper\Ecommerce\EcommerceUtils;
use App\Helper\Ecommerce\LazadaUtils;
use App\Helper\Ecommerce\ShopeeUtils;
use App\Helper\Ecommerce\TikiUtils;
use App\Helper\Helper;
use App\Helper\StringUtils;
use App\Http\Controllers\Api\User\Ecommerce\Connect\LazadaController;
use App\Http\Controllers\Api\User\Ecommerce\Connect\ShopeeController;
use App\Http\Controllers\Controller;
use App\Models\ecommerce_line_items;
use App\Models\EcommerceLineItem;
use App\Models\EcommercePlatform;
use App\Models\EcommerceOrder;
use App\Models\MsgCode;
use App\Models\Store;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group  User/Kết nối sàn đơn hàng
 */
class SyncOrderController extends Controller
{

    /**
     * Danh sách đơn hàng
     * 
     * @queryParam shop_ids  Id của shop
     * @queryParam sku  sku của đơn hàng
     * @queryParam name  tên đơn hàng
     * @queryParam order_statuses  trạng thái đơn hàng
     * @queryParam payment_statuses  trạng thái đơn hàng
     * 
     */
    public function getAllOrderEcommerce(Request $request)
    {
        $shop_ids = request("shop_ids") == null ? [] : explode(',', request("shop_ids"));
        $pages = $request->page ?? 1;
        $order_statuses = request("order_statuses") == null ? [] : explode(',', request("order_statuses"));
        $payment_statuses = request("payment_statuses") == null ? [] : explode(',', request("payment_statuses"));

        $name = request('name');
        $sku = request('sku');

        $created_from_date = request('created_from_date');
        $created_to_date = request('created_to_date');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');


        $created_from_date = $carbon->parse($created_from_date);
        $created_to_date = $carbon->parse($created_to_date);


        $dateCreateFrom = $created_from_date->toDateString() . ' 00:00:00';
        $dateCreateTo = $created_to_date->toDateString() . ' 23:59:59';

        $DeferenceInDays = Carbon::parse($created_to_date)->diffInDays($created_from_date);
        if ($DeferenceInDays <= 7) {

            $ecommercePlatforms = EcommercePlatform::where('store_id',  $request->store->id)
                ->whereIn('shop_id',   $shop_ids)
                ->get();

            foreach ($ecommercePlatforms as    $ecommercePlatform) {
                if ($ecommercePlatform != null &&  $ecommercePlatform->platform == "TIKI" && $ecommercePlatform->type_sync_orders == EcommerceUtils::TYPE_SYNC_ORDERS_AUTO) {
                    $data =   TikiUtils::syncAndSaveOrUpdateOrders($ecommercePlatform, $request, $pages, $created_from_date, $created_to_date);
                }
                if ($ecommercePlatform != null &&  $ecommercePlatform->platform == "LAZADA"  && $ecommercePlatform->type_sync_orders == EcommerceUtils::TYPE_SYNC_ORDERS_AUTO) {
                    $data = LazadaUtils::syncAndSaveOrUpdateOrders($ecommercePlatform, $request, $pages, $created_from_date, $created_to_date);
                }
                if ($ecommercePlatform != null &&  $ecommercePlatform->platform == "SHOPEE"  && $ecommercePlatform->type_sync_orders == EcommerceUtils::TYPE_SYNC_ORDERS_AUTO) {
                    $data = ShopeeUtils::syncAndSaveOrUpdateOrders($ecommercePlatform, $request, $pages, $created_from_date, $created_to_date);
                }
            }
        }

        $dataDB = EcommerceOrder::where('store_id', $request->store->id)->when($sku  != null, function ($query) use ($sku) {
            $query->where('sku', 'like', '%' . $sku . '%');
        })->when($name  != null, function ($query) use ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        })
            ->when(request("created_from_date")  != null, function ($query) use ($dateCreateFrom) {
                $query->where('created_at_ecommerce', '>=', $dateCreateFrom);
            })
            ->when(request("created_to_date")  != null, function ($query) use ($dateCreateTo) {
                $query->where('created_at_ecommerce', '<=', $dateCreateTo);
            })
            ->whereIn('shop_id',   $shop_ids);



        $list = $dataDB->when(count($order_statuses) > 0, function ($query) use ($order_statuses) {
            $query->whereIn('order_status', $order_statuses);
        })
            ->when(count($payment_statuses) > 0, function ($query) use ($payment_statuses) {
                $query->whereIn('payment_status', $payment_statuses);
            })
            ->orderBy('created_at_ecommerce', 'desc')
            ->paginate(request('limit') == null ? 20 : request('limit'));


        $custom = collect(
            [
                'new_orders_count' => $dataDB->whereIn('order_status', ['queueing', 'handover_to_partner', 'processing', 'pending'])->count(),
                'ship_orders_count' => $dataDB->whereIn('order_status', ['successful_delivery', 'delivered', 'packaging', 'picking', 'shipping', 'ready_to_ship', 'finished_packing', 'topack', 'toship'])->count(),
                'problem_orders_count' => $dataDB->whereIn('order_status', ['canceled', 'closed', 'holded', 'returned', 'failed', 'lost'])->count(),
                "payment_orders_count" => $dataDB->whereIn('order_status', ['waiting_payment', 'paid', 'payment_review', 'unpaid'])->count(),
                "success_orders_count" => $dataDB->whereIn('order_status', ['complete', 'shipped'])->count(),
            ]
        );

        $data = $custom->merge($list);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data
        ], 200);
    }


    /**
     * Đồng bộ đơn hàng từ các sàn
     * 
     * @bodyParam shop_ids  Danh sách id của shop
     * @bodyParam page 
     */

    public function syncOrderEcommerce(Request $request)
    {
        $now = Helper::getTimeNowDateTime();
        $created_from_date = request('created_from_date');
        $created_to_date = request('created_to_date');

        if ($request->page == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Thiếu page",
            ], 400);
        }

        $shop_ids = is_array($request->shop_ids) ?  $request->shop_ids : array();
        $ecommercePlatforms = EcommercePlatform::where('store_id',  $request->store->id)
            ->when(count($shop_ids) > 0, function ($query) use ($shop_ids) {
                $query->whereIn('shop_id',   $shop_ids);
            })
            ->get();

        $total_in_page = 0;
        $sync_updated = 0;
        $sync_created = 0;

        foreach ($ecommercePlatforms as $ecommercePlatform) {
            if ($ecommercePlatform->platform == "TIKI") {
                try {
                    $data =   TikiUtils::syncAndSaveOrUpdateOrders($ecommercePlatform, $request, $request->page, $created_from_date, $created_to_date);
                    $total_in_page =  $data['total_in_page'];
                    $sync_updated =  $data['sync_updated'];
                    $sync_created =  $data['sync_created'];
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            }
            if ($ecommercePlatform->platform == "LAZADA") {
                try {
                    $data =   LazadaUtils::syncAndSaveOrUpdateOrders($ecommercePlatform, $request, $request->page, $created_from_date, $created_to_date);
                    $total_in_page =  $data['total_in_page'];
                    $sync_updated =  $data['sync_updated'];
                    $sync_created =  $data['sync_created'];
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            }
            if ($ecommercePlatform->platform == "SHOPEE") {
                try {
                    if ($ecommercePlatform->expiry_token < $now) {
                        ShopeeController::refresh_token($ecommercePlatform);
                    }
                    $data =   ShopeeUtils::syncAndSaveOrUpdateOrders($ecommercePlatform, $request, $request->page, $created_from_date, $created_to_date);
                    $total_in_page =  $data['total_in_page'];
                    $sync_updated =  $data['sync_updated'];
                    $sync_created =  $data['sync_created'];
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                'total_in_page' => $total_in_page,
                'sync_created' => $sync_created,
                'sync_updated' => $sync_updated,
            ],
        ], 200);
    }



    /**
     * Cập nhật đơn hàng
     * 
     * @bodyParam shop_id  Danh sách id của shop
     * @urlParam order_id Order id order_id_in_ecommerce
     * @bodyParam total_shipping_fee double phí ship
     * @bodyParam customer_province_name string tên tỉnh
     * @bodyParam customer_district_name string tên quận huyện
     * @bodyParam customer_wards_name string tên phường xã
     * @bodyParam customer_name string tên khách hàng
     * @bodyParam customer_phone string số điện thoại khách hàng
     * @bodyParam customer_address_detail địa chỉ chi tiết
     * @bodyParam customer_note string note
     * @bodyParam created_by_staff_id int staff id
     * @bodyParam created_by_user_id int user id
     * @bodyParam line_items danh sách item 
     * @bodyParam product_id_in_ecommerce
     * @bodyParam sku string sku (in lineitem)
     * @bodyParam name string tên (in lineitem)
     * @bodyParam name_distribute string tên phân loại (in lineitem)
     * @bodyParam item_price double giá (in lineitem)
     * @bodyParam quantity int số lượng (in lineitem)
     * @bodyParam thumbnail string ảnh  (in lineitem)
     * 
     */

    public function editOrderEcommerce(Request $request)
    {

        $ecommerceOrderExists = EcommerceOrder::where('order_id_in_ecommerce', $request->order_id)->where('store_id', $request->store->id)->first();

        if ($ecommerceOrderExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 400);
        }


        try {

            $data = [
                'total_shipping_fee' => $request->total_shipping_fee,
                'customer_province_name' => $request->customer_province_name,
                'customer_district_name' => $request->customer_district_name,
                'customer_wards_name' => $request->customer_wards_name,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address_detail' => $request->customer_address_detail,
                'customer_note' => $request->customer_note,
                'created_by_staff_id' => $request->created_by_staff_id,
                'created_by_user_id' => $request->created_by_user_id,
            ];

            $listSkus = [];

            foreach ($request->line_items as $item) {
                array_push($listSkus,  $item['sku']);
                $lineAddOrUpdate = [
                    'store_id' => $request->store->id,
                    "order_item_id_in_ecommerce" => $request['order_id'],
                    "product_id_in_ecommerce" => $item['product_id_in_ecommerce'],
                    "order_id_in_ecommerce" => $request->order_id,
                    "sku" => $item['sku'],
                    "name" => $item['name'],
                    "name_distribute" => $item['name_distribute'],
                    "item_price" => $item['item_price'],
                    "quantity" => $item['quantity'],
                    "thumbnail" => $item['thumbnail'],
                ];

                $lineItemExists = EcommerceLineItem::where('sku', $item['sku'])
                    ->where('order_id_in_ecommerce', $request->order_id)
                    ->where('store_id', $request->store->id)
                    ->first();

                if ($lineItemExists  != null) {
                    $lineItemExists->update($lineAddOrUpdate);
                } else {
                    EcommerceLineItem::create($lineAddOrUpdate);
                }
            }

            EcommerceLineItem::where('order_id_in_ecommerce', $request->order_id)
                ->whereNotIn('sku',  $listSkus)->delete();

            $ecommerceOrderExists->update($data);
        } catch (Exception $e) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => $e->getMessage(),
            ], 400);
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $ecommerceOrderExists
        ], 200);
    }

    /**
     * Lấy thông tin đơn hàng chi tiết 
     * 
     * @bodyParam shop_id  Danh sách id của shop
     * @urlParam order_id Order id
     * 
     * 
     */

    public function getOrderDetailEcommerce(Request $request)
    {
        $now = Helper::getTimeNowCarbon();
        $ecommerceOrderExists = EcommerceOrder::where('order_id_in_ecommerce', $request->order_id)->where('store_id', $request->store->id)->first();

        if ($ecommerceOrderExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 400);
        }

        $shop_id =  $ecommerceOrderExists->shop_id;
        $ecommerceOrderDetailExists = EcommerceLineItem::where('order_id_in_ecommerce', $request->order_id)->where('store_id', $request->store->id)->get();

        if ($ecommerceOrderDetailExists->isEmpty()) {
            $ecommercePlatformExists = EcommercePlatform::where('store_id',  $request->store->id)
                ->where('shop_id',  $shop_id)
                ->first();
            if ($ecommercePlatformExists != null) {
                if ($ecommercePlatformExists->platform == "LAZADA") {
                    try {
                        $resOrderDetail = LazadaUtils::getOrderDetail($ecommercePlatformExists, $request->order_id);
                        if ($resOrderDetail['order_line_item'] != null) {
                            $newListOrderLineItem = [];

                            $checkLineItemExists = DB::table('ecommerce_line_items')->where([
                                ['store_id', $ecommercePlatformExists->store_id],
                                ['shop_id', $ecommercePlatformExists->shop_id],
                                ['order_id', $request->order_id]
                            ])->exists();

                            if (!$checkLineItemExists) {
                                foreach ($resOrderDetail['order_line_item'] as $keyOrderListItem => $orderListItem) {
                                    $newListOrderLineItem = [];

                                    foreach ($orderListItem as $keyOrderItem => $orderItem) {
                                        if (!isset($newListOrderLineItem[$orderItem->sku])) {

                                            $newListOrderLineItem[$orderItem->sku] = [
                                                'store_id' => $request->store->id,
                                                'shop_id' => $ecommercePlatformExists->shop_id,
                                                'order_id' => $orderItem->order_id,
                                                'product_id_in_ecommerce' => $orderItem->product_id,
                                                'order_id_in_ecommerce' => $orderItem->order_id,
                                                'sku' => $orderItem->sku,
                                                'name' => $orderItem->name,
                                                'name_distribute' => $orderItem->variation,
                                                'customer_id' => "",
                                                'phone_number' => "",
                                                'device_id' => "",
                                                'product_id' => $orderItem->product_id,
                                                'total_refund' => "",
                                                'before_discount_price' => $orderItem->item_price,
                                                'item_price' => $orderItem->item_price,
                                                'cost_of_capital' => 0,
                                                'quantity' => 1,
                                                'is_refund' => null,
                                                'branch_id' => null,
                                                'is_bonus' => null,
                                                'has_edit_item_price' => null,
                                                'bonus_product_name' => null,
                                                'created_at' => $now,
                                                'updated_at' => $now
                                            ];
                                        } else {
                                            $newListOrderLineItem[$orderItem->sku]['quantity'] += 1;
                                        }
                                    }
                                    $newListOrderLineItem = array_values($newListOrderLineItem);
                                    EcommerceLineItem::insert($newListOrderLineItem);
                                }
                            }
                        }
                    } catch (Exception $e) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::ERROR[0],
                            'msg' => $e->getMessage(),
                        ], 400);
                    }
                }

                if ($ecommercePlatformExists->platform == "TIKI") {
                    $resOrderDetail = TikiUtils::getOrderDetail($ecommercePlatformExists->token, $request->order_id);

                    if ($resOrderDetail->items != null) {
                        $newListOrderLineItem = [];

                        $checkLineItemExists = DB::table('ecommerce_line_items')->where([
                            ['store_id', $ecommercePlatformExists->store_id],
                            ['shop_id', $ecommercePlatformExists->shop_id],
                            ['order_id', $request->order_id]
                        ])->exists();


                        if (!$checkLineItemExists) {
                            foreach ($resOrderDetail->items as $keyOrderListItem => $orderListItem) {

                                $newListOrderLineItem = [
                                    'store_id' => $request->store->id,
                                    'shop_id' => $ecommercePlatformExists->shop_id,
                                    'order_id' => $request->order_id,
                                    'product_id_in_ecommerce' => $orderListItem->product->master_id,
                                    'order_id_in_ecommerce' => $request->order_id,
                                    "order_item_id_in_ecommerce" => $orderListItem->id,
                                    'sku' => $orderListItem->product->sku,
                                    'name' => $orderListItem->product->name,
                                    'name_distribute' => null,
                                    'customer_id' => "",
                                    'phone_number' => "",
                                    'device_id' => "",
                                    'product_id' => $orderListItem->product->master_id,
                                    'total_refund' => "",
                                    'before_discount_price' => $orderListItem->price,
                                    'item_price' => $orderListItem->price,
                                    'cost_of_capital' => 0,
                                    'quantity' =>  $orderListItem->qty,
                                    'is_refund' => null,
                                    'branch_id' => null,
                                    'is_bonus' => null,
                                    'has_edit_item_price' => null,
                                    'bonus_product_name' => null,
                                    "thumbnail" =>  $orderListItem->product->thumbnail,
                                    'created_at' => $now,
                                    'updated_at' => $now
                                ];

                                EcommerceLineItem::create($newListOrderLineItem);
                            }
                        }
                    }
                }

                if ($ecommercePlatformExists->platform == "SHOPEE") {
                    try {
                        $resOrderDetail = ShopeeUtils::getOrderDetail($ecommercePlatformExists, $request->order_id);
                        if (isset($resOrderDetail->order_list[0]->item_list[0])) {
                            $resOrderDetail = $resOrderDetail->order_list[0]->item_list;
                            $newListOrderLineItem = [];

                            $checkLineItemExists = DB::table('ecommerce_line_items')->where([
                                ['store_id', $ecommercePlatformExists->store_id],
                                ['shop_id', $ecommercePlatformExists->shop_id],
                                ['order_id', $request->order_id]
                            ])->exists();

                            if (!$checkLineItemExists) {
                                foreach ($resOrderDetail as $keyOrderItem => $orderItem) {
                                    $newListOrderLineItem = [
                                        'store_id' => $request->store->id,
                                        'shop_id' => $ecommercePlatformExists->shop_id,
                                        'order_id' => $request->order_id,
                                        'product_id_in_ecommerce' => $orderItem->item_id,
                                        'order_id_in_ecommerce' => $request->order_id,
                                        "order_item_id_in_ecommerce" => $orderItem->order_item_id,
                                        'sku' => $orderItem->item_sku,
                                        'name' => $orderItem->item_name,
                                        'name_distribute' => null,
                                        'customer_id' => "",
                                        'phone_number' => "",
                                        'device_id' => "",
                                        'product_id' => $orderItem->item_id,
                                        'total_refund' => "",
                                        'before_discount_price' => $orderItem->model_original_price,
                                        'item_price' => $orderItem->model_original_price,
                                        'cost_of_capital' => 0,
                                        'quantity' =>  $orderItem->model_quantity_purchased,
                                        'is_refund' => null,
                                        'branch_id' => null,
                                        'is_bonus' => null,
                                        'has_edit_item_price' => null,
                                        'bonus_product_name' => null,
                                        "thumbnail" =>  $orderItem->image_info->image_url,
                                        'created_at' => $now,
                                        'updated_at' => $now
                                    ];

                                    EcommerceLineItem::create($newListOrderLineItem);
                                }
                            }
                        }
                    } catch (Exception $e) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::ERROR[0],
                            'msg' => $e->getMessage(),
                        ], 400);
                    }
                }
            }
            $ecommerceOrderDetailExists = EcommerceLineItem::where('order_id_in_ecommerce', $request->order_id)->where('store_id', $request->store->id)->get();
        }

        $ecommerceOrderExists->line_times =   $ecommerceOrderDetailExists;
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $ecommerceOrderExists
        ], 200);
    }
}
