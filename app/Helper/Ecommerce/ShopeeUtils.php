<?php

namespace App\Helper\Ecommerce;

use App\Http\Controllers\Api\User\Ecommerce\Connect\ShopeeController;
use App\Models\EcommerceOrder;
use Carbon\Carbon;
use Illuminate\Support\Arr;

use function PHPUnit\Framework\isEmpty;

class ShopeeUtils
{
    static function syncAndSaveOrUpdateOrders($ecommercePlatform, $request, $page, $created_from_date, $created_to_date)
    {
        $sync_updated  = 0;
        $sync_created  = 0;
        $total_in_page = 0;

        $dataShopee = (ShopeeUtils::getOrders($ecommercePlatform->token, $ecommercePlatform->shop_id, $page, $created_from_date, $created_to_date));
        $total_in_page  += (count($dataShopee->order_list));

        foreach ($dataShopee->order_list as $item) {
            $data = [
                "store_id" => $request->store->id,
                "phone_number" => "",
                'order_id_in_ecommerce' => $item->order_sn,
                'order_code' => $item->order_sn,
                "order_status" => $item->order_status,
                "payment_status" => $item->order_status,
                "total_before_discount" => $item->total_amount,
                "total_after_discount" => $item->total_amount,
                "discount" => null,
                "total_final" => $item->total_amount,

                "total_shipping_fee" => $item->estimated_shipping_fee,
                "remaining_amount" => null,
                "branch_id" => null,
                "line_items_in_time" => json_encode($item->item_list),

                "customer_name" => $item->recipient_address->name,
                "customer_country" => null,
                "customer_province" => null,
                "customer_district" => null,
                "customer_wards" => null,
                "customer_village" => null,
                "customer_postcode" => null,

                "customer_province_name" => $item->recipient_address->state,
                "customer_district_name" => $item->recipient_address->city,
                "customer_wards_name" => $item->recipient_address->district,


                "customer_email" => null,
                "customer_phone" => $item->recipient_address->phone,
                "customer_address_detail" => $item->recipient_address->full_address,
                "customer_note" => null,
                "created_by_user_id"  => null,
                "created_by_staff_id"  => null,
                "order_code_refund" => null,
                "order_from" =>  null,

                "last_time_change_order_status"  => null,
                "package_weight"   => null,
                "package_length" => null,
                "package_width"  => null,
                "package_height"  => null,
                "from_platform" => 'SHOPEE',
                "created_at_ecommerce" => date('y-m-d H:i:s', $item->create_time),
                "updated_at_ecommerce" => date('y-m-d H:i:s', $item->update_time),
                "shop_id" => $ecommercePlatform->shop_id,
                "shop_name" => $ecommercePlatform->name,

                "code" => null,
            ];

            $ecommerceOrderExists  = EcommerceOrder::where('order_code',  $item->order_sn)->where('store_id', $request->store->id)->first();
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

    static function getOrders($token, $shopId, $page = 1, $created_from_date, $created_to_date, $limit = 20)
    {
        $curl = curl_init();
        $created_from_date = strtotime($created_from_date) ?? time();
        $created_to_date = strtotime($created_to_date) ?? time();

        $path = '/api/v2/order/get_order_list';
        $timestamp = time();
        $sign = ShopeeController::generateSign($path, $timestamp, $token, $shopId);

        $parameterUrl = array(
            'access_token' => $token,
            'cursor' => ($page - 1) * $limit,
            // 'order_status' => 'READY_TO_SHIP',
            'page_size' => $limit,
            'partner_id' => EcommerceUtils::SHOPEE_LIVE_PARTER_ID,
            'response_optional_fields' => 'order_status',
            'time_range_field' => 'create_time',
            'time_from' => $created_from_date,
            'time_to' => $created_to_date,
            'shop_id' => $shopId,
            'sign' => $sign,
            'timestamp' => $timestamp
        );

        $url = EcommerceUtils::SHOPEE_HOST . $path . '?' . http_build_query($parameterUrl);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $resOrderList = json_decode(curl_exec($curl));

        curl_close($curl);

        if (isset($resOrderList->response)) {
            $arrIdOrder = Arr::pluck($resOrderList->response->order_list, 'order_sn');
            $strIdOrder = implode(',', $arrIdOrder);

            $path = '/api/v2/order/get_order_detail';
            $sign = ShopeeController::generateSign($path, $timestamp, $token, $shopId);

            $parameterUrl = array(
                'access_token' => $token,
                'partner_id' => EcommerceUtils::SHOPEE_LIVE_PARTER_ID,
                'shop_id' => $shopId,
                'sign' => $sign,
                'timestamp' => $timestamp,
            );

            $arrResOptionalField = implode('%2C', [
                "buyer_user_id", "buyer_username", "estimated_shipping_fee", "recipient_address",
                "actual_shipping_fee", "goods_to_declare", "note", "note_update_time", "item_list",
                "pay_time", "dropshipper", "dropshipper_phone", "split_up", "buyer_cancel_reason",
                "cancel_by", "cancel_reason", "actual_shipping_fee_confirmed", "buyer_cpf_id",
                "fulfillment_flag", "pickup_done_time", "package_list", "shipping_carrier",
                "payment_method", "total_amount", "buyer_username", "invoice_data", "checkout_shipping_carrier", "reverse_shipping_fee",
                "order_chargeable_weight_gram", "edt", "prescription_images", "prescription_check_status"
            ]);

            $url = EcommerceUtils::SHOPEE_HOST . $path . '?' . http_build_query($parameterUrl) . '&order_sn_list=' . $strIdOrder . '&response_optional_fields=' . $arrResOptionalField;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $resOrderDetail = json_decode(curl_exec($curl));
            curl_close($curl);

            if (isset($resOrderDetail->response)) {
                return $resOrderDetail->response;
            }
            throw new \ErrorException($resOrderDetail);
        } else {
            throw new \ErrorException($resOrderList);
        }

        return $resOrderList;
    }

    static function getProducts($ecommercePlatform, $page = 1, $limit = 20)
    {
        $curl = curl_init();
        $path = '/api/v2/product/get_item_list';
        $timestamp = time();
        $sign = ShopeeController::generateSign($path, $timestamp, $ecommercePlatform->token, $ecommercePlatform->shop_id);

        $parameterUrl = array(
            'access_token' => $ecommercePlatform->token,
            'item_status' => 'NORMAL',
            'offset' => ($page - 1) * $limit,
            'page_size' => $limit,
            'partner_id' => EcommerceUtils::SHOPEE_LIVE_PARTER_ID,
            'shop_id' => $ecommercePlatform->shop_id,
            'sign' => $sign,
            'timestamp' => $timestamp
        );

        $url = EcommerceUtils::SHOPEE_HOST . $path . '?' . http_build_query($parameterUrl);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $listIdProduct = json_decode($response);

        if (isset($listIdProduct->response)) {
            $listIdProduct = implode(',', Arr::pluck($listIdProduct->response->item, 'item_id'));

            $path = '/api/v2/product/get_item_base_info';
            $sign = ShopeeController::generateSign($path, $timestamp, $ecommercePlatform->token, $ecommercePlatform->shop_id);

            $parameterUrl = array(
                'access_token' => $ecommercePlatform->token,
                'need_complaint_policy' => true,
                'need_tax_info' => true,
                'partner_id' => EcommerceUtils::SHOPEE_LIVE_PARTER_ID,
                'shop_id' => $ecommercePlatform->shop_id,
                'sign' => $sign,
                'timestamp' => $timestamp,
            );

            $url = EcommerceUtils::SHOPEE_HOST . $path . '?' . http_build_query($parameterUrl) . '&item_id_list=' . $listIdProduct;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = json_decode(curl_exec($curl));
            curl_close($curl);

            if (isset($response->response)) {
                foreach ($response->response->item_list as $keyProductItem => $productItem) {
                    if (!isset($productItem->price_info)) {
                        $curl = curl_init();

                        $path = '/api/v2/product/get_model_list';
                        $sign = ShopeeController::generateSign($path, $timestamp, $ecommercePlatform->token, $ecommercePlatform->shop_id);

                        $parameterUrl = array(
                            'access_token' => $ecommercePlatform->token,
                            'need_complaint_policy' => true,
                            'need_tax_info' => true,
                            'partner_id' => EcommerceUtils::SHOPEE_LIVE_PARTER_ID,
                            'shop_id' => $ecommercePlatform->shop_id,
                            'sign' => $sign,
                            'timestamp' => $timestamp,
                        );

                        $url = EcommerceUtils::SHOPEE_HOST . $path . '?' . http_build_query($parameterUrl) . '&item_id=' . $productItem->item_id;

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/json'
                            ),
                        ));

                        $responseModelList = json_decode(curl_exec($curl));
                        curl_close($curl);

                        $response->response->item_list[$keyProductItem]->variation_item = $responseModelList->response;
                    }
                }
            }
            if (isset($response->response)) {
                return $response->response;
            }

            throw new \ErrorException($response);
        } else {
            throw new \ErrorException($response);
        }
    }

    static function updatePriceProduct($token, $product)
    {
        $timestamp = time();
        $path = "/api/v2/product/update_price";
        $body = array(
            "item_id" => (int)$product->product_id_in_ecommerce,
            "price_list" => [
                [                // "model_id" => "",
                    "original_price" => $product->price
                ]
            ]
        );

        $sign = ShopeeController::generateSign($path, $timestamp, $token, $product->shop_id);
        $url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s&shop_id=%s&access_token=%s", EcommerceUtils::SHOPEE_HOST, $path, EcommerceUtils::SHOPEE_LIVE_PARTER_ID, $timestamp, $sign, $product->shop_id, $token);

        $c = curl_init($url);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($c);

        $response = json_decode($resp);

        return $response;

        // if ($info["http_code"] != 200) {
        //     throw new \ErrorException((json_decode($response)->errors[0]));
        // }
        // return json_decode($response);
    }

    static function getOrderDetail($ecommercePlatformExists, $order_code)
    {
        $timestamp = time();

        $path = '/api/v2/order/get_order_detail';
        $sign = ShopeeController::generateSign($path, $timestamp, $ecommercePlatformExists->token, $ecommercePlatformExists->shop_id);

        $parameterUrl = array(
            'access_token' => $ecommercePlatformExists->token,
            'partner_id' => EcommerceUtils::SHOPEE_LIVE_PARTER_ID,
            'shop_id' => $ecommercePlatformExists->shop_id,
            'sign' => $sign,
            'timestamp' => $timestamp,
        );

        $arrResOptionalField = implode('%2C', [
            "buyer_user_id", "buyer_username", "estimated_shipping_fee", "recipient_address",
            "actual_shipping_fee", "goods_to_declare", "note", "note_update_time", "item_list",
            "pay_time", "dropshipper", "dropshipper_phone", "split_up", "buyer_cancel_reason",
            "cancel_by", "cancel_reason", "actual_shipping_fee_confirmed", "buyer_cpf_id",
            "fulfillment_flag", "pickup_done_time", "package_list", "shipping_carrier",
            "payment_method", "total_amount", "buyer_username", "invoice_data", "checkout_shipping_carrier", "reverse_shipping_fee",
            "order_chargeable_weight_gram", "edt", "prescription_images", "prescription_check_status"
        ]);

        $url = EcommerceUtils::SHOPEE_HOST . $path . '?' . http_build_query($parameterUrl) . '&order_sn_list=' . $order_code . '&response_optional_fields=' . $arrResOptionalField;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $resOrderDetail = json_decode(curl_exec($curl));
        curl_close($curl);

        if (isset($resOrderDetail->response)) {
            return $resOrderDetail->response;
        }

        return $resOrderDetail;
    }

    static function getAllInventory($ecommercePlatformExists)
    {
        $curl = curl_init();
        $timestamp = time();

        $path = '/api/v2/shop/get_warehouse_detail';
        $sign = ShopeeController::generateSign($path, $timestamp, $ecommercePlatformExists->token, $ecommercePlatformExists->shop_id);

        $parameterUrl = array(
            'access_token' => $ecommercePlatformExists->token,
            'partner_id' => EcommerceUtils::SHOPEE_LIVE_PARTER_ID,
            'shop_id' => $ecommercePlatformExists->shop_id,
            'sign' => $sign,
            'region' => "ID",
            'timestamp' => $timestamp,
        );

        $url = EcommerceUtils::SHOPEE_HOST . $path . '?' . http_build_query($parameterUrl);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if (!isset($response->response)) {
            throw new \Exception($response->error . " - " . $response->message);
        }

        return $response;
    }
}
