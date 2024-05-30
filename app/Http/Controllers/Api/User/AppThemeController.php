<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CacheUtils;
use App\Helper\HomeLayout;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Models\AppTheme;
use App\Models\CarouselAppImage;
use App\Models\HomeButton;
use App\Models\LayoutSort;
use App\Models\MsgCode;
use App\Models\WebTheme;
use App\Services\UploadImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;


/**
 * @group  User/AppTheme
 *
 * APIs AppTheme
 */
class AppThemeController extends Controller
{

    /**
     * Thông tin AppTheme
     * @urlParam  store_code required Store code cần lấy
     */
    public function getAppTheme(Request $request)
    {
        $columns = Schema::getColumnListing('app_themes');

        $appThemeExists = AppTheme::where(
            'store_id',
            $request->store->id
        )->first();



        $appThemeResponse = new AppTheme();

        foreach ($columns as $column) {

            if ($appThemeExists != null && array_key_exists($column, $appThemeExists->toArray())) {
                $appThemeResponse->$column =  $appThemeExists->$column;
            } else {
                $appThemeResponse->$column = null;
            }
        }



        unset($appThemeResponse['id']);
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $appThemeResponse,
        ], 200);
    }


    /**
     * Cập nhật AppTheme
     * Gửi một trong các trường lên để cập nhật
     * @urlParam  store_code required Store code cần lấy
     * @bodyParam carousel_app_images List<json>   VD: [ {image_url:"link",title:"title"} ]
     */
    public function update(Request $request)
    {

        $appThemeExists = AppTheme::where(
            'store_id',
            $request->store->id
        )->first();


        //get all column app theme
        $columns = Schema::getColumnListing('app_themes');

        $imageUrl = $request->logo_url;

        if ($request->hasFile('logo')) {
            $imageUrl = UploadImageService::uploadImage($request->logo->getRealPath());
        }


        $appThemeUpdate = [];


        foreach ($columns as $column) {

            if (isset($request->$column) && $request->$column !== null) {



                if ($request->$column === "false") {
                    $request->$column = false;
                }

                if ($request->$column === "true") {
                    $request->$column = true;
                }

                $appThemeUpdate[$column] =  $request->$column;
            } else
            if ($appThemeExists !== null && isset($appThemeExists->toArray()[$column])) {
                $appThemeUpdate[$column] =  $appThemeExists->$column;
            } else {
                $appThemeUpdate[$column] = null;
            }
        }

        if ($imageUrl !== null) {
            $appThemeUpdate["logo_url"] = $imageUrl;
        }

        if ($appThemeExists !== null) {


            $appThemeUpdate["updated_at"] = \Carbon\Carbon::now();
            $appThemeExists->update(
                $appThemeUpdate
            );
        } else {
            $appThemeUpdate['store_id'] = $request->store->id;

            AppTheme::create(
                $appThemeUpdate
            );
        }


        //get all images carousel
        $carouselImagesRequest = $request->carousel_app_images;
        $carouselImages = [];


        //Add carousel
        if ($carouselImagesRequest !== null && is_array($carouselImagesRequest)) {

            foreach ($carouselImagesRequest as $imageRequest) {

                $image_url = $imageRequest["image_url"] ?? "";
                $title = $imageRequest["title"] ?? "";
                $link_to = $imageRequest["link_to"] ?? "";

                if (isset($image_url)) {
                    array_push($carouselImages, [
                        "title" => $title,
                        "image_url" => $image_url,
                        "link_to" => $link_to,
                        "store_id" => $request->store->id
                    ]);
                }
            }
        }
        if (count($carouselImages) >= 0) {

            CarouselAppImage::where("store_id", $request->store->id)->delete();

            foreach ($carouselImages as $carouselImage) {

                CarouselAppImage::create($carouselImage);
            }
        }

        $appThemeSaved = AppTheme::where(
            'store_id',
            $request->store->id
        )->first();

        if ($appThemeSaved != null) {

            $appThemeSaved->update([
                "contact_address" => $request->contact_address,

                "contact_email" => $request->contact_email,
                "contact_phone_number" => $request->contact_phone_number,
                "contact_time_work" => $request->contact_time_work,
                "contact_fanpage" => $request->contact_fanpage,

                "phone_number_hotline" => $request->phone_number_hotline,
                "id_facebook" => $request->id_facebook,
                "id_zalo" => $request->id_zalo,

                "is_show_icon_facebook" => $request->is_show_icon_facebook,
                "is_show_icon_hotline" => $request->is_show_icon_hotline,
                "is_show_icon_zalo" => $request->is_show_icon_zalo,
            ]);
        }

        $webThemeSaved = WebTheme::where(
            'store_id',
            $request->store->id
        )->first();
        if ($webThemeSaved != null) {

            $webThemeSaved->update([
                "contact_address" => $request->contact_address,

                "contact_email" => $request->contact_email,
                "contact_phone_number" => $request->contact_phone_number,
                "contact_time_work" => $request->contact_time_work,
                "contact_fanpage" => $request->contact_fanpage,

                "phone_number_hotline" => $request->phone_number_hotline,
                "id_facebook" => $request->id_facebook,
                "id_zalo" => $request->id_zalo,

                "is_show_icon_facebook" => $request->is_show_icon_facebook,
                "is_show_icon_hotline" => $request->is_show_icon_hotline,
                "is_show_icon_zalo" => $request->is_show_icon_zalo,
            ]);
        }
        unset($appThemeSaved['id']);


        PushNotificationAdminJob::dispatch(
            "User ",
            "Vừa cập nhật giao diện app " . $request->store->name . "|" . $request->store->store_code,
        );


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_THEME,
            "Cập nhật giao diện app",
            $webThemeSaved->id,
            $webThemeSaved->home_page_type
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $appThemeSaved
        ], 201);
    }


    // QR VOURCHER PRODUCTS_NEW SCORE MESSAGE_TO_SHOP
    static function home_button_default()
    {
        return [
            [
                "title" => "Quét mã QR",
                "type_action" => "QR"
            ],
            [
                "title" => "Sản phẩm mới",
                "type_action" => "PRODUCTS_NEW"
            ],
            [
                "title" => "Tích điểm",
                "type_action" => "SCORE"
            ],
            [
                "title" => "Nhắn tin cho shop",
                "type_action" => "MESSAGE_TO_SHOP"
            ],
            [
                "title" => "Voucher",
                "type_action" => "VOUCHER"
            ],
            [
                "title" => "Thưởng sản phẩm",
                "type_action" => "BONUS_PRODUCT"
            ],
        ];
    }

    /**
     * Cập nhật Home Button
     * Gửi danh sách button lên type_action gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
     *
     * @bodyParam home_buttons List<json>   VD: [ {image_url:"link",title:"title", type_action:"PRODUCT", value:"gia trị thực thi"} ]  
     */
    public function update_home_buttons(Request $request)
    {

        $homeButtonsRequest = $request->home_buttons;
        $homeButtons = [];


        //Add item
        if ($homeButtonsRequest != null && is_array($homeButtonsRequest)) {

            foreach ($homeButtonsRequest as $homeButton) {

                $image_url = $homeButton["image_url"] ?? null;
                $title = $homeButton["title"] ?? null;
                $type_action = $homeButton["type_action"] ?? null;
                $value = $homeButton["value"] ?? null;

                if (isset($type_action) && isset($title)) {
                    array_push($homeButtons, [
                        "title" => $title,
                        "type_action" => $type_action,
                        "value" => $value,
                        "image_url" => $image_url,
                    ]);
                }
            }
        }

        $homeButtonExists = HomeButton::where(
            'store_id',
            $request->store->id
        )->first();


        $json_buttons = json_encode($homeButtons);

        if ($homeButtonExists !== null) {
            $homeButtonExists->update(
                [
                    "json_buttons" =>  $json_buttons
                ]
            );
        } else {

            HomeButton::create(
                [
                    'store_id' =>  $request->store->id,
                    "json_buttons" => " $json_buttons "
                ]
            );
        }

        $homeButtonSaved = HomeButton::where(
            'store_id',
            $request->store->id
        )->first();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $homeButtonSaved
        ], 200);
    }

    // QR VOURCHER PRODUCTS_NEW SCORE MESSAGE_TO_SHOP
    static function layouts_default()
    {
        return [
            [
                "title" => "Chức năng chính",
                "type_layout" => HomeLayout::BUTTONS,
                "type_action_more" => null,
                "model" => "HomeButton"
            ],
            [
                "title" => "Danh mục sản phẩm",
                "type_layout" => HomeLayout::CATEGORY,
                "type_action_more" => null,
                "model" => "Category"
            ],
            [
                "title" => "Sản phẩm giảm giá",
                "type_layout" => HomeLayout::PRODUCTS_DISCOUNT,
                "type_action_more" => "PRODUCTS_DISCOUNT",
                "model" => "DiscountProductsList"
            ],
            [
                "title" => "Sản phẩm bán chạy",
                "type_layout" => HomeLayout::PRODUCTS_TOP_SALES,
                "type_action_more" => "PRODUCTS_TOP_SALES",
                "model" => "Product"
            ],
            [
                "title" => "Sản phẩm mới",
                "type_layout" => HomeLayout::PRODUCTS_NEW,
                "type_action_more" => "PRODUCTS_NEW",
                "model" => "Product"
            ],
            [
                "title" => "Tin tức bài viết",
                "type_layout" => HomeLayout::POSTS_NEW,
                "type_action_more" => "CATEGORY_POST",
                "model" => "Post"
            ],
            [
                "title" => "Sản phẩm theo danh mục",
                "type_layout" => HomeLayout::PRODUCT_BY_CATEGORY,
                "type_action_more" => "PRODUCT_BY_CATEGORY",
                "model" => "Product"
            ],
        ];
    }

    static function standard($layouts)
    {


        $define_model = [

            HomeLayout::BUTTONS => "HomeButton",

            HomeLayout::CATEGORY => "Category",

            HomeLayout::PRODUCTS_DISCOUNT => "Product",

            HomeLayout::PRODUCTS_TOP_SALES => "Product",

            HomeLayout::PRODUCTS_NEW => "Product",

            HomeLayout::POSTS_NEW => "Post"

        ];



        $type_layout_default = array_map(function ($item) {
            return $item["type_layout"] ?? null;
        }, AppThemeController::layouts_default());

        if ($layouts == null || !is_array($layouts)) $layouts = [];
        $layouts = json_encode($layouts);
        $layouts = json_decode($layouts);


        $type_layout_request = array_map(function ($item) {
            return $item->type_layout  ?? "";
        }, $layouts);


        $layout_rt = [];

        foreach ($layouts as $layout) {
            if ($layout->type_layout == HomeLayout::PRODUCT_BY_CATEGORY && $layout->title != "Sản phẩm theo danh mục") {
                continue;
            }

            if (HomeLayout::PRODUCT_BY_CATEGORY == $layout->type_layout || (isset($layout->type_layout) && in_array($layout->type_layout,   $type_layout_default))) {

                $model = $define_model[$layout->type_layout] ?? null;
                if (HomeLayout::PRODUCT_BY_CATEGORY == $layout->type_layout) {
                    $model = "Product";
                }

                array_push(
                    $layout_rt,
                    [
                        "title" => $layout->title ?? "",
                        "type_layout" => $layout->type_layout ?? "",
                        "type_action_more" => $layout->type_action_more ?? null,
                        "hide" =>  filter_var($layout->hide ?? false, FILTER_VALIDATE_BOOLEAN),
                        "model" =>   $model
                    ]
                );
            }
        }


        foreach (AppThemeController::layouts_default() as $layout) {
            if (!isset($define_model[$layout["type_layout"]]) || ($layout["type_layout"] == HomeLayout::PRODUCT_BY_CATEGORY && $layout["title"] != "Sản phẩm theo danh mục")) {
                continue;
            }
            if (isset($layout["type_layout"]) && !in_array($layout["type_layout"], $type_layout_request)) {
                array_push(
                    $layout_rt,
                    [
                        "title" => $layout["title"] ?? "",
                        "type_layout" => $layout["type_layout"] ?? "",
                        "type_action_more" => $layout["type_action_more"] ?? null,
                        "hide" =>  filter_var($layout["hide"] ?? false, FILTER_VALIDATE_BOOLEAN),
                        "model" =>   $define_model[$layout["type_layout"]]
                    ]
                );
            }
        }
        $hasProByCate = false;
        foreach ($layout_rt as $layout) {
            if ($layout['type_layout'] == HomeLayout::PRODUCT_BY_CATEGORY) {
                $hasProByCate = true;
            }
        }

        if ($hasProByCate  == false) {
            $add = AppThemeController::layouts_default()[6];
            $add['hide'] =
                array_push(
                    $layout_rt,
                    $add
                );
        }




        return  $layout_rt;
    }

    /**
     * Cập nhật thứ tự danh sách layout (bố cục)
     * Định nghĩa type_layout gồm: BUTTONS (model HomeButton),PRODUCTS_DISCOUNT(model Product),PRODUCTS_TOP_SALES(model Product),PRODUCTS_NEW (model Product),POSTS_NEW (model Posy),
     * Định nghĩa type_action_more:  PRODUCTS_DISCOUNT, PRODUCTS_TOP_SALES, PRODUCTS_NEW, CATEGORY_POST
     * Truyền đầy đủ danh sách trong đủ item là json gồm title, type_layout, type_action_more, hide (ko truyền sẽ mặc định hiển thị)
    
     * @bodyParam layouts List<json>   VD: [ {title:"title", type_layout:"PRODUCTS_DISCOUNT",type_action_more:"PRODUCTS_DISCOUNT",} ]  
     */
    public function update_layout_sort(Request $request)
    {

        $layoutsRequest = $request->layouts;
        $layouts = [];


        //Add item
        if ($layoutsRequest != null && is_array($layoutsRequest)) {

            foreach ($layoutsRequest as $layout) {

                $title = $layout["title"] ?? null;
                $type_layout = $layout["type_layout"] ?? null;
                $type_action_more = $layout["type_action_more"] ?? null;
                $hide = $layout["hide"] ?? null;


                if (isset($type_layout) && isset($title)) {

                    array_push($layouts, [
                        "title" => $title,
                        "type_layout" => $type_layout,
                        "type_action_more" => $type_action_more,
                        "hide" => $hide
                    ]);
                }
            }
        }



        $layoutExists = LayoutSort::where(
            'store_id',
            $request->store->id
        )->first();

        $layouts = AppThemeController::standard($layouts);

        $json_layouts = json_encode($layouts);

        if ($layoutExists !== null) {
            $layoutExists->update(
                [
                    "json_layouts" =>  $json_layouts
                ]
            );
        } else {

            LayoutSort::create(
                [
                    'store_id' =>  $request->store->id,
                    "json_layouts" => $json_layouts
                ]
            );
        }

        $layoutSaved = LayoutSort::where(
            'store_id',
            $request->store->id
        )->first();

        Cache::forget(json_encode([CacheUtils::CACHE_KEY_LAYOUT_APP,  $request->store->id]));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $layoutSaved
        ], 200);
    }

    /**
     * lấy ds Home Button
     * Gửi danh sách button lên type_action gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
     *
     * @bodyParam home_buttons List<json>   VD: [ {image_url:"link",title:"title", type_action:"PRODUCT", value:"gia trị thực thi"} ]  
     */
    public function get_home_buttons(Request $request)
    {
        $homeButtonSaved = HomeButton::where(
            'store_id',
            $request->store->id
        )->first();
        if ($homeButtonSaved !== null) {
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $homeButtonSaved
            ], 200);
        } else {
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => []
            ], 200);
        }
    }
}
