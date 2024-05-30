<?php

namespace App\Helper\Ecommerce;

use App\Models\EcommerceOrder;
use Carbon\Carbon;

use function PHPUnit\Framework\isEmpty;

class TikiUtils
{

    static function syncAndSaveOrUpdateOrders($ecommercePlatform, $request, $page, $created_from_date, $created_to_date)
    {
        $sync_updated  = 0;
        $sync_created  = 0;
        $total_in_page = 0;

        $dataTiki = (TikiUtils::getOrders($ecommercePlatform->token, $page, $created_from_date, $created_to_date));
        $total_in_page  += (count($dataTiki->data));
        foreach ($dataTiki->data as $item) {
            $data = [
                "store_id" => $request->store->id,

                "phone_number" => "",
                'order_id_in_ecommerce' => $item->code,
                'order_code' => $item->code,
                "order_status" => $item->status,
                "payment_status" => $item->status,
                "total_before_discount" => $item->status,
                "total_after_discount" => $item->status,
                "discount" => $item->status,
                "total_final" => $item->status,

                "remaining_amount" => $item->status,
                "branch_id" => null,
                "line_items_in_time" => json_encode($item->items),

                "customer_name" => $item->billing_address->full_name,
                "customer_country" => null,
                "customer_province" => null,
                "customer_district" => null,
                "customer_wards" => null,
                "customer_village" => null,
                "customer_postcode" => null,

                "customer_province_name" => $item->billing_address->region,
                "customer_district_name" => $item->billing_address->district,
                "customer_wards_name" => $item->billing_address->ward,


                "customer_email" => null,
                "customer_phone" => null,
                "customer_address_detail" => null,
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
                "from_platform" => 'TIKI',
                "created_at_ecommerce" => $item->created_at,
                "updated_at_ecommerce" => $item->updated_at,
                "shop_id" => $ecommercePlatform->shop_id,
                "shop_name" => $ecommercePlatform->name,

                "code" => null,
            ];

            $ecommerceOrderExists  = EcommerceOrder::where('order_code',  $item->code)->where('store_id', $request->store->id)->first();
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

    static function getOrders($token, $page, $created_from_date, $created_to_date)
    {
        $curl = curl_init();

        $created_from_date = request('created_from_date');
        $created_to_date = request('created_to_date');



        $created_from_date = $created_from_date == null ? null : $created_from_date;
        $created_from_date = (Carbon::parse($created_from_date));
        $created_from_date =  $created_from_date->toDateString() . " 00:00:00";

        $created_from_date = str_replace(" ", "%20", $created_from_date);
        $created_from_date = str_replace(":", "%3A", $created_from_date);


        $created_to_date = $created_to_date == null ? null : $created_to_date;
        $created_to_date = (Carbon::parse($created_to_date));
        $created_to_date =  $created_to_date->toDateString() . " 23:59:59";
        $created_to_date = str_replace(" ", "%20", $created_to_date);
        $created_to_date = str_replace(":", "%3A", $created_to_date);

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.tiki.vn/integration/v2/orders?page=' . $page .
                '&created_at%7Cdesc' .
                (empty($created_from_date) ? "" : '&created_from_date=' . $created_from_date) .
                (empty($created_from_date) ? "" : '&created_to_date=' . $created_to_date),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
            ),
        ));

