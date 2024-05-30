<?php

namespace App\Helper\Ecommerce;

use App\Helper\Helper;
use App\Http\Controllers\Api\User\Ecommerce\Connect\LazadaController;
use App\Models\EcommerceOrder;
use DateTime;
use Lazada\LazopClient;
use Lazada\LazopRequest;
use Lazada\UrlConstants;
use SimpleXMLElement;

class LazadaUtils
{
    static function syncAndSaveOrUpdateOrders($ecommercePlatform, $request, $page, $created_from_date, $created_to_date)
    {
        $sync_updated  = 0;
        $sync_created  = 0;
        $total_in_page = 0;

        try {
            $dataLazada = (LazadaUtils::getOrders($ecommercePlatform->token,  $page, $created_from_date, $created_to_date))->data;
        } catch (\Throwable $th) {
            if ($ecommercePlatform->store_id == null || $ecommercePlatform->seller_id == null || $ecommercePlatform->refresh_token == null) {
                return [
                    'sync_created' =>  $sync_created,
                    'sync_updated' =>  $sync_updated,
                    'total_in_page' =>  $total_in_page,
                ];
            }
            LazadaController::refresh_token($ecommercePlatform->store_id, $ecommercePlatform->seller_id, $ecommercePlatform->refresh_token);
            $dataLazada = (LazadaUtils::getOrders($ecommercePlatform->token,  $page, $created_from_date, $created_to_date))->data;
        }


        $total_in_page  += ($dataLazada->countTotal);
        foreach ($dataLazada->orders as $orderItem) {
            $data = [
                "store_id" => $request->store->id,
                "phone_number" => "",
                'order_id_in_ecommerce' => $orderItem->order_number,
                'order_code' => $orderItem->order_number,
                "order_status" => isset($orderItem->statuses[0]) ? $orderItem->statuses[0] : 'UNKNOWN',
                "payment_status" => null,
                "total_before_discount" => $orderItem->price,
                "total_after_discount" => $orderItem->price,
                "discount" => 0,
                "total_final" => $orderItem->price,
                "ship_discount_amount" => $orderItem->shipping_fee_discount_platform,
                "total_shipping_fee" => $orderItem->shipping_fee,

                "remaining_amount" => null,
                "branch_id" => null,
                "line_items_in_time" => null,

                "customer_name" => $orderItem->address_shipping->first_name . ' ' . $orderItem->address_shipping->last_name,
                "customer_country" => null,
                "customer_province" => null,
                "customer_district" => null,
                "customer_wards" => null,
                "customer_village" => null,
                "customer_postcode" => null,

                "customer_province_name" => $orderItem->address_billing->address3,
                "customer_district_name" => $orderItem->address_billing->address4,
                "customer_wards_name" => $orderItem->address_billing->address5,

                "customer_email" => null,
                "customer_phone" => $orderItem->address_billing->phone,
                "customer_address_detail" => $orderItem->address_billing->address1,
                "customer_note" => null,
                "created_by_user_id"  => null,
                "created_by_staff_id"  => null,
                "order_code_refund" => null,
                "order_from" =>  null,

                "last_time_change_order_status"  => null,
                "package_weight"   => null,
                "package_length" => null,
                "package_width"  => null,
                "created_at_ecommerce" => $orderItem->created_at,
                "updated_at_ecommerce" => $orderItem->updated_at,
                "package_height"  => null,
                "from_platform" => 'LAZADA',
                "shop_id" => $ecommercePlatform->shop_id,
                "shop_name" => $ecommercePlatform->name,
                "code" => null,
            ];

            $ecommerceOrderExists  = EcommerceOrder::where('order_code',  $orderItem->order_number)->where('store_id', $request->store->id)->first();
            if ($ecommerceOrderExists   != null) {
                $ecommerceOrderExists->update($data);
                $sync_updated += 1;
            } else {
                EcommerceOrder::create($data);
                $sync_created += 1;
            }
        }
        return [
            'sync_created' =>  $sync_created,
            'sync_updated' =>  $sync_updated,
            'total_in_page' =>  $total_in_page,
        ];
    }

