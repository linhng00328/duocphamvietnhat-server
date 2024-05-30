<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDecentralizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('decentralizations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_id')->unsigned()->index();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->string("name")->nullable();
            $table->string("description")->nullable();

            $table->boolean("overview")->default(true)->nullable();
            $table->boolean("vip_edit")->default(true)->nullable();

            $table->boolean("product_list")->default(true)->nullable();
            $table->boolean("product_add")->default(true)->nullable();
            $table->boolean("product_update")->default(true)->nullable();
            $table->boolean("product_copy")->default(true)->nullable();
            $table->boolean("product_remove_hide")->default(true)->nullable();

            $table->boolean("product_category_list")->default(true)->nullable();
            $table->boolean("product_category_add")->default(true)->nullable();
            $table->boolean("product_category_update")->default(true)->nullable();
            $table->boolean("product_category_remove")->default(true)->nullable();

            $table->boolean("product_attribute_list")->default(true)->nullable();
            $table->boolean("product_attribute_add")->default(true)->nullable();
            $table->boolean("product_attribute_update")->default(true)->nullable();
            $table->boolean("product_attribute_remove")->default(true)->nullable();

            $table->boolean("product_ecommerce")->default(true)->nullable();
            $table->boolean("product_import_from_excel")->default(true)->nullable();
            $table->boolean("product_export_to_excel")->default(true)->nullable();

            $table->boolean("customer_list")->default(true)->nullable();
            $table->boolean("customer_list_export")->default(true)->nullable();
            $table->boolean("customer_list_import")->default(true)->nullable();
            $table->boolean("customer_config_point")->default(true)->nullable();
            $table->boolean("customer_review_list")->default(true)->nullable();
            $table->boolean("customer_review_censorship")->default(true)->nullable();
            $table->boolean("customer_role_edit")->default(true)->nullable();
            $table->boolean("customer_change_point")->default(true)->nullable();
            $table->boolean("customer_change_referral")->default(false)->nullable();

            $table->boolean("promotion_discount_list")->default(true)->nullable();
            $table->boolean("promotion_discount_add")->default(true)->nullable();
            $table->boolean("promotion_discount_update")->default(true)->nullable();
            $table->boolean("promotion_discount_end")->default(true)->nullable();

            $table->boolean("promotion_voucher_list")->default(true)->nullable();
            $table->boolean("promotion_voucher_add")->default(true)->nullable();
            $table->boolean("promotion_voucher_update")->default(true)->nullable();
            $table->boolean("promotion_voucher_end")->default(true)->nullable();

            $table->boolean("promotion_combo_list")->default(true)->nullable();
            $table->boolean("promotion_combo_add")->default(true)->nullable();
            $table->boolean("promotion_combo_update")->default(true)->nullable();
            $table->boolean("promotion_combo_end")->default(true)->nullable();

            $table->boolean("promotion_bonus_product_list")->default(true)->nullable();
            $table->boolean("promotion_bonus_product_add")->default(true)->nullable();
            $table->boolean("promotion_bonus_product_update")->default(true)->nullable();
            $table->boolean("promotion_bonus_product_end")->default(true)->nullable();

            $table->boolean("post_list")->default(true)->nullable();
            $table->boolean("post_add")->default(true)->nullable();
            $table->boolean("post_update")->default(true)->nullable();
            $table->boolean("post_remove_hide")->default(true)->nullable();

            $table->boolean("post_category_list")->default(true)->nullable();
            $table->boolean("post_category_add")->default(true)->nullable();
            $table->boolean("post_category_update")->default(true)->nullable();
            $table->boolean("post_category_remove")->default(true)->nullable();

            $table->boolean("app_theme_edit")->default(true)->nullable();
            $table->boolean("app_theme_main_config")->default(true)->nullable();
            $table->boolean("app_theme_button_contact")->default(true)->nullable();
            $table->boolean("app_theme_home_screen")->default(true)->nullable();
            $table->boolean("app_theme_main_component")->default(true)->nullable();
            $table->boolean("app_theme_category_product")->default(true)->nullable();
            $table->boolean("app_theme_product_screen")->default(true)->nullable();
            $table->boolean("app_theme_contact_screen")->default(true)->nullable();

            $table->boolean("web_theme_edit")->default(true)->nullable();
            $table->boolean("web_theme_overview")->default(true)->nullable();
            $table->boolean("web_theme_contact")->default(true)->nullable();
            $table->boolean("web_theme_help")->default(true)->nullable();
            $table->boolean("web_theme_footer")->default(true)->nullable();
            $table->boolean("web_theme_banner")->default(true)->nullable();
            $table->boolean("web_theme_seo")->default(true)->nullable();

            $table->boolean("delivery_pick_address_list")->default(true)->nullable();
            $table->boolean("delivery_pick_address_update")->default(true)->nullable();
            $table->boolean("delivery_provider_update")->default(true)->nullable();

            $table->boolean("payment_list")->default(true)->nullable();
            $table->boolean("payment_on_off")->default(true)->nullable();

            $table->boolean("notification_schedule_list")->default(true)->nullable();
            $table->boolean("notification_schedule_add")->default(true)->nullable();
            $table->boolean("notification_schedule_remove_pause")->default(true)->nullable();
            $table->boolean("notification_schedule_update")->default(true)->nullable();

            $table->boolean("popup_list")->default(true)->nullable();
            $table->boolean("popup_add")->default(true)->nullable();
            $table->boolean("popup_update")->default(true)->nullable();
            $table->boolean("popup_remove")->default(true)->nullable();

            $table->boolean("collaborator_config")->default(true)->nullable();
            $table->boolean("collaborator_list")->default(true)->nullable();
            $table->boolean("collaborator_view")->default(true)->nullable();
            $table->boolean("collaborator_register")->default(true)->nullable();
            $table->boolean("collaborator_top_sale")->default(true)->nullable();
            $table->boolean("collaborator_payment_request_list")->default(true)->nullable();
            $table->boolean("collaborator_payment_request_solve")->default(true)->nullable();
            $table->boolean("collaborator_payment_request_history")->default(true)->nullable();
            $table->boolean("collaborator_add_sub_balance")->default(true)->nullable();

            $table->boolean("agency_config")->default(true)->nullable();
            $table->boolean("agency_list")->default(true)->nullable();
            $table->boolean("agency_view")->default(true)->nullable();
            $table->boolean("agency_register")->default(true)->nullable();
            $table->boolean("agency_top_import")->default(true)->nullable();
            $table->boolean("agency_top_commission")->default(true)->nullable();
            $table->boolean("agency_bonus_program")->default(true)->nullable();
            $table->boolean("agency_payment_request_list")->default(true)->nullable();
            $table->boolean("agency_payment_request_solve")->default(true)->nullable();
            $table->boolean("agency_payment_request_history")->default(true)->nullable();
            $table->boolean("agency_add_sub_balance")->default(true)->nullable();

            $table->boolean("notification_to_stote")->default(true)->nullable();

            $table->boolean("chat_list")->default(true)->nullable();
            $table->boolean("chat_allow")->default(true)->nullable();

            $table->boolean("report_finance")->default(true)->nullable();
            $table->boolean("report_view")->default(true)->nullable();
            $table->boolean("report_overview")->default(true)->nullable();
            $table->boolean("report_product")->default(true)->nullable();
            $table->boolean("report_order")->default(true)->nullable();

            $table->boolean("decentralization_list")->default(true)->nullable();
            $table->boolean("decentralization_update")->default(true)->nullable();
            $table->boolean("decentralization_add")->default(true)->nullable();
            $table->boolean("decentralization_remove")->default(true)->nullable();

            $table->boolean("staff_list")->default(true)->nullable();
            $table->boolean("staff_update")->default(true)->nullable();
            $table->boolean("staff_add")->default(true)->nullable();
            $table->boolean("staff_remove")->default(true)->nullable();
            $table->boolean("staff_delegating")->default(true)->nullable();

            // $table->boolean("agency_list")->default(true)->nullable();

            $table->boolean("inventory_list")->default(true)->nullable();
            $table->boolean("inventory_import")->default(true)->nullable();
            $table->boolean("inventory_tally_sheet")->default(true)->nullable();

            $table->boolean("revenue_expenditure")->default(true)->nullable();

            $table->boolean("create_order_pos")->default(true)->nullable();
            $table->boolean("change_price_pos")->default(true)->nullable();
            $table->boolean("change_discount_pos")->default(true)->nullable();
            $table->boolean("supplier")->default(true)->nullable();
            $table->boolean("barcode_print")->default(true)->nullable();

            $table->boolean("timekeeping")->default(true)->nullable();

            $table->boolean("transfer_stock")->default(true)->nullable();

            $table->boolean("onsale")->default(true)->nullable();
            $table->boolean("train")->default(true)->nullable();
            $table->boolean("train_add")->default(true)->nullable();
            $table->boolean("train_update")->default(true)->nullable();
            $table->boolean("train_delete")->default(true)->nullable();
            $table->boolean("train_exam_list")->default(true)->nullable();
            $table->boolean("train_exam_add")->default(true)->nullable();
            $table->boolean("train_exam_update")->default(true)->nullable();
            $table->boolean("train_exam_delete")->default(true)->nullable();
            $table->boolean("train_exam_history")->default(true)->nullable();

            $table->boolean("order_list")->default(true)->nullable();
            $table->boolean("order_allow_change_status")->default(true)->nullable();
            $table->boolean("onsale_list")->default(true)->nullable();
            $table->boolean("onsale_edit")->default(true)->nullable();
            $table->boolean("onsale_add")->default(true)->nullable();
            $table->boolean("onsale_remove")->default(true)->nullable();
            $table->boolean("onsale_assignment")->default(true)->nullable();

            $table->boolean("promotion")->default(true)->nullable();
            $table->boolean("branch_list")->default(true)->nullable();
            $table->boolean("report_inventory")->default(true)->nullable();
            $table->boolean("add_revenue")->default(true)->nullable();
            $table->boolean("add_expenditure")->default(true)->nullable();

            $table->boolean("gamification")->default(true)->nullable();
            $table->boolean("sale_config")->default(true)->nullable();
            $table->boolean("sale_list")->default(true)->nullable();
            $table->boolean("sale_view")->default(true)->nullable();
            $table->boolean("sale_top")->default(true)->nullable();
            $table->boolean("sale_watching")->default(true)->nullable();

            $table->boolean("ecommerce_list")->default(true)->nullable();
            $table->boolean("ecommerce_products")->default(true)->nullable();
            $table->boolean("ecommerce_connect")->default(true)->nullable();
            $table->boolean("ecommerce_orders")->default(true)->nullable();
            $table->boolean("ecommerce_inventory")->default(true)->nullable();

            $table->boolean("config_sms")->default(true)->nullable();
            $table->boolean("config_setting")->default(true)->nullable();
            $table->boolean("config_terms_agency_collaborator")->default(true)->nullable();
            $table->boolean("invoice_template")->default(true)->nullable();
            $table->boolean("store_info")->default(true)->nullable();
            $table->boolean("product_commission")->default(true)->nullable();
            $table->boolean("order_import_from_excel")->default(true)->nullable();
            $table->boolean("order_export_to_excel")->default(true)->nullable();
            $table->boolean("banner_ads")->default(true)->nullable();
            $table->boolean("accountant_time_sheet")->default(true)->nullable();
            $table->boolean("group_customer")->default(true)->nullable();
            $table->boolean("history_operation")->default(true)->nullable();

            $table->boolean("communication_list")->default(true)->nullable();
            $table->boolean("communication_update")->default(true)->nullable();
            $table->boolean("communication_delete")->default(true)->nullable();
            $table->boolean("communication_add")->default(true)->nullable();
            $table->boolean("communication_approve")->default(true)->nullable();

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
        Schema::dropIfExists('decentralizations');
    }
}
