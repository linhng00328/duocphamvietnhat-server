<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_themes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            //Main config
            $table->string('logo_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('image_share_web_url')->nullable();
            $table->string('domain')->nullable();
            $table->string('home_title')->nullable();
            $table->string('home_description')->nullable();

            $table->boolean('is_show_logo')->nullable();

            $table->string('color_main_1')->nullable();
            $table->string('color_main_2')->nullable();

            $table->string('font_color_all_page')->nullable();
            $table->string('font_color_title')->nullable();
            $table->string('font_color_main')->nullable();
            $table->string('font_family')->nullable();

            //icon support
            $table->string('icon_hotline')->nullable();
            $table->boolean('is_show_icon_hotline')->nullable();
            $table->string('note_icon_hotline')->nullable();
            $table->string('phone_number_hotline')->nullable();

            $table->string('icon_email')->nullable();
            $table->boolean('is_show_icon_email')->nullable();
            $table->string('title_popup_icon_email')->nullable();
            $table->string('title_popup_success_icon_email')->nullable();
            $table->string('email_contact')->nullable();
            $table->string('body_email_success_icon_email')->nullable();

            $table->string('icon_facebook')->nullable();
            $table->boolean('is_show_icon_facebook')->nullable();
            $table->string('note_icon_facebook')->nullable();
            $table->string('id_facebook')->nullable();

            $table->string('icon_zalo')->nullable();
            $table->boolean('is_show_icon_zalo')->nullable();
            $table->string('note_icon_zalo')->nullable();
            $table->string('id_zalo')->nullable();

            $table->boolean('is_show_icon_tiktok')->nullable();
            $table->string('id_tiktok')->nullable();

            $table->boolean('is_show_icon_youtube')->nullable();
            $table->string('id_youtube')->nullable();

            $table->boolean('is_show_icon_ministry_of_industry_and_trade')->nullable();
            $table->string('link_ministry_of_industry_and_trade')->nullable();
            $table->string('content_ministry_of_industry_and_trade')->nullable();

            //button_home
            $table->boolean('is_scroll_button')->nullable();
            $table->integer('type_button')->nullable();

            //Header style
            $table->integer('header_type')->nullable();
            $table->string('color_background_header')->nullable();
            $table->string('color_text_header')->nullable();

            //Component main
            $table->integer('type_navigator')->nullable();
            $table->integer('type_loading')->nullable();

            //Search style
            $table->integer('type_of_menu')->nullable();

            $table->integer('product_item_type')->nullable();
            $table->string('search_background_header')->nullable();
            $table->string('search_text_header')->nullable();

            $table->integer('carousel_type')->nullable();
            $table->integer('home_id_carousel_app_image')->nullable();
            $table->boolean('home_list_category_is_show')->nullable();
            $table->integer('home_id_list_category_app_image')->nullable();

            $table->boolean('home_top_is_show')->nullable();
            $table->string('home_top_text')->nullable();
            $table->string('home_top_color')->nullable();
            $table->boolean('home_carousel_is_show')->nullable();

            //Home
            $table->integer('home_page_type')->nullable();


            //Category
            $table->integer('category_page_type')->nullable();

            //Product
            $table->integer('product_page_type')->nullable();
            $table->boolean('is_show_same_product')->nullable();

            $table->boolean('is_show_product_new')->default(1)->nullable();
            $table->boolean('is_show_product_top_sale')->default(1)->nullable();
            $table->boolean('is_show_product_sold')->default(1)->nullable();
            $table->boolean('is_show_product_view')->default(0)->nullable();
            $table->boolean('is_show_product_count_stars')->default(0)->nullable();

            //Id post
            $table->boolean('is_show_list_post_contact')->default(1)->nullable();
            $table->integer('post_id_help')->nullable();
            $table->integer('post_id_contact')->nullable();
            $table->integer('post_id_about')->nullable();
            $table->integer('post_id_terms')->nullable();
            $table->integer('post_id_return_policy')->nullable();
            $table->integer('post_id_support_policy')->nullable();
            $table->integer('post_id_privacy_policy')->nullable();
            $table->integer('post_id_delivery_policy')->nullable();
            $table->integer('post_id_participating')->nullable();
            $table->integer('post_id_payment_policy')->nullable();
            $table->integer('post_id_goods_inspecstion_policy')->nullable();

            //Contact
            $table->integer('contact_page_type')->nullable();
            $table->string('contact_google_map')->nullable();
            $table->longText('contact_address')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone_number')->nullable();
            $table->longText('contact_individual_organization_name')->nullable();
            $table->string('contact_short_description')->nullable();
            $table->string('contact_business_registration_certificate')->nullable();
            $table->string('contact_time_work')->nullable();
            $table->string('contact_info_bank')->nullable();
            $table->string('contact_fanpage')->nullable();

            //Style
            $table->integer('banner_type')->nullable();
            $table->integer('product_home_type')->nullable();
            $table->integer('post_home_type')->nullable();
            $table->integer('footer_type')->nullable();
            $table->boolean('is_use_footer_html')->default(false)->nullable();


            $table->longText("html_footer")->nullable();

            $table->boolean('no_use_sub_domain')->default(false)->nullable();
            $table->string("google_tag_manager_id")->nullable();
            $table->string("code_google_analytics")->nullable();
            $table->string("google_site_verification_code")->nullable();

            $table->boolean('is_use_custom_menu')->default(false)->nullable();
            $table->longText("json_custom_menu")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_themes');
    }
}