        // {
        //     "data": [
        //         {
        //             "id": 176538853,
        //             "code": "265589080",
        //             "fulfillment_type": "dropship",
        //             "status": "queueing",
        //             "items": [
        //                 {
        //                     "id": 281329089,
        //                     "product": {
        //                         "id": 248956418,
        //                         "type": "simple",
        //                         "super_id": 0,
        //                         "master_id": 248956417,
        //                         "sku": "7121107567807",
        //                         "name": "Áo Thun KXGF1",
        //                         "catalog_group_name": "Thời trang",
        //                         "inventory_type": "backorder",
        //                         "imeis": [],
        //                         "serial_numbers": [],
        //                         "thumbnail": "https://salt.tikicdn.com/cache/280x280/ts/product/3f/7f/bc/c7a496c3dd5663c9e9997197565d37e0.jpeg",
        //                         "seller_product_code": "",
        //                         "seller_supply_method": null
        //                     },
        //                     "seller": {
        //                         "id": 328784,
        //                         "name": "ikitech123"
        //                     },
        //                     "confirmation": {
        //                         "status": "waiting",
        //                         "confirmed_at": null,
        //                         "available_confirm_sla": "2023-03-29 12:00:00",
        //                         "pickup_confirm_sla": null,
        //                         "histories": []
        //                     },
        //                     "parent_item_id": 0,
        //                     "price": 150000,
        //                     "qty": 1,
        //                     "fulfilled_at": null,
        //                     "is_virtual": false,
        //                     "is_ebook": false,
        //                     "is_bookcare": false,
        //                     "is_free_gift": false,
        //                     "is_fulfilled": false,
        //                     "backend_id": 0,
        //                     "applied_rule_ids": [
        //                         "3096281"
        //                     ],
        //                     "invoice": {
        //                         "price": 150000,
        //                         "quantity": 1,
        //                         "subtotal": 150000,
        //                         "row_total": 150000,
        //                         "discount_amount": 0,
        //                         "discount_tikixu": 0,
        //                         "discount_promotion": 0,
        //                         "discount_percent": 0,
        //                         "discount_coupon": 0,
        //                         "discount_other": 0,
        //                         "discount_tikier": 0,
        //                         "discount_tiki_first": 0,
        //                         "discount_data": {},
        //                         "is_seller_discount_coupon": false,
        //                         "is_taxable": false,
        //                         "fob_price": 0,
        //                         "seller_fee": -11100,
        //                         "seller_income": 138900,
        //                         "fees": []
        //                     },
        //                     "inventory_requisition": null,
        //                     "inventory_withdrawals": [],
        //                     "seller_inventory_id": 384740,
        //                     "seller_inventory_name": "kho mb",
        //                     "seller_income_detail": {
        //                         "item_price": 150000,
        //                         "item_qty": 1,
        //                         "shipping_fee": 14000,
        //                         "seller_fees": [
        //                             {
        //                                 "id": null,
        //                                 "fee_type_key": "percent_per_item_sales_value",
        //                                 "fee_type_name": "Chiết khấu",
        //                                 "status": null,
        //                                 "quantity": null,
        //                                 "base_amount": 0,
        //                                 "total_amount": -3600,
        //                                 "discount_amount": 0,
        //                                 "final_amount": -3600
        //                             },
        //                             {
        //                                 "id": null,
        //                                 "fee_type_key": "payment_processing_fee",
        //                                 "fee_type_name": "Phí thanh toán",
        //                                 "status": null,
        //                                 "quantity": null,
        //                                 "base_amount": 0,
        //                                 "total_amount": -3000,
        //                                 "discount_amount": 0,
        //                                 "final_amount": -3000
        //                             },
        //                             {
        //                                 "id": null,
        //                                 "fee_type_key": "shipping_fee",
        //                                 "fee_type_name": "Phí vận chuyển",
        //                                 "status": null,
        //                                 "quantity": null,
        //                                 "base_amount": 0,
        //                                 "total_amount": -14000,
        //                                 "discount_amount": 0,
        //                                 "final_amount": -14000
        //                             },
        //                             {
        //                                 "id": null,
        //                                 "fee_type_key": "freeship_plus",
        //                                 "fee_type_name": "Phí tham gia Freeship không giới hạn",
        //                                 "status": null,
        //                                 "quantity": null,
        //                                 "base_amount": 0,
        //                                 "total_amount": -4500,
        //                                 "discount_amount": 0,
        //                                 "final_amount": -4500
        //                             }
        //                         ],
        //                         "sub_total": 164000,
        //                         "seller_income": 138900,
        //                         "discount": {
        //                             "discount_shipping_fee": {
        //                                 "sellerDiscount": 0,
        //                                 "fee_amount": 14000,
        //                                 "qty": 1,
        //                                 "apply_discount": [
        //                                     {
        //                                         "rule_id": "3096281",
        //                                         "type": "universal",
        //                                         "amount": 14000,
        //                                         "seller_sponsor": null,
        //                                         "tiki_sponsor": null
        //                                     }
        //                                 ],
        //                                 "seller_subsidy": 0,
        //                                 "tiki_subsidy": 14000
        //                             },
        //                             "discount_coupon": {
        //                                 "seller_discount": 0,
        //                                 "platform_discount": 0,
        //                                 "total_discount": 0
        //                             },
        //                             "discount_tikixu": {
        //                                 "amount": 0
        //                             }
        //                         }
        //                     }
        //                 }
        //             ],
        //             "status_histories": [],
        //             "is_virtual": false,
        //             "siblings": [],
        //             "tikixu_point_earning": 0,
        //             "is_flower_gift": false,
        //             "dropship_already": false,
        //             "created_at": "2023-03-28 12:50:18",
        //             "shipment_status_histories": [],
        //             "billing_address": {
        //                 "full_name": "hoàng sỹ",
        //                 "street": "vinfast mỹ đình, số 8 phạm hùng",
        //                 "ward": "Phường Mỹ Đình 1",
        //                 "ward_tiki_code": "VN034027004",
        //                 "district": "Quận Nam Từ Liêm",
        //                 "district_tiki_code": "VN034027",
        //                 "region": "Hà Nội",
        //                 "region_tiki_code": "VN034",
        //                 "country": "Việt Nam",
        //                 "country_id": "VN"
        //             },
        //             "main_substate_text_en": "Order verified",
        //             "type": "simple",
        //             "state_histories": [],
        //             "inventory_status": "backorder",
        //             "platform": "frontend-desktop",
        //             "seller_warehouse": {
        //                 "id": 384740,
        //                 "name": "kho mb",
        //                 "code": "WH000384740",
        //                 "contact_name": "cuong",
        //                 "contact_phone": "+84337056362",
        //                 "street": "Giao tự",
        //                 "owner": "seller",
        //                 "warehouse_type": "pickup",
        //                 "region_code": "VN034",
        //                 "region_id": 297,
        //                 "region_name": "Hà Nội",
        //                 "district_code": "VN034005",
        //                 "district_id": 6,
        //                 "district_name": "Huyện Gia Lâm",
        //                 "ward_code": "VN034005014",
        //                 "ward_id": 3261,
        //                 "ward_name": "Xã Kim Sơn",
        //                 "country_code": "vn",
        //                 "longitude": 105.990555,
        //                 "latitude": 21.025944,
        //                 "active": true
        //             },
        //             "linked_code": "",
        //             "has_backorder_items": true,
        //             "multiseller_confirmation": {
        //                 "seller_id": 328784,
        //                 "need_other_sellers_confirm": false
        //             },
        //             "original_code": "",
        //             "updated_at": "2023-03-28 12:55:39",
        //             "shipping": {
        //                 "partner_id": null,
        //                 "partner_name": null,
        //                 "tracking_code": null,
        //                 "status": null,
        //                 "pickup_shipping_code": null,
        //                 "pickup_partner_code": null,
        //                 "return_shipping_code": null,
        //                 "return_partner_code": null,
        //                 "delivery_shipping_code": null,
        //                 "delivery_partner_code": null,
        //                 "plan": {
        //                     "id": 1,
        //                     "name": "TikiFAST Giao Tiết Kiệm",
        //                     "is_free_shipping": true,
        //                     "promised_delivery_date": "2023-03-31 23:59:59",
        //                     "description": "Giao vào Thứ sáu, 31/03"
        //                 },
        //                 "address": {
        //                     "full_name": "hoàng sỹ",
        //                     "street": "vinfast mỹ đình, số 8 phạm hùng",
        //                     "ward": "Phường Mỹ Đình 1",
        //                     "ward_tiki_code": "VN034027004",
        //                     "district": "Quận Nam Từ Liêm",
        //                     "district_tiki_code": "VN034027",
        //                     "region": "Hà Nội",
        //                     "region_tiki_code": "VN034",
        //                     "country": "Việt Nam",
        //                     "country_id": "VN",
        //                     "email": null,
        //                     "phone": ""
        //                 },
        //                 "shipping_detail": null
        //             },
        //             "children": [],
        //             "shipment_mappings": [],
        //             "backend_id": 0,
        //             "main_substate": "order_verified",
        //             "payment": {
        //                 "method": "cod",
        //                 "is_prepaid": false,
        //                 "status": "success",
        //                 "description": "Thanh toán tiền mặt khi nhận hàng"
        //             },
        //             "is_bookcare": false,
        //             "relation_code": "",
        //             "is_rma": false,
        //             "boxes": [],
        //             "delivery": {
        //                 "delivery_confirmed": false,
        //                 "delivery_confirmed_at": null,
        //                 "delivery_confirmed_by_customer": false,
        //                 "delivery_confirmed_by_customer_at": null,
        //                 "delivery_note": null,
        //                 "delivery_confirmation": []
        //             },
        //             "main_state": "awaiting_confirmation",
        //             "tiki_warehouse": {
        //                 "id": 19,
        //                 "name": "Ha Noi 5",
        //                 "code": "hn5"
        //             },
        //             "labels": [],
        //             "applied_rule_ids": [
        //                 "3096281"
        //             ],
        //             "is_vat_exporting": false,
        //             "main_state_text": "Chờ xác nhận",
        //             "main_substate_text": "Tiki đã tiếp nhận đơn hàng",
        //             "invoice": {
        //                 "items_count": 1,
        //                 "items_quantity": 1,
        //                 "subtotal": 150000,
        //                 "grand_total": 150000,
        //                 "collectible_amount": 150000,
        //                 "discount_amount": 0,
        //                 "discount_tikixu": 0,
        //                 "discount_promotion": 0,
        //                 "discount_percent": 0,
        //                 "discount_coupon": 0,
        //                 "discount_other": 0,
        //                 "gift_card_amount": 0,
        //                 "gift_card_code": null,
        //                 "coupon_code": null,
        //                 "shipping_amount_after_discount": 0,
        //                 "shipping_discount_amount": 14000,
        //                 "handling_fee": 0,
        //                 "other_fee": 0,
        //                 "total_seller_fee": -11100,
        //                 "total_seller_income": 138900,
        //                 "purchased_at": "2023-03-28 12:50:18",
        //                 "tax_info": null
        //             },
        //             "main_state_text_en": "Awaiting Confirmation",
        //             "customer": {
        //                 "id": 20150314,
        //                 "full_name": "Tiến Sỹ"
        //             }
        //         }
        //     ],
        //     "paging": {
        //         "total": 1,
        //         "current_page": 1,
        //         "from": 1,
        //         "to": 1,
        //         "per_page": 20,
        //         "last_page": 1
        //     }
        // }
        $response = curl_exec($curl);