    static function getOrders($token, $page, $created_from_date, $created_to_date, $limit = 20, $filter = null)
    {
        $created_after = $created_from_date == null ? (new DateTime())->format(Datetime::ATOM) : (new DateTime($created_from_date))->format(Datetime::ATOM);
        $update_after = $created_from_date == null ? (new DateTime())->format(Datetime::ATOM) : (new DateTime($created_from_date))->format(Datetime::ATOM);
        // $created_before = $created_to_date == null ? null : (new DateTime($created_to_date))->format(Datetime::ATOM);

        $c = new LazopClient(UrlConstants::$api_gateway_url_vn, EcommerceUtils::LAZADA_API_KEY, EcommerceUtils::LAZADA_API_SECRET);
        $request = new LazopRequest('/orders/get', 'GET');


        $request->addApiParam('sort_direction', 'DESC');
        $request->addApiParam('offset', ($page - 1) * 20);
        $request->addApiParam('limit', $limit);
        // $request->addApiParam('update_after', $created_after);
        // $request->addApiParam('created_after', $update_after);
        $request->addApiParam('update_after', '2020-03-30T16:00:00+08:00');
        $request->addApiParam('created_after', '2020-03-30T16:00:00+08:00');
        // $request->addApiParam('sort_by', 'updated_at');
        // $request->addApiParam('created_before', '2030-03-30T16:00:00+08:00');
        // $request->addApiParam('status', 'shipped');

        $response = json_decode($c->execute($request, $token));

        return $response;
    }

    static function getOrderDetail($ecommercePlatform, $order_id)
    {
        $responseGetOrderDetail = null;
        $responseOrderLineItem = null;
        $c = new LazopClient(UrlConstants::$api_gateway_url_vn, EcommerceUtils::LAZADA_API_KEY, EcommerceUtils::LAZADA_API_SECRET);

        $requestOrderDetail = new LazopRequest('/order/get', 'GET');
        $requestOrderDetail->addApiParam('order_id', $order_id);
        $requestOrderLineItem = new LazopRequest('/order/items/get', 'GET');
        $requestOrderLineItem->addApiParam('order_id', $order_id);

        try {
            $responseGetOrderDetail = json_decode($c->execute($requestOrderDetail, $ecommercePlatform->token));
            $responseOrderLineItem = json_decode($c->execute($requestOrderLineItem, $ecommercePlatform->token));
        } catch (\Throwable $th) {
            LazadaController::refresh_token($ecommercePlatform->store_id, $ecommercePlatform->seller_id, $ecommercePlatform->refresh_token);
            $responseGetOrderDetail = json_decode($c->execute($requestOrderDetail, $ecommercePlatform->token));
            $responseOrderLineItem = json_decode($c->execute($requestOrderLineItem, $ecommercePlatform->token));
        }
        return [
            'order_detail' => $responseGetOrderDetail,
            'order_line_item' => $responseOrderLineItem
        ];
    }

