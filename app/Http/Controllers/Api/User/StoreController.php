<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\BranchUtils;
use App\Helper\Data\Post\DataPostExample;
use App\Helper\Helper;
use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Jobs\DataDemoNewStoreJobDecentralization;
use App\Models\AppTheme;
use App\Models\Attribute;
use App\Models\AttributeField;
use App\Models\CarouselAppImage;
use App\Models\Category;
use App\Models\CategoryPost;
use App\Models\Customer;
use App\Models\Distribute;
use App\Models\ElementDistribute;
use App\Models\MsgCode;
use App\Models\Post;
use App\Models\PostCategoryPost;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDistribute;
use App\Models\ProductImage;
use App\Models\Store;
use App\Models\WebTheme;
use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group  User/Store
 */
class StoreController extends Controller
{

    //Data p
    public function new_data_example(Request $request)
    {

        $store_id = 5;
        $store_name = "Utachi";

        $categoryCreate = CategoryPost::create(
            [
                'image_url' => "https://i.imgur.com/0NQhkzs.jpg",
                'title' => 'Chính sách',
                'store_id' => $store_id,
                'description' => "Danh mục chính sách",
            ]
        );

        $webThemeSaved = WebTheme::where(
            'store_id',
            $store_id
        )->first();

        //////////////////////////////////////////////////////////////////
        $postCreate = Post::create(
            [
                'store_id' => $store_id,
                'title' => 'Chính sách đổi trả',
                'image_url' => 'https://i.imgur.com/7dPjFRM.jpg',
                'summary' => 'Chính sách đổi trả',
                'content' => DataPostExample::getReturnPolicy($store_name),
                'published' => true,
            ]
        );
        PostCategoryPost::create(
            [
                'post_id' => $postCreate->id,
                'categorypost_id' => $categoryCreate->id
            ]
        );
        $webThemeSaved->update(['post_id_return_policy' => $postCreate->id]);
        //////////////////////////////////////////////////////////////////
        $postCreate = Post::create(
            [
                'store_id' => $store_id,
                'title' => 'Chính sách hỗ trợ',
                'image_url' => 'https://i.imgur.com/oCmjI0r.jpg',
                'summary' => 'Chính sách hỗ trợ',
                'content' => DataPostExample::getSupportPolicy($store_name),
                'published' => true,
            ]
        );
        PostCategoryPost::create(
            [
                'post_id' => $postCreate->id,
                'categorypost_id' => $categoryCreate->id
            ]
        );
        $webThemeSaved->update(['post_id_support_policy' => $postCreate->id]);
        //////////////////////////////////////////////////////////////////
        $postCreate = Post::create(
            [
                'store_id' => $store_id,
                'title' => 'Chính sách bảo mật',
                'image_url' => 'https://i.imgur.com/NeXik0E.jpg',
                'summary' => 'Chính sách bảo mật',
                'content' => DataPostExample::getPrivacyPolicy($store_name),
                'published' => true,
            ]
        );
        PostCategoryPost::create(
            [
                'post_id' => $postCreate->id,
                'categorypost_id' => $categoryCreate->id
            ]
        );
        $webThemeSaved->update(['post_id_privacy_policy' => $postCreate->id]);
        //////////////////////////////////////////////////////////////////

        $postCreate = Post::create(
            [
                'store_id' => $store_id,
                'title' => 'Điều khoản điều kiện',
                'image_url' => 'https://i.imgur.com/0NQhkzs.jpg',
                'summary' => 'Điều khoản điều kiện',
                'content' => DataPostExample::getTermConditions($store_name),
                'published' => true,
            ]
        );
        PostCategoryPost::create(
            [
                'post_id' => $postCreate->id,
                'categorypost_id' => $categoryCreate->id
            ]
        );
        echo 'ok';
    }
    //Lấy data tạo
    public function new_data_example2(Request $request)
    {

        $career = 81;
        $store_id = 85;
        $user_id = 1;
        $rawXML = null;
        try {
            $rawXML =  Storage::get('new_store_data/store_' . $career . '.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
        } catch (\Exception $e) {
        }

        if ($rawXML  === null) {
            return;
        } else {

            $xml = simplexml_load_string($rawXML);

            $json = json_encode($xml);
            $array = json_decode($json, true);


            $i = 0;
            foreach ($array["products"]['product'] as $product) {
                $array["products"]['product'][$i]["description"] = $xml->products->product[$i]->description->__toString();
                $i++;
            }


            $store = Store::where('id', $store_id)->first();

            //Logo
            $store->update([
                'logo_url' => $array['logo']
            ]);
            //Banner
            foreach ($array["banners"]['banner'] as $banner) {
                CarouselAppImage::create([
                    "title" => "",
                    "image_url" => $banner,
                    "store_id" => $store_id
                ]);
            }

            //AppTheme

            $appThemeExists = AppTheme::where(
                'user_id',
                $user_id
            )->where(
                'store_id',
                $store_id
            )->first();

            $appThemeUpdate = [
                'store_id' => $store_id,
                'user_id' => $user_id,
                'logo_url' => $array['logo'],
                'color_main_1' => $array['color_main_1'],
                'is_scroll_button' => 0,
                'type_button' => 0

            ];
            if ($appThemeExists !== null) {


                $appThemeExists->update(
                    $appThemeUpdate
                );
            } else {


                AppTheme::create(
                    $appThemeUpdate
                );
            }

            //Home Buttons
            //Add item
            // if ($array["home_buttons"]['home_button'] != null && is_array($array["home_buttons"]['home_button'])) {


            //     $homeButtons = [];
            //     foreach ($array["home_buttons"]['home_button'] as $homeButton) {

            //         $image_url = $homeButton["image_url"] ?? null;
            //         $title = $homeButton["title"] ?? null;
            //         $type_action = $homeButton["type_action"] ?? null;
            //         $value = $homeButton["value"] ?? null;

            //         if (isset($type_action) && isset($title)) {
            //             array_push($homeButtons, [
            //                 "title" => $title,
            //                 "type_action" => $type_action,
            //                 "value" => $value,
            //                 "image_url" => $image_url,
            //             ]);
            //         }
            //     }

            //     $homeButtonExists = HomeButton::where(
            //         'store_id',
            //         $store_id
            //     )->first();


            //     $json_buttons = json_encode($homeButtons);

            //     if ($homeButtonExists !== null) {
            //         $homeButtonExists->update(
            //             [
            //                 "json_buttons" =>  $json_buttons
            //             ]
            //         );
            //     } else {

            //         HomeButton::create(
            //             [
            //                 'store_id' =>  $store_id,
            //                 "json_buttons" => " $json_buttons "
            //             ]
            //         );
            //     }
            // }


            //Category
            $save_cate = [];
            foreach ($array["categories"]['category'] as $category) {
                $categoryCreate = Category::create(
                    [
                        'image_url' => $category['image'],
                        'name' => $category['name'],
                        'store_id' => $store_id
                    ]
                );
                $save_cate[$category['id']] =  $categoryCreate->id;
            }
            ///////////
            $attributes = [];
            foreach ($array["attributes"]['attribute'] as $attribute) {
                array_push($attributes, $attribute);
            }
            $fields = json_encode($attributes);

            $attributeFieldExists = AttributeField::where(
                'store_id',
                $store_id
            )->first();

            if (empty($attributeFieldExists)) {
                AttributeField::create(
                    [
                        'store_id' => $store_id,
                        'fields' =>  $fields
                    ]
                );
            } else {
                $attributeFieldExists->update(
                    [
                        'fields' =>  $fields
                    ]
                );
            }
            ///////////
            foreach ($array["products"]['product'] as $product) {
                $name = $product["name"];
                $description = $product["description"];
                $price = floatval($product["price"]);
                $quantity_in_stock = floatval($product["quantity_in_stock"]);


                $productCreate = Product::create(
                    [
                        'description' => $description,
                        'name' => $name,
                        'store_id' => $store_id,
                        'price' => $price,

                        'status' => 0,
                        'quantity_in_stock' => $quantity_in_stock
                    ]
                );


                if ($product["images"]['image'] !== null && count((array)$product["images"]['image']) > 0) {

                    foreach ((array)$product["images"]['image'] as $image) {
                        ProductImage::create(
                            [
                                'image_url' => $image,
                                'product_id' => $productCreate->id,
                            ]
                        );
                    }
                }

                ProductCategory::create(
                    [
                        'product_id' => $productCreate->id,
                        'category_id' => (int)   $save_cate[$product["categories"]]
                    ]
                );

                if (isset($product['list_distribute']) && isset($product['list_distribute']['distribute']) && is_array($product['list_distribute']['distribute']) && count((array)$product['list_distribute']['distribute']) > 0) {

                    foreach ((array)$product['list_distribute']['distribute'] as $distribute) {
                        if (isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {

                            $distributeCreated = Distribute::create(
                                [
                                    'product_id' => $productCreate->id,
                                    'store_id' => $store_id,
                                    'name' => $distribute["name"],
                                ]
                            );


                            foreach ($distribute["element_distributes"] as $element_distribute) {
                                ElementDistribute::create(
                                    [
                                        'product_id' => $productCreate->id,
                                        'store_id' => $store_id,
                                        'name' => $element_distribute['name'],
                                        'image_url' => isset($element_distribute["image_url"]) ? $element_distribute["image_url"] : null,
                                        'distribute_id' => $distributeCreated->id
                                    ]
                                );
                            }

                            ProductDistribute::create(
                                [
                                    'store_id' => $store_id,
                                    'product_id' => $productCreate->id,
                                    'distribute_id' => $distributeCreated->id
                                ]
                            );
                        }
                    }
                }

                if (isset($product['list_attribute']) && isset($product['list_attribute']['attribute']) && is_array($product['list_attribute']['attribute']) && count((array)$product['list_attribute']['attribute']) > 0) {

                    foreach ((array)$product['list_attribute']['attribute'] as $attribute) {
                        if (isset($attribute["name"]) && isset($attribute["value"]) != null) {
                            $distributeCreated = Attribute::create(
                                [
                                    'store_id' => $store_id,
                                    'product_id' => $productCreate->id,
                                    'name' => $attribute["name"],
                                    'value' => $attribute["value"],
                                ]
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Tạo store
     * @bodyParam name string required Tên store
     * @bodyParam store_code string required Code store (Bắt đầu bằng chữ >= 2 ký tự)
     * @bodyParam address string required Địa chỉ
     * @bodyParam id_type_of_store string required Lĩnh vực
     * @bodyParam career Ngành nghề
     * @bodyParam logo_url string logo
     */
    public function create(Request $request)
    {

        if ($request->staff != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::STAFF_CANNOT_CREATE_STORE[0],
                'msg' => MsgCode::STAFF_CANNOT_CREATE_STORE[1],
            ], 400);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+[a-zA-Z0-9_]+$/',  $request->store_code)  || strlen($request->store_code) < 2) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_CODE_STORE[0],
                'msg' => MsgCode::INVALID_CODE_STORE[1],
            ], 400);
        }


        $listCant = [
            "admin", "user", "account", "partner", "api", "quanly", "ad", "store", "data", "app", "login", "register",
            "ship", "call", "doapp", "do", "my", "contact", "web", "manage"
        ];

        if (in_array($request->store_code, $listCant)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CAN_NOT_USE[0],
                'msg' => MsgCode::CAN_NOT_USE[1],
            ], 400);
        }


        $length = Store::where(
            'user_id',
            $request->user->id
        )->count();
        $max = $request->user->create_maximum_store ?? 0;

        if ($length >= $max) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Tài khoản của bạn không thể tạo thêm cửa hàng hãy liên hệ chúng tôi để tăng cửa hàng",
            ], 400);
        }


        $checkStoreExists = Store::where(
            'store_code',
            $request->store_code
        )->first();

        if ($checkStoreExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CODE_ALREADY_EXISTS[0],
                'msg' => MsgCode::CODE_ALREADY_EXISTS[1],
            ], 400);
        }

        $date = Helper::getTimeNowDateTime();
        $interval = new DateInterval('P30D');
        $date->add($interval);

        // PushNotificationAdminJob::dispatch(
        //     "User " . $request->user->name,
        //     "Vừa tạo store " . TypeOfStoreHelper::getNameCareer($request->career) . "|" . $request->name . "|" .   $request->store_code,
        // );




        $storeCreate = Store::create(
            [
                'name' => $request->name,
                'store_code' => strtolower($request->store_code),
                'address' => $request->address,
                'id_type_of_store' => $request->id_type_of_store,
                'logo_url' => $request->logo_url,
                'career' => $request->career,
                'user_id' => $request->user->id,
                'date_expried' => $date->format("Y-m-d")
            ]
        );


        BranchUtils::getBranchDefault($storeCreate->id);

        $customerCreate = Customer::create(
            [
                'area_code' => '+84',
                'phone_number' =>  "0342362909",
                'email' => "choepro57@gmail.com",
                'password' => bcrypt("111111"),
                'name' => "IKITECH",
                'name_str_filter' => StringUtils::convert_name_lowcase("IKITECH"),
                'store_id' => $storeCreate->id,
                'official' => true,
                'sex' => 1,
            ]
        );

        // DataDemoNewStoreJobPost::dispatch(
        //     $storeCreate->id,
        //     $request->career,
        // );
        DataDemoNewStoreJobDecentralization::dispatch(
            $storeCreate->id,
            $request->career,
        );

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $storeCreate
        ], 201);
    }


    /**
     * Danh sách store
     */
    public function getAll(Request $request)
    {
        $stores = null;
        if ($request->staff != null) {
            $stores = Store::where('id', $request->staff->store_id)->get();

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $stores,
            ], 200);
        }
        if ($request->user != null) {
            $stores = Store::where('user_id', $request->user->id)->get();
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $stores,
        ], 200);
    }


    /**
     * xóa một Store
     * @urlParam  store_code required Store code cần xóa.
     */
    public function deleteOneStore(Request $request)
    {
        $codeDeleted = $request->store->store_code;
        $request->store->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['codeDeleted' => $codeDeleted],
        ], 200);
    }


    /**
     * uppdate một Store
     * @urlParam  store_code required Store code cần update
     * @bodyParam name string required Tên store
     * @bodyParam store_code string required Code store (Bắt đầu bằng chữ >= 2 ký tự)
     * @bodyParam address string required Địa chỉ
     * @bodyParam id_type_of_store string required Lĩnh vực
     * @bodyParam career Ngành nghề
     * @bodyParam logo_url string logo
     */
    public function updateOneStore(Request $request)
    {
        $request->store->update(Helper::sahaRemoveItemArrayIfNullValue([
            'name' => $request->name,
            'address' => $request->address,
            'logo_url' => $request->logo_url,
            'id_type_of_store' => $request->id_type_of_store,
            'career' => $request->career,
        ]));


        // PushNotificationAdminJob::dispatch(
        //     "User " . $request->user->name,
        //     "Vừa cập nhật thông tin store " . $request->store->name . "",
        // );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Store::where('store_code', $request->store->store_code)->first(),
        ], 200);
    }



    /**
     * get một Store
     * @urlParam  store_code required Store code cần update
     */
    public function getOneStore(Request $request)
    {

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $request->store,
        ], 200);
    }
}