        return json_decode($response);
    }

    static function getProducts($token, $page)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.tiki.vn/integration/v2.1/products?page=' . $page,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
            ),
        ));

        // {
        //     "data": [
        //       {
        //         "product_id": 248705051,
        //         "sku": "1843684638526",
        //         "name": "San pham 1",
        //         "master_id": 248705050,
        //         "master_sku": "8442464609196",
        //         "super_id": 0,
        //         "super_sku": "",
        //         "active": 0,
        //         "original_sku": "",
        //         "type": "simple",
        //         "entity_type": "seller_simple",
        //         "price": 10000,
        //         "market_price": 0,
        //         "created_at": "2023-03-24 08:48:38",
        //         "updated_at": "2023-03-24 08:48:38",
        //         "thumbnail": "https://salt.tikicdn.com/cache/280x280/ts/product/a8/6d/a1/995f4713f6b8e10503d0828510bc138b.jpg",
        //         "attributes": {},
        //         "categories": [
        //           {
        //             "id": 7566,
        //             "name": "Túi trống",
        //             "url_key": "tui-trong",
        //             "is_primary": true
        //           },
        //           {
        //             "id": 6000,
        //             "name": "Balo và Vali",
        //             "url_key": "balo-va-vali",
        //             "is_primary": false
        //           },
        //           {
        //             "id": 8387,
        //             "name": "Túi du lịch và phụ kiện",
        //             "url_key": "tui-du-lich-va-phu-kien",
        //             "is_primary": false
        //           }
        //         ]
        //       },
        //       {
        //         "product_id": 241949322,
        //         "sku": "1484823017481",
        //         "name": "hjhjàdasdasdf",
        //         "master_id": 241949319,
        //         "master_sku": "2767998086499",
        //         "super_id": 0,
        //         "super_sku": "",
        //         "active": 1,
        //         "original_sku": "213123",
        //         "type": "simple",
        //         "entity_type": "seller_simple",
        //         "price": 1231232,
        //         "market_price": 0,
        //         "created_at": "2023-03-01 14:43:03",
        //         "updated_at": "2023-03-06 11:35:37",
        //         "thumbnail": "https://salt.tikicdn.com/cache/280x280/ts/product/2d/30/72/51966137114d674dbdaf6d2efa56939e.jpg",
        //         "attributes": {},
        //         "categories": [
        //           {
        //             "id": 23228,
        //             "name": "Chậu, vòi rửa chén bát",
        //             "url_key": "chau-voi-rua-chen-bat",
        //             "is_primary": true
        //           },
        //           {
        //             "id": 1883,
        //             "name": "Nhà Cửa - Đời Sống",
        //             "url_key": "nha-cua-doi-song",
        //             "is_primary": false
        //           },
        //           {
        //             "id": 1951,
        //             "name": "Dụng cụ nhà bếp",
        //             "url_key": "nha-bep",
        //             "is_primary": false
        //           },
        //           {
        //             "id": 1986,
        //             "name": "Phụ kiện nhà bếp",
        //             "url_key": "phu-kien-nha-bep",
        //             "is_primary": false
        //           }
        //         ]
        //       }
        //     ],
        //     "paging": {
        //       "total": 2,
        //       "per_page": 20,
        //       "current_page": 1,
        //       "last_page": 1,
        //       "from": 0,
        //       "to": 2
        //     }
        //   }
        $response = curl_exec($curl);

        return json_decode($response);
    }

    static function updateProduct($token, $body)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.tiki.vn/integration/v2.1/products/updateSku',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        // {
        //     "state": "approved"
        // }

        $info = curl_getinfo($curl);

        if ($info["http_code"] != 200) {
            throw new \ErrorException((json_decode($response)->errors[0]));
        }
        return json_decode($response);
    }


    static function getOrderDetail($token, $order_code)
    {

        // {
        //     "id": 176788292,
        //     "code": "216045310",
        //     "fulfillment_type": "dropship",
        //     "status": "canceled",
        //     "items": [
        //         {
        //             "id": 281705021,
        //             "product": {
        //                 "id": 248956418,
        //                 "type": "simple",
        //                 "super_id": 0,
        //                 "master_id": 248956417,
        //                 "sku": "7121107567807",
        //                 "name": "Áo Thun KXGF1 hoa",
        //                 "catalog_group_name": "Thời trang",
        //                 "inventory_type": "backorder",
        //                 "imeis": [],
        //                 "serial_numbers": [],
        //                 "thumbnail": "https://salt.tikicdn.com/cache/280x280/ts/product/3f/7f/bc/c7a496c3dd5663c9e9997197565d37e0.jpeg",
        //                 "seller_product_code": "",
        //                 "seller_supply_method": null
        //             },
        //             "seller": {
        //                 "id": 328784,
        //                 "name": "ikitech123"
        //             },
        //             "confirmation": {
        //                 "status": "waiting",
        //                 "confirmed_at": null,
        //                 "available_confirm_sla": "2023-04-05 12:00:00",
        //                 "pickup_confirm_sla": null,
        //                 "histories": []
        //             },
        //             "parent_item_id": 0,
        //             "price": 150000,
        //             "qty": 2,
        //             "fulfilled_at": null,
        //             "is_virtual": false,
        //             "is_ebook": false,
        //             "is_bookcare": false,
        //             "is_free_gift": false,
        //             "is_fulfilled": false,
        //             "backend_id": 0,
        //             "applied_rule_ids": [
        //                 "3096283"
        //             ],
        //             "invoice": {
        //                 "price": 150000,
        //                 "quantity": 2,
        //                 "subtotal": 300000,
        //                 "row_total": 300000,
        //                 "discount_amount": 0,
        //                 "discount_tikixu": 0,
        //                 "discount_promotion": 0,
        //                 "discount_percent": 0,
        //                 "discount_coupon": 0,
        //                 "discount_other": 0,
        //                 "discount_tikier": 0,
        //                 "discount_tiki_first": 0,
        //                 "discount_data": {},
        //                 "is_seller_discount_coupon": false,
        //                 "is_taxable": false,
        //                 "fob_price": 0,
        //                 "seller_fee": 0,
        //                 "seller_income": 0,
        //                 "fees": []
        //             },
        //             "inventory_requisition": null,
        //             "inventory_withdrawals": [],
        //             "seller_inventory_id": 384740,
        //             "seller_inventory_name": "kho mb",
        //             "seller_income_detail": {
        //                 "item_price": 150000,
        //                 "item_qty": 2,
        //                 "shipping_fee": 14000,
        //                 "seller_fees": [],
        //                 "sub_total": 314000,
        //                 "seller_income": 314000,
        //                 "discount": {
        //                     "discount_shipping_fee": {
        //                         "sellerDiscount": 0,
        //                         "fee_amount": 14000,
        //                         "qty": 1,
        //                         "apply_discount": [
        //                             {
        //                                 "rule_id": "3096283",
        //                                 "type": "universal",
        //                                 "amount": 14000,
        //                                 "seller_sponsor": null,
        //                                 "tiki_sponsor": null
        //                             }
        //                         ],
        //                         "seller_subsidy": 0,
        //                         "tiki_subsidy": 14000
        //                     },
        //                     "discount_coupon": {
        //                         "seller_discount": 0,
        //                         "platform_discount": 0,
        //                         "total_discount": 0
        //                     },
        //                     "discount_tikixu": {
        //                         "amount": 0
        //                     }
        //                 }
        //             }
        //         }
        //     ],
        //     "status_histories": [
        //         {
        //             "id": 2124969183,
        //             "status": "processing",
        //             "created_at": "2023-04-04 16:39:15"
        //         },
        //         {
        //             "id": 2124971850,
        //             "status": "queueing",
        //             "created_at": "2023-04-04 16:44:32"
        //         },
        //         {
        //             "id": 2124982136,
        //             "status": "canceled",
        //             "created_at": "2023-04-04 17:03:26"
        //         }
        //     ],
        //     "is_virtual": false,
        //     "siblings": [],
        //     "tikixu_point_earning": 0,
        //     "is_flower_gift": false,
        //     "dropship_already": false,
        //     "created_at": "2023-04-04 16:39:15",
        //     "shipment_status_histories": [],
        //     "billing_address": {
        //         "full_name": "trang huyền",
        //         "street": "169 đăng thai thân",
        //         "ward": "Phường Trung Liệt",
        //         "ward_tiki_code": "VN034021017",
        //         "district": "Quận Đống Đa",
        //         "district_tiki_code": "VN034021",
        //         "region": "Hà Nội",
        //         "region_tiki_code": "VN034",
        //         "country": "Việt Nam",
        //         "country_id": "VN"
        //     },
        //     "main_substate_text_en": "",
        //     "type": "simple",
        //     "state_histories": [],
        //     "inventory_status": "backorder",
        //     "platform": "frontend-desktop",
        //     "seller_warehouse": {
        //         "id": 384740,
        //         "name": "kho mb",
        //         "code": "WH000384740",
        //         "contact_name": "cuong",
        //         "contact_phone": "+84337056362",
        //         "street": "Giao tự",
        //         "owner": "seller",
        //         "warehouse_type": "pickup",
        //         "region_code": "VN034",
        //         "region_id": 297,
        //         "region_name": "Hà Nội",
        //         "district_code": "VN034005",
        //         "district_id": 6,
        //         "district_name": "Huyện Gia Lâm",
        //         "ward_code": "VN034005014",
        //         "ward_id": 3261,
        //         "ward_name": "Xã Kim Sơn",
        //         "country_code": "vn",
        //         "longitude": 105.990555,
        //         "latitude": 21.025944,
        //         "active": true
        //     },
        //     "linked_code": "",
        //     "has_backorder_items": true,
        //     "multiseller_confirmation": {
        //         "seller_id": 328784,
        //         "need_other_sellers_confirm": false
        //     },
        //     "original_code": "",
        //     "updated_at": "2023-04-04 17:03:26",
        //     "shipping": {
        //         "partner_id": null,
        //         "partner_name": null,
        //         "tracking_code": null,
        //         "status": null,
        //         "pickup_shipping_code": null,
        //         "pickup_partner_code": null,
        //         "return_shipping_code": null,
        //         "return_partner_code": null,
        //         "delivery_shipping_code": null,
        //         "delivery_partner_code": null,
        //         "plan": {
        //             "id": 1,
        //             "name": "TikiFAST Giao Tiết Kiệm",
        //             "is_free_shipping": true,
        //             "promised_delivery_date": "2023-04-07 23:59:59",
        //             "description": "Giao vào Thứ sáu, 07/04"
        //         },
        //         "address": {
        //             "full_name": "trang huyền",
        //             "street": "169 đăng thai thân",
        //             "ward": "Phường Trung Liệt",
        //             "ward_tiki_code": "VN034021017",
        //             "district": "Quận Đống Đa",
        //             "district_tiki_code": "VN034021",
        //             "region": "Hà Nội",
        //             "region_tiki_code": "VN034",
        //             "country": "Việt Nam",
        //             "country_id": "VN",
        //             "email": null,
        //             "phone": ""
        //         },
        //         "shipping_detail": null
        //     },
        //     "children": [],
        //     "shipment_mappings": [],
        //     "backend_id": 0,
        //     "payment": {
        //         "method": "cod",
        //         "is_prepaid": false,
        //         "status": "success",
        //         "description": "Thanh toán tiền mặt khi nhận hàng"
        //     },
        //     "state": "canceled",
        //     "is_bookcare": false,
        //     "relation_code": "",
        //     "cancel_info": {
        //         "reason_code": "201",
        //         "reason_text": "Không còn nhu cầu",
        //         "comment": "",
        //         "canceled_at": null
        //     },
        //     "is_rma": false,
        //     "boxes": [],
        //     "delivery": {
        //         "delivery_confirmed": false,
        //         "delivery_confirmed_at": null,
        //         "delivery_confirmed_by_customer": false,
        //         "delivery_confirmed_by_customer_at": null,
        //         "delivery_note": null,
        //         "delivery_confirmation": []
        //     },
        //     "main_state": "canceled",
        //     "tiki_warehouse": {
        //         "id": 19,
        //         "name": "Ha Noi 5",
        //         "code": "hn5"
        //     },
        //     "labels": [],
        //     "applied_rule_ids": [
        //         "3096283"
        //     ],
        //     "is_vat_exporting": false,
        //     "main_state_text": "Hủy",
        //     "main_substate_text": "",
        //     "invoice": {
        //         "items_count": 1,
        //         "items_quantity": 2,
        //         "subtotal": 300000,
        //         "grand_total": 300000,
        //         "collectible_amount": 300000,
        //         "discount_amount": 0,
        //         "discount_tikixu": 0,
        //         "discount_promotion": 0,
        //         "discount_percent": 0,
        //         "discount_coupon": 0,
        //         "discount_other": 0,
        //         "gift_card_amount": 0,
        //         "gift_card_code": null,
        //         "coupon_code": null,
        //         "shipping_amount_after_discount": 0,
        //         "shipping_discount_amount": 14000,
        //         "handling_fee": 0,
        //         "other_fee": 0,
        //         "total_seller_fee": 0,
        //         "total_seller_income": 0,
        //         "purchased_at": "2023-04-04 16:39:15",
        //         "tax_info": null
        //     },
        //     "main_state_text_en": "Canceled",
        //     "customer": {
        //         "id": 16010160,
        //         "full_name": "Phm Huyn Trang"
        //     }
        // }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.tiki.vn/integration/v2/orders/216045310?include=status_histories',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }


    static function getAllInventory($token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.tiki.vn/integration/v2/sellers/me/warehouses',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        // {
        //     "state": "approved"
        // }

        $info = curl_getinfo($curl);

        if ($info["http_code"] != 200) {
            throw new \ErrorException((json_decode($response)->errors[0]));
        }
        return json_decode($response);
    }
}
