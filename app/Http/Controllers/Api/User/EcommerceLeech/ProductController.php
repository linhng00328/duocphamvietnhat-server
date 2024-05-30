<?php

namespace App\Http\Controllers\Api\User\EcommerceLeech;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use Exception;

class ProductController extends Controller
{
    /*
    {
        "description": "hieudeptrai",
        "name": "hieu",
        "index_image_avatar": 0,
        "price": 1000,
        "barcode": "ghdgfh",
        "status": 0,
        "images": [
            "https://gaixinh24h.com/wp-content/uploads/2020/03/hinh-gai-dep-tu-nhien-gaixinh24h-7.jpg",
            "https://gaixinh24h.com/wp-content/uploads/2020/03/hinh-gai-dep-tu-nhien-gaixinh24h-7.jpg",
            "https://gaixinh24h.com/wp-content/uploads/2020/03/hinh-gai-dep-tu-nhien-gaixinh24h-7.jpg"
        ],
        "list_distribute": [
            {
                "name": "Màu2",
                "sub_element_distribute_name":"Size1",
                "element_distributes": [
                    {
                        "name": "Đỏ",
                        "image_url": "https://gaixinh24h.com/wp-content/uploads/2020/03/hinh-gai-dep-tu-nhien-gaixinh24h-7.jpg",
                        "price": 1,
                        "quantity_in_stock": 2,
                        "sub_element_distributes": [
                            {
                                "name": "XL",
                                "image_url": "https://gaixinh24h.com/wp-content/uploads/2020/03/hinh-gai-dep-tu-nhien-gaixinh24h-7.jpg",
                                "price": 3,
                                "quantity_in_stock": 4
                            },
                            {
                                "name": "SX",
                                "image_url": "https://gaixinh24h.com/wp-content/uploads/2020/03/hinh-gai-dep-tu-nhien-gaixinh24h-7.jpg",
                                "price": 5,
                                "quantity_in_stock": 6
                            }
                        ]
                    },
                    {
                        "name": "Xanh",
                        "image_url": "https://gaixinh24h.com/wp-content/uploads/2020/03/hinh-gai-dep-tu-nhien-gaixinh24h-7.jpg",
                         "price": 122,
                        "quantity_in_stock": 233,
                        "sub_element_distributes": [
                            {
                                "name": "XL",
                                "image_url": "https://gaixinh24h.com/wp-content/uploads/2020/03/hinh-gai-dep-tu-nhien-gaixinh24h-7.jpg",
                                "price": 31,
                                "quantity_in_stock": 42
                            },
                            {
                                "name": "SX",
                                "image_url": "https://gaixinh24h.com/wp-content/uploads/2020/03/hinh-gai-dep-tu-nhien-gaixinh24h-7.jpg",
                                "price": 55,
                                "quantity_in_stock": 64
                            }
                        ]
                    }
                ]
            }
        ],
        "list_attribute": [
            {
                "name": "Màu",
                "value": "Xanh"
            },
            {
                "name": "Xuất xứ",
                "value": "Vàng"
            }
        ],
        "categories": []
    }

    
*/

