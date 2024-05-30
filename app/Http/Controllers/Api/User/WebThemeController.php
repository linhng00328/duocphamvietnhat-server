<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\SaveOperationHistoryUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Models\AppTheme;
use App\Models\CarouselAppImage;
use App\Models\MsgCode;
use App\Models\WebTheme;
use App\Services\UploadImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * @group  User/Web theme
 */

class WebThemeController extends Controller
{
    /**
     * get WebTheme
     * @urlParam  store_code required Store code cần lấy
     */
    public function getWebTheme(Request $request)
    {
        $columns = Schema::getColumnListing('web_themes');

        $webThemeExists = WebTheme::where(
            'store_id',
            $request->store->id
        )->first();



        $webThemeResponse = new WebTheme();

        foreach ($columns as $column) {

            if ($webThemeExists != null && array_key_exists($column, $webThemeExists->toArray())) {
                $webThemeResponse->$column =  $webThemeExists->$column;
            } else {
                $webThemeResponse->$column = null;
            }
        }



        unset($webThemeResponse['user_id']);
        unset($webThemeResponse['id']);
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $webThemeResponse,
        ], 200);
    }


    /**
     * Cập nhật WebTheme
     * 
     * Gửi một trong các trường lên để cập nhật
     * 
     * @bodyParam logo_url required Logo Chính hiển thị trên header
     * @bodyParam favicon_url required link favicon_url
     * @bodyParam image_share_web_url required link ảnh khi chia sẻ trang web đến facebook hoặc MXH
     * @bodyParam home_title required Title chính cho trang home 
     * @bodyParam home_description required Mo ta cho trang home
     * @bodyParam domain required Tên miền
     * @bodyParam is_show_logo required no
     * @bodyParam color_main_1 required Màu chính cho web
     * @bodyParam color_main_2 required no
     * @bodyParam font_color_all_page required no
     * @bodyParam font_color_title required  no
     * @bodyParam font_color_main required no
     * @bodyParam font_family required Kiểu chữ cho web
     * @bodyParam icon_hotline  required no
     * @bodyParam is_show_icon_hotline required  có show nút hotline ko
     * @bodyParam note_icon_hotline  required no
     * @bodyParam phone_number_hotline required Số điện thoại hotline chạy theo web
     * @bodyParam icon_email required no
     * @bodyParam is_show_icon_email required email có show email liên hệ chạy theo web
     * @bodyParam title_popup_icon_email required  no
     * @bodyParam title_popup_success_icon_email required  no
     * @bodyParam email_contact required địa chỉ email liên hệ  no
     * @bodyParam body_email_success_icon_email required  no
     * @bodyParam icon_facebook required  no
     * @bodyParam is_show_icon_facebook required  show icon facelieen hệ ko
     * @bodyParam note_icon_facebook required no
     * @bodyParam id_facebook required id fanpage 
     * @bodyParam icon_zalo required no
     * @bodyParam is_show_icon_zalo required show zalo ko
     * @bodyParam note_icon_zalo required  no
     * @bodyParam id_zalo id zalo required no
     * @bodyParam is_scroll_button required no
     * @bodyParam type_button required no
     * @bodyParam header_type required no
     * @bodyParam color_background_header required no
     * @bodyParam color_text_header required no
     * @bodyParam type_navigator required no
     * @bodyParam type_loading required no
     * @bodyParam type_of_menu required no
     * @bodyParam product_item_type required no
     * @bodyParam search_background_header required no
     * @bodyParam search_text_header required no
     * @bodyParam carousel_type required no
     * @bodyParam home_id_carousel_app_image required no
     * @bodyParam home_list_category_is_show required no
     * @bodyParam home_id_list_category_app_image required no
     * @bodyParam home_top_is_show required no
     * @bodyParam home_top_text required no
     * @bodyParam home_top_color required no
     * @bodyParam home_carousel_is_show required no
     * @bodyParam home_page_type required no
     * @bodyParam category_page_type required no
     * @bodyParam product_page_type required no
     * @bodyParam is_show_same_product required no
     * @bodyParam is_show_list_post_contact required boolean có show bài viết hỗ trợ không
     * @bodyParam post_id_help required id bài viết giúp đỡ
     * @bodyParam post_id_contact required id bài viết liên hệ
     * @bodyParam post_id_about required id bời viết giới thiệu
     * @bodyParam post_id_terms required id bời viết điều khoản điều kiện
     * @bodyParam post_id_return_policy required id chính sách hoàn trả
     * @bodyParam post_id_support_policy  required id chính sách hỗ trợ
     * @bodyParam post_id_privacy_policy required id chính sách bảo mật
     * @bodyParam post_id_delivery_policy required id chính sách giao hàng
     * @bodyParam post_id_payment_policy required id chính sách thanh toán
     * @bodyParam post_id_goods_inspecstion_policy required id chính sách kiểm hàng
     * @bodyParam post_id_participating required id bài viết tham gia
     * @bodyParam contact_page_type required no
     * @bodyParam contact_google_map required no
     * @bodyParam contact_address required Địa chỉ dưới footer
     * @bodyParam contact_email required email dưới footer
     * @bodyParam contact_phone_number required sdt dưới footer
     * @bodyParam contact_time_work required thời gian làm việc dưới footer
     * @bodyParam contact_info_bank required thông tin ngân hàng dưới footer
     * @bodyParam contact_individual_organization_name required Tên cá nhân hoặc tổ chức
     * @bodyParam contact_short_description required Mô tả ngắn dưới footer
     * @bodyParam contact_business_registration_certificate required Giấy đăng ký kinh doanh
     * @bodyParam contact_fanpage required fanpage dưới footer
     * @bodyParam html_footer required html tùy chỉnh dưới footer
     * @bodyParam banner_type int banner type home
     * @bodyParam product_home_type kiểu sản phẩm
     * @bodyParam post_home_type kiểu bài viết
     * @bodyParam footer_type kiểu footer
     * @bodyParam is_use_footer_html sử dụng html thay vì type footer
     * @bodyParam carousel_app_images required List<json>   VD: [ {image_url:"link",title:"title", link_to:"Link"} ] danh sach banner
     * 
     */
    public function update(Request $request)
    {

        $webThemeExists = WebTheme::where(
            'store_id',
            $request->store->id
        )->first();

        //get all column app theme
        $columns = Schema::getColumnListing('web_themes');

        $imageUrl = $request->logo_url;

        if ($request->hasFile('logo')) {
            $imageUrl = UploadImageService::uploadImage($request->logo->getRealPath());
        }


        $webThemeUpdate = [];


        foreach ($columns as $column) {


            if ($column == "id") continue;

            // if (isset($request->$column) && $request->$column !== null) {



            if ($request->$column === "false") {
                $request->$column = false;
            }

            if ($request->$column === "true") {
                $request->$column = true;
            }

            $webThemeUpdate[$column] =  $request->$column;
            // } else
            // if ($webThemeExists !== null && isset($webThemeExists->toArray()[$column])) {
            //     $webThemeUpdate[$column] =  $webThemeExists->$column;
            // } else {
            //     $webThemeUpdate[$column] = null;
            // }
        }

        if ($imageUrl !== null) {
            $webThemeUpdate["logo_url"] = $imageUrl;
        }

        $webThemeUpdate['store_id'] = $request->store->id;


        //Check param

        $domain = $request->domain;
        $domain = str_replace("https://", "", $domain);
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("/", "", $domain);

        $str = $domain;

        if (!empty($domain)) {
            if ((ctype_alnum(str_replace('-', '', $str)) && $str[0] != '-' && $str[strlen($str) - 1] != '-')) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DOMAIN[0],
                    'msg' => MsgCode::INVALID_DOMAIN[1],
                ], 400);
            }

            $checkHasDomain = WebTheme::where('domain', $domain)->where('domain', '<>', $domain)->first();

            if ($checkHasDomain != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::DUPLICATE_DOMAIN[0],
                    'msg' => MsgCode::DUPLICATE_DOMAIN[1],
                ], 400);
            }
        }


        $webThemeUpdate['domain'] = $domain;

        ////////////////////////////////////////////////////////////////////////

        if ($webThemeExists !== null) {

            $webThemeUpdate['id'] = $webThemeExists->id;

            $webThemeUpdate["updated_at"] = \Carbon\Carbon::now();
            $webThemeExists->update(
                $webThemeUpdate
            );
        } else {
            WebTheme::create(
                $webThemeUpdate
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

        $webThemeSaved = WebTheme::where(
            'store_id',
            $request->store->id
        )->first();

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


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_THEME,
            "Cập nhật giao diện web",
            $webThemeSaved->id,
            $webThemeSaved->home_page_type
        );

        PushNotificationAdminJob::dispatch(
            "User ",
            "Vừa cập nhật giao diện web " . $request->store->name . "|" . $request->store->store_code,
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $webThemeSaved
        ], 201);
    }

    function is_valid_domain_name($domain_name)
    {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)); //length of each label
    }
}