    static function  getProducts($token, $page, $limit = 20, $filter = null, $update_before = null, $create_before = null, $create_after = null, $update_after = null, $options = null, $sku_seller_list = [])
    {
        $apiKey = "116970";
        $apiSecret = "oGmTJsFyAFmO54CvV2rENmDY2svEEGpO";
        $curl = curl_init();

        $c = new LazopClient(UrlConstants::$api_gateway_url_vn, $apiKey, $apiSecret);
        $request = new LazopRequest('/products/get', 'GET');
        // $request->addApiParam('filter', 'live');
        // $request->addApiParam('update_before', '2018-01-01T00:00:00+0800');
        // $request->addApiParam('create_before', '2018-01-01T00:00:00+0800');
        $request->addApiParam('offset', ($page - 1) * 20);
        // $request->addApiParam('create_after', '2010-01-01T00:00:00+0800');
        // $request->addApiParam('update_after', '2010-01-01T00:00:00+0800');
        $request->addApiParam('limit', $limit);
        $request->addApiParam('options', '1');
        // $request->addApiParam('sku_seller_list', ' [\"39817:01:01\", \"Apple 6S Black\"]');

        return json_decode($c->execute($request, $token));

        // {
        //     "data": {
        //       "total_products": 1,
        //       "products": [
        //         {
        //           "created_time": "1678077282000",
        //           "updated_time": "1679548047000",
        //           "images": [
        //             "https://vn-live.slatic.net/p/a15bf2ac17d07939027b54430e2c6545.jpg"
        //           ],
        //           "skus": [
        //             {
        //               "Status": "active",
        //               "quantity": 2,
        //               "sellableStock": 2,
        //               "Images": [],
        //               "SellerSku": "124",
        //               "ShopSku": "2198127336_VNAMZ-10463678435",
        //               "occupiedStock": 0,
        //               "dropshippingStock": 0,
        //               "Url": "https://www.lazada.vn/-i2198127336-s10463678435.html",
        //               "saleProp": {
        //                 "color_family": "Green"
        //               },
        //               "fulfilmentStock": 0,
        //               "multiWarehouseInventories": [
        //                 {
        //                   "occupyQuantity": 0,
        //                   "quantity": 2,
        //                   "totalQuantity": 2,
        //                   "withholdQuantity": 0,
        //                   "warehouseCode": "dropshipping",
        //                   "sellableQuantity": 2
        //                 }
        //               ],
        //               "package_width": "12.00",
        //               "color_family": "Green",
        //               "package_height": "12.00",
        //               "fblWarehouseInventories": [],
        //               "special_price": 0,
        //               "price": 123121,
        //               "channelInventories": [],
        //               "package_length": "123.00",
        //               "package_weight": "123",
        //               "SkuId": 10463678435,
        //               "preorderStock": 0,
        //               "withholdingStock": 0
        //             },
        //             {
        //               "Status": "active",
        //               "quantity": 4,
        //               "sellableStock": 4,
        //               "Images": [],
        //               "SellerSku": "122",
        //               "ShopSku": "2198127336_VNAMZ-10463678434",
        //               "occupiedStock": 0,
        //               "dropshippingStock": 0,
        //               "Url": "https://www.lazada.vn/-i2198127336-s10463678434.html",
        //               "saleProp": {
        //                 "color_family": "Camel"
        //               },
        //               "fulfilmentStock": 0,
        //               "multiWarehouseInventories": [
        //                 {
        //                   "occupyQuantity": 0,
        //                   "quantity": 4,
        //                   "totalQuantity": 4,
        //                   "withholdQuantity": 0,
        //                   "warehouseCode": "dropshipping",
        //                   "sellableQuantity": 4
        //                 }
        //               ],
        //               "package_width": "12.00",
        //               "color_family": "Camel",
        //               "package_height": "12.00",
        //               "fblWarehouseInventories": [],
        //               "special_price": 0,
        //               "price": 123123,
        //               "channelInventories": [],
        //               "package_length": "123.00",
        //               "package_weight": "123",
        //               "SkuId": 10463678434,
        //               "preorderStock": 0,
        //               "withholdingStock": 0
        //             }
        //           ],
        //           "item_id": 2198127336,
        //           "trialProduct": false,
        //           "primary_category": 6396,
        //           "marketImages": [
        //             "https://filebroker-cdn.lazada.vn/kf/S09e90543428c44a6bf86e4769a60fe05H.jpg"
        //           ],
        //           "attributes": {
        //             "name": "váy tím mộng mơ",
        //             "brand": "a",
        //             "dress_shape": "Đầm suông",
        //             "clothing_material": "Chiffon",
        //             "Hazmat": "Không",
        //             "source": "asc"
        //           },
        //           "status": "Active"
        //         }
        //       ]
        //     },
        //     "code": "0",
        //     "request_id": "2101430e16796518321617734"
        //   }
        $response = curl_exec($curl);

        return json_decode($response);
    }

    static function updatePriceQuantityProduct($token, $product)
    {
        $c = new LazopClient(UrlConstants::$api_gateway_url_vn, EcommerceUtils::LAZADA_API_KEY, EcommerceUtils::LAZADA_API_SECRET);
        $request = new LazopRequest('/product/price_quantity/update');

        $request->addApiParam(
            'payload',
            '<Request>   
                <Product>     
                    <Skus>       
                        <Sku>         
                            <ItemId>' . $product->product_id_in_ecommerce . '</ItemId>         
                            <SkuId>' . $product->sku_in_ecommerce . '</SkuId>      
                            <Price>' . $product->price . '</Price>         
                            <SalePrice>' . $product->price . '</SalePrice>         
                        </Sku>     
                    </Skus>   
                </Product> 
            </Request>'
        );

        return json_decode($c->execute($request, $token));
    }

    static function deleteProduct($token, $products)
    {
        $c = new LazopClient(UrlConstants::$api_gateway_url_vn, EcommerceUtils::LAZADA_API_KEY, EcommerceUtils::LAZADA_API_SECRET);
        $request = new LazopRequest('/product/remove');

        $listIdRemove = [];
        foreach ($products as $key => $productItem) {
            $customerIdRemove = "SkuId_" . $productItem->product_id_in_ecommerce . "_" . $productItem->sku;
            array_push($listIdRemove, $customerIdRemove);
        }

        $request->addApiParam(
            'seller_sku_list',
            json_encode($listIdRemove)
        );

        return json_decode($c->execute($request, $token));
    }

    static function getAllInventory($token)
    {
        $c = new LazopClient(UrlConstants::$api_gateway_url_vn, EcommerceUtils::LAZADA_API_KEY, EcommerceUtils::LAZADA_API_SECRET);
        $request = new LazopRequest('/rc/warehouse/detail/get');

        return json_decode($c->execute($request, $token));
    }
}