    /**
     * Lấy danh sách sản phẩm ở sàn TMDT
     * 
     *
     * @bodyParam page int required Trang
     * @bodyParam shop_id int required Id của shop 
     * @bodyParam provider int required Sàn nào (shopee,lazada,sendo)
     */
    public function get_product(Request $request)
    {

        $provider = $request->provider;

        $page = $request->page;
        $shop_id = $request->shop_id;

        $data = [
            "total_count" =>  0,
            "page" =>  0,
            "per_page" => 30,
            'list' =>   []
        ];

        if ($provider  == 'shopee') {
            $data  = $this->shopee_data($shop_id, $page);
        }

        if ($provider  == 'lazada') {
            $data  = $this->lazada_data($shop_id, $page);
        }

        if ($provider  == 'sendo') {
            $data  = $this->sendo_data($shop_id, $page);
        }
        if ($provider  == 'tiki') {
            $data  = $this->tiki_data($shop_id, $page);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data
        ], 200);
    }


    function shopee_data($shop_id, $page)
    {

        $data = [];
        $list = [];
        $total_count = 0;
        $client = new GuzzleClient();

        preg_match('/[a-zA-Z]/', $shop_id, $matches);
        if (count($matches)) {


            try {
                $urlPro =   "https://shopee.vn/api/v4/shop/get_shop_detail?sort_sold_out=0&username=$shop_id";
                $response = $client->request(
                    'GET',
                    $urlPro,
                );

                $body = (string) $response->getBody();
                $jsonResponse = json_decode($body);

                if (isset($jsonResponse->data->shopid)) {
                    $shop_id =  $jsonResponse->data->shopid;
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $statusCode = $e->getResponse()->getStatusCode();
                return response()->json([
                    'code' => 400,
                    'success' => true,
                    'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                    'msg' => MsgCode::NO_STORE_EXISTS[1],
                ], 400);
            }
        }


        try {
            $page_request = (($page - 1) * 30);
            $response = $client->request(
                'GET',
                "https://shopee.vn/api/v4/shop/rcmd_items?bundle=shop_page_category_tab_main&item_card=2&limit=30&offset=$page_request&section=shop_page_category_tab_main_sec&shopid=$shop_id&sort_type=1&tab_name=popular&upstream=pdp",
                [
                    'headers' => [
                        'Accept' => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
                        'User-Agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:106.0) Gecko/20100101 Firefox/106.0",
                    ]
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);


            $total_count =   $jsonResponse->data->sections[0]->total ?? 0;


            foreach ($jsonResponse->data->sections[0]->data->item as $item) { //Chay san pham
                $images = [];
                $list_distribute = [];


                //Chay distribute
                // if ($item->item_basic->tier_variations != null && count($item->item_basic->tier_variations) > 0) {

                //     $element_distributes_add = [];
                //     $index = 0;
                //     foreach ($item->item_basic->tier_variations[0]->options as $element_distributes) {

                //         $sub_element_distributes_add = [];

                //         //Kiem tra tung item theo list
                //         if (isset($item->item_basic->tier_variations[1])) {

                //             foreach ($item->item_basic->tier_variations[1]->options  as $sub_element_distributes) {
                //                 array_push(
                //                     $sub_element_distributes_add,
                //                     [
                //                         'name' => $sub_element_distributes,
                //                     ]
                //                 );
                //             }
                //         }

                //         $image = null;

                //         if (isset($item->item_basic->tier_variations[0]->images[$index])) {
                //             $image = $item->item_basic->tier_variations[0]->images[$index];
                //         }



                //         array_push(
                //             $element_distributes_add,
                //             [
                //                 'name' => $element_distributes,
                //                 'image' => $image == null ? null :  "https://cf.shopee.vn/file/$image",
                //                 'sub_element_distributes' => $sub_element_distributes_add
                //             ]
                //         );
                //         $index++;
                //     }

                //     $data_distribute = [
                //         'name' =>   $item->item_basic->tier_variations[0]->name,
                //         'sub_element_distribute_name' =>   isset($item->item_basic->tier_variations[1]) ? $item->item_basic->tier_variations[1]->name : null,
                //         'element_distributes' =>  $element_distributes_add
                //     ];

                //     array_push(
                //         $list_distribute,
                //         $data_distribute
                //     );
                // }

                $image = "https://cf.shopee.vn/file/$item->image";

                array_push($images,  $image);
                // $itemid =  $item->itemid;

                // //Noi dung chi tiet
                // $client = new GuzzleClient();
                // $response = $client->request(
                //     'GET',
                //     "https://shopee.vn/api/v4/item/get?itemid=$itemid&shopid=$shop_id",
                //     [
                //         'headers' => [
                //             'Accept' => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
                //             'User-Agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:106.0) Gecko/20100101 Firefox/106.0",
                //             "sz-token" => "01KaCPngTsZswsQDp+FiMg==|DYGfMFE1CqnEHrtCC4xEjRjmn3GCQSLWeJ7cRZ13igMF8lyxozUQDxdfMuxIWF9NTBFxnBJM/9Af3V61U2wCTznMuCguHkfZg5Y=|X4jF2b0M8pHdZTvH|06|3",
                //             "cookie" => '_gcl_au=1.1.293748568.1659928018; SPC_F=IrhLW28eUGyVi74OkHqI7doQEQJcSU6z; REC_T_ID=2a17bbde-16c7-11ed-bee9-ccbbfe5deea3; _fbp=fb.1.1659928018999.108976709; _hjSessionUser_868286=eyJpZCI6ImEyNDFkMTRmLWQ2ZGQtNTYxMS05MzQyLWE4ODFlODIxMTczMCIsImNyZWF0ZWQiOjE2NTk5MjgwMTg4OTAsImV4aXN0aW5nIjp0cnVlfQ==; G_ENABLED_IDPS=google; SPC_CLIENTID=SXJoTFcyOGVVR3lWolfmkylroavygsll; _ga_KK6LLGGZNQ=GS1.1.1660710134.1.0.1660710134.0.0.0; __stripe_mid=8c6e8f8c-0a4f-43fa-b3d3-5bc023647c583a72aa; _ga_CGXK257VSB=GS1.1.1663740643.1.0.1663740643.60.0.0; _med=refer; SPC_EC=-; SPC_U=-; SPC_IA=-1; SPC_T_ID="mEtI9Kr+nBjnmtiZZycAgpk5iG7iMC0exkC0bJ03sTCduG7BWd/lDhbjQRQTCZv79gaqIiJE78xUNWLQ7QWnpuE/WjGNpTJ2SJiMCNg97F4="; SPC_T_IV="ENy/Vd2D7Qb/9dwVtU+BTg=="; SPC_R_T_ID=mEtI9Kr+nBjnmtiZZycAgpk5iG7iMC0exkC0bJ03sTCduG7BWd/lDhbjQRQTCZv79gaqIiJE78xUNWLQ7QWnpuE/WjGNpTJ2SJiMCNg97F4=; SPC_R_T_IV=ENy/Vd2D7Qb/9dwVtU+BTg==; SPC_T_ID=mEtI9Kr+nBjnmtiZZycAgpk5iG7iMC0exkC0bJ03sTCduG7BWd/lDhbjQRQTCZv79gaqIiJE78xUNWLQ7QWnpuE/WjGNpTJ2SJiMCNg97F4=; SPC_T_IV=ENy/Vd2D7Qb/9dwVtU+BTg==; SPC_SI=xhVRYwAAAABhVU9TdXZHVZnTuAAAAAAAVHpoVkJJTWY=; __LOCALE__null=VN; csrftoken=PZNqypnQTPus82WIE5KJcAUzmTHYcpYq; _QPWSDCXHZQA=26bb7364-5709-4ca3-c399-bdd9780648e8; _hjSession_868286=eyJpZCI6ImE0NWQ0MmE2LTgxNDYtNDI0NC1iNzgyLWNlNzRjOTY0MTc2MSIsImNyZWF0ZWQiOjE2NjY4NDA2Nzg1NzAsImluU2FtcGxlIjpmYWxzZX0=; AMP_TOKEN=%24NOT_FOUND; _gid=GA1.2.126800478.1666840679; cto_bundle=DHwpaF91NG5aSUpMUnlxa2JPOVVpNEZkU0tEVzglMkJnQUZnSlFQaEJ6cHo5WnhPVmRQUmhQRlI5MDYlMkJQQ1pSZTFiUlBCNUxING1WTW5xcGtralV2eUFGaHFyJTJCckJhN1VDUzFKd1hoZkVvbXFpbXN2WkVVMlV1Nm9PclQlMkJoS1NWSVFWWkFkdXJlbjVvd3pqbW5YMU5aT0pGZWRtdyUzRCUzRA; _ga_M32T05RVZT=GS1.1.1666840678.20.1.1666842379.59.0.0; _ga=GA1.1.52459693.1659928018; _dc_gtm_UA-61914164-6=1; shopee_webUnique_ccd=01KaCPngTsZswsQDp%2BFiMg%3D%3D%7CDYGfMFE1CqnEHrtCC4xEjRjmn3GCQSLWeJ7cRZ13igMF8lyxozUQDxdfMuxIWF9NTBFxnBJM%2F9Af3V61U2wCTznMuCguHkfZg5Y%3D%7CX4jF2b0M8pHdZTvH%7C06%7C3; ds=f2282d864983336b0d2b01a05d8f3d16'
                //             ]
                //     ]
                // );

                // dd($response);

                // $body = (string) $response->getBody();
                // $jsonResponse = json_decode($body);
                // //Attributes

                // $list_attribute = [];
                // if ($jsonResponse->data->attributes != null && count($jsonResponse->data->attributes) > 0) {
                //     foreach ($jsonResponse->data->attributes as $attribute) {

                //         array_push($list_attribute, [
                //             'name' => $attribute->name,
                //             'value' => $attribute->value,
                //         ]);
                //     }
                // }

                // //thêm giá cho sub element
                // if ($jsonResponse->data->models != null && count($jsonResponse->data->models) > 0 && count($list_distribute) > 0) {
                //     foreach ($jsonResponse->data->models as $model) {

                //         $indexEleDis = 0;
                //         foreach ($list_distribute[0]["element_distributes"] as $element_distributes) {
                //             $indexSub = 0;
                //             foreach ($element_distributes["sub_element_distributes"] as $sub_element_distributes) {

                //                 if ($element_distributes["name"] . ',' . $sub_element_distributes["name"] == $model->name) {

                //                     //dd($list_distribute[0]["element_distributes"][$indexEleDis]['sub_element_distributes'][$indexSub]);

                //                     $list_distribute[0]["element_distributes"][$indexEleDis]['sub_element_distributes'][$indexSub]['price'] = $model->price;
                //                     break;
                //                 }
                //                 $indexSub++;
                //             }
                //             $indexEleDis++;
                //         }
                //     }
                // }





                array_push(
                    $list,
                    [
                        'name' => $item->name,
                        'price' => (int)($item->price / 100000),
                        'images' => $images,
                      //  'list_distribute' => $list_distribute,
                        //'description' => $jsonResponse->data->description != null ? '<span style="white-space: pre-wrap;">' . $jsonResponse->data->description . '</span>'  : null
                    ]
                );
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $statusCode = $e->getResponse()->getStatusCode();
        }

        // catch (Exception $e) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::CATEGORY_EXISTS[0],
        //         'msg' => $e,
        //     ], 400);
        // }



        return [
            "total_count" =>  $total_count,
            "page" =>  (int)$page,
            "per_page" => count($list),
            'list' =>    $list
        ];
    }



    function lazada_data($shop_id, $page)
    {

        $data = [];
        $list = [];
        $total_count = 0;



        $client = new GuzzleClient();
        try {

            $response = $client->request(
                'GET',
                "https://www.lazada.vn/$shop_id/?ajax=true&from=wangpu&lang=vi&langFlag=vi&page=$page&pageTypeId=2&q=All-Products",
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);


            $total_count =   isset($jsonResponse->mainInfo->totalResults) ?   (int) $jsonResponse->mainInfo->totalResults  : 0;



            foreach ($jsonResponse->mods->listItems as $item) { //Chay san pham

                $images = [];
                $list_distribute = [];

                if ($item->thumbs != null && count($item->thumbs) > 0) {

                    foreach ($item->thumbs as  $thumb) {
                        array_push($images, $thumb->image);
                    }
                }

                //Chay distribute
                // if ($item->item_basic->tier_variations != null && count($item->item_basic->tier_variations) > 0) {

                //     $element_distributes_add = [];
                //     $index = 0;
                //     foreach ($item->item_basic->tier_variations[0]->options as $element_distributes) {

                //         $sub_element_distributes = [];
                //         //Kiem tra tung item theo list
                //         if (isset($item->item_basic->tier_variations[1])) {
                //             foreach ($item->item_basic->tier_variations[1]->options  as $sub_element_distributes) {
                //                 array_push(
                //                     $element_distributes_add,
                //                     [
                //                         'name' => $sub_element_distributes,
                //                     ]
                //                 );
                //             }
                //         }

                //         $image = $item->item_basic->tier_variations[0]->options[$index];
                //         array_push(
                //             $element_distributes_add,
                //             [
                //                 'name' => $element_distributes,
                //                 'image' =>  "https://cf.shopee.vn/file/$image",
                //                 'sub_element_distributes' => $sub_element_distributes
                //             ]
                //         );
                //         $index++;
                //     }

                //     $data_distribute = [
                //         'name' =>   $item->item_basic->tier_variations[0]->name,
                //         'sub_element_distribute_name' =>   isset($item->item_basic->tier_variations[1]) ? $item->item_basic->tier_variations[1]->name : null,
                //         'element_distributes' =>  $element_distributes_add
                //     ];

                //     array_push(
                //         $list_distribute,
                //         $data_distribute
                //     );
                // }

                // foreach ($item->item_basic->images as $image) {
                //     array_push(
                //         $images,
                //         "https://cf.shopee.vn/file/$image"
                //     );
                // }
                array_push(
                    $list,
                    [
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => (int)($item->price),
                        'images' => $images,
                        'list_distribute' => $list_distribute
                    ]
                );
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
        }

        // catch (Exception $e) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::CATEGORY_EXISTS[0],
        //         'msg' => $e,
        //     ], 400);
        // }



        return [
            "total_count" =>  $total_count,
            "page" =>  (int)$page,
            "per_page" => count($list),
            'list' =>    $list
        ];
    }


    function get_numerics($str)
    {
        preg_match_all('/\d+/', $str, $matches);
        return $matches[0];
    }

    function sendo_data($shop_id, $page)
    {

        $data = [];
        $list = [];
        $total_count = 0;



        $client = new GuzzleClient();
        try {


            $htmlP = file_get_contents("https://www.sendo.vn/shop/$shop_id/san-pham");
            $startIndex = strpos($htmlP, "seller_admin_id");


            if ($startIndex  != false) {

                $htmlP = substr($htmlP, $startIndex);

                $endIndex = strpos($htmlP, "}}}");

                $shop_id = substr($htmlP, 0,   $endIndex);

                $shop_id  =  $this->get_numerics($shop_id)[0];
            }


            $page_request = (($page - 1) * 30);
            $response = $client->request(
                'GET',
                "https://api.sendo.vn/onsite-services/shop/product-filter?limit=60&platform=2&seller_admin_id=$shop_id&sortType=vasup_desc",
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);


            $total_count =   $jsonResponse->data->total ?? 0;

            $description = "";
            foreach ($jsonResponse->data->list as $item) { //Chay san pham
                $images = [];
                $list_distribute = [];

                try {

                    $newstr = str_replace("www.sendo.vn/", "detail-api.sendo.vn/full/", $item->url_key);
                    $newstr = str_replace(".html", "", $newstr);
                    $response = $client->request(
                        'GET',
                        $newstr,
                    );

                    $body = (string) $response->getBody();
                    $jsonResponse = json_decode($body);


                    $description = $jsonResponse->data->description_info->description ?? "";
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                }

                array_push(
                    $images,
                    $item->image
                );

                array_push(
                    $list,
                    [
                        'name' => $item->name,
                        'description' => $description,
                        'price' => (int)($item->price),
                        'images' => $images,
                        'list_distribute' => $list_distribute
                    ]
                );
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
        }

        // catch (Exception $e) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::CATEGORY_EXISTS[0],
        //         'msg' => $e,
        //     ], 400);
        // }



        return [
            "total_count" =>  $total_count,
            "page" =>  (int)$page,
            "per_page" => count($list),
            'list' =>    $list
        ];
    }


    function tiki_data($shop_id, $page)
    {

        $data = [];
        $list = [];
        $total_count = 0;



        $client = new GuzzleClient();
        try {

            $limit = (($page - 1)) * 30 + 1;

            $response = $client->request(
                'GET',
                "https://api.tiki.vn/v2/seller/stores/$shop_id/products?limit=$limit&page=$page",
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            $total_count =   isset($jsonResponse->paging->total) ?   (int) $jsonResponse->paging->total  : 0;

            foreach ($jsonResponse->data as $item) { //Chay san pham

                $images = [];
                $list_distribute = [];

                array_push($images, $item->thumbnail_url);

                $desription =  $item->short_description;
                $idProduct  = $item->id;
                try {
                    $response = $client->request(
                        'GET',
                        "https://tiki.vn/api/v2/products/$idProduct"
                    );
                    $body = (string) $response->getBody();
                    $jsonResponse2 = json_decode($body);

                    $desription = $jsonResponse2->description;
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                }


                //Chay distribute
                // if ($item->item_basic->tier_variations != null && count($item->item_basic->tier_variations) > 0) {

                //     $element_distributes_add = [];
                //     $index = 0;
                //     foreach ($item->item_basic->tier_variations[0]->options as $element_distributes) {

                //         $sub_element_distributes = [];
                //         //Kiem tra tung item theo list
                //         if (isset($item->item_basic->tier_variations[1])) {
                //             foreach ($item->item_basic->tier_variations[1]->options  as $sub_element_distributes) {
                //                 array_push(
                //                     $element_distributes_add,
                //                     [
                //                         'name' => $sub_element_distributes,
                //                     ]
                //                 );
                //             }
                //         }

                //         $image = $item->item_basic->tier_variations[0]->options[$index];
                //         array_push(
                //             $element_distributes_add,
                //             [
                //                 'name' => $element_distributes,
                //                 'image' =>  "https://cf.shopee.vn/file/$image",
                //                 'sub_element_distributes' => $sub_element_distributes
                //             ]
                //         );
                //         $index++;
                //     }

                //     $data_distribute = [
                //         'name' =>   $item->item_basic->tier_variations[0]->name,
                //         'sub_element_distribute_name' =>   isset($item->item_basic->tier_variations[1]) ? $item->item_basic->tier_variations[1]->name : null,
                //         'element_distributes' =>  $element_distributes_add
                //     ];

                //     array_push(
                //         $list_distribute,
                //         $data_distribute
                //     );
                // }

                // foreach ($item->item_basic->images as $image) {
                //     array_push(
                //         $images,
                //         "https://cf.shopee.vn/file/$image"
                //     );
                // }
                array_push(
                    $list,
                    [
                        'name' => $item->name,
                        'description' => $desription,
                        'price' => (int)($item->price),
                        'images' => $images,
                        'list_distribute' => $list_distribute
                    ]
                );
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
        }


        return [
            "total_count" =>  $total_count,
            "page" =>  (int)$page,
            "per_page" => count($list),
            'list' =>    $list
        ];
    }
}
