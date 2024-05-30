<?php

namespace App\Jobs;

use App\Helper\Data\Post\DataPostExample;
use App\Models\AppTheme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Attribute;
use App\Models\AttributeField;
use App\Models\BannerAd;
use App\Models\CarouselAppImage;
use App\Models\Category;
use App\Models\CategoryPost;
use App\Models\ConfigDataExample;
use App\Models\Decentralization;
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
use Illuminate\Support\Facades\Storage;

class DataDemoNewStoreJobDecentralization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 4;

    protected $store_id;
    protected $career;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_id, $career)
    {
        $this->store_id = $store_id;
        $this->career = $career;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $configDataDB = ConfigDataExample::where('type_id', $this->career)->first();
        if ($configDataDB != null) {

            $store = Store::where('store_code', $configDataDB->store_code)->first();
            $this->handleWithDataDB($store);
        } else {
            $configDataDB = ConfigDataExample::first();
            $store = Store::where('store_code', $configDataDB->store_code)->first();

            $this->handleWithDataDB($store);
        }

        echo 'store_id: ' . ($this->store_id ?? "?") . 'career: ' . ($this->career ?? "?") . 'configDataDB store_code: ' . ($configDataDB->store_code ?? "?") . 'ok';

        $this->handle2();
    }


    public function handle2()
    {
        $career =  $this->career;
        $store_id =  $this->store_id;

        $store = Store::where('id', $store_id)->first();

        $created = Decentralization::create(
            [
                "store_id" => $store_id,
                "name" => "Quản lý",
                "description" => "Quản lý cấp cao",
                "product_list" => true,
                "product_add" => true,
                "product_update" => true,
                "product_remove_hide" => true,
                "product_category_list" => true,
                "product_category_add" => true,
                "product_category_update" => true,
                "product_category_remove" => true,
                "product_attribute_list" => true,
                "product_attribute_add" => true,
                "product_attribute_update" => true,
                "product_attribute_remove" => true,
                "product_ecommerce" => true,
                "product_import_from_excel" => true,
                "product_export_to_excel" => true,
                "customer_list" => true,
                "customer_config_point" => true,
                "customer_review_list" => true,
                "customer_review_censorship" => true,
                "customer_role_edit" => true,
                "promotion_discount_list" => true,
                "promotion_discount_add" => true,
                "promotion_discount_update" => true,
                "promotion_discount_end" => true,
                "promotion_voucher_list" => true,
                "promotion_voucher_add" => true,
                "promotion_voucher_update" => true,
                "promotion_voucher_end" => true,
                "promotion_combo_list" => true,
                "promotion_combo_add" => true,
                "promotion_combo_update" => true,
                "promotion_combo_end" => true,
                "post_list" => true,
                "post_add" => true,
                "post_update" => true,
                "post_remove_hide" => true,
                "post_category_list" => true,
                "post_category_add" => true,
                "post_category_update" => true,
                "post_category_remove" => true,
                "app_theme_edit" => true,
                "app_theme_main_config" => true,
                "app_theme_button_contact" => true,
                "app_theme_home_screen" => true,
                "app_theme_main_component" => true,
                "app_theme_category_product" => true,
                "app_theme_product_screen" => true,
                "app_theme_contact_screen" => true,
                "web_theme_edit" => true,
                "web_theme_overview" => true,
                "web_theme_contact" => true,
                "web_theme_help" => true,
                "web_theme_footer" => true,
                "web_theme_banner" => true,
                "delivery_pick_address_list" => true,
                "delivery_pick_address_update" => true,
                "delivery_provider_update" => true,
                "payment_list" => true,
                "payment_on_off" => true,
                "notification_schedule_list" => true,
                "notification_schedule_add" => true,
                "notification_schedule_remove_pause" => true,
                "notification_schedule_update" => true,
                "popup_list" => true,
                "popup_add" => true,
                "popup_update" => true,
                "popup_remove" => true,
                "order_list" => true,
                "order_allow_change_status" => true,
                "collaborator_config" => true,
                "collaborator_list" => true,
                "collaborator_payment_request_list" => true,
                "collaborator_payment_request_solve" => true,
                "collaborator_payment_request_history" => true,
                "collaborator_add_sub_balance" => true,
                "notification_to_stote" => true,
                "chat_list" => true,
                "chat_allow" => true,
                "report_view" => true,
                "report_overview" => true,
                "report_product" => true,
                "report_order" => true,
                "decentralization_list" => true,
                "decentralization_update" => true,
                "decentralization_add" => true,
                "decentralization_remove" => true,
                "staff_list" => true,
                "staff_update" => true,
                "staff_add" => true,
                "staff_remove" => true,
                "staff_delegating" => true,


            ]
        );


        $created = Decentralization::create(
            [
                "store_id" => $store_id,
                "name" => "Nhân viên bán hàng",
                "description" => "Nhân viên quản lý đơn hàng",
                "product_list" => false,
                "product_add" => false,
                "product_update" => false,
                "product_remove_hide" => false,
                "product_category_list" => false,
                "product_category_add" => false,
                "product_category_update" => false,
                "product_category_remove" => false,
                "product_attribute_list" => false,
                "product_attribute_add" => false,
                "product_attribute_update" => false,
                "product_attribute_remove" => false,
                "product_ecommerce" => false,
                "product_import_from_excel" => false,
                "product_export_to_excel" => false,
                "customer_list" => false,
                "customer_config_point" => false,
                "customer_review_list" => false,
                "customer_review_censorship" => false,
                "promotion_discount_list" => false,
                "promotion_discount_add" => false,
                "promotion_discount_update" => false,
                "promotion_discount_end" => false,
                "promotion_voucher_list" => false,
                "promotion_voucher_add" => false,
                "promotion_voucher_update" => false,
                "promotion_voucher_end" => false,
                "promotion_combo_list" => false,
                "promotion_combo_add" => false,
                "promotion_combo_update" => false,
                "promotion_combo_end" => false,
                "post_list" => false,
                "post_add" => false,
                "post_update" => false,
                "post_remove_hide" => false,
                "post_category_list" => false,
                "post_category_add" => false,
                "post_category_update" => false,
                "post_category_remove" => false,
                "app_theme_edit" => false,
                "app_theme_main_config" => false,
                "app_theme_button_contact" => false,
                "app_theme_home_screen" => false,
                "app_theme_main_component" => false,
                "app_theme_category_product" => false,
                "app_theme_product_screen" => false,
                "app_theme_contact_screen" => false,
                "web_theme_edit" => false,
                "web_theme_overview" => false,
                "web_theme_contact" => false,
                "web_theme_help" => false,
                "web_theme_footer" => false,
                "web_theme_banner" => false,
                "delivery_pick_address_list" => false,
                "delivery_pick_address_update" => false,
                "delivery_provider_update" => false,
                "payment_list" => false,
                "payment_on_off" => false,
                "notification_schedule_list" => false,
                "notification_schedule_add" => false,
                "notification_schedule_remove_pause" => false,
                "notification_schedule_update" => false,
                "popup_list" => false,
                "popup_add" => false,
                "popup_update" => false,
                "popup_remove" => false,
                "order_list" => true,
                "order_allow_change_status" => true,
                "collaborator_config" => false,
                "collaborator_list" => false,
                "collaborator_payment_request_list" => false,
                "collaborator_payment_request_solve" => false,
                "collaborator_payment_request_history" => false,
                "notification_to_stote" => true,
                "chat_list" => true,
                "chat_allow" => true,
                "report_view" => true,
                "report_overview" => true,
                "report_product" => true,
                "report_order" => true,
                "decentralization_list" => false,
                "decentralization_update" => false,
                "decentralization_add" => false,
                "decentralization_remove" => false,
                "staff_list" => false,
                "staff_update" => false,
                "staff_add" => false,
                "staff_remove" => false,
                "staff_delegating" => false,
            ]
        );

        echo 'ok';
    }


    public function handleWithDataDB($store)
    {

        $storeNew = Store::where('id', $this->store_id)->first();


        //Thêm thuộc tính 
        $fields = json_encode(["Thương hiệu", "Xuất xứ"]);
        $attributeFieldExists = AttributeField::where(
            'store_id',
            $this->store_id
        )->first();
        if (empty($attributeFieldExists)) {
            AttributeField::create(
                [
                    'store_id' =>     $this->store_id,
                    'fields' =>  $fields
                ]
            );
        }

        //Xu ly app theme
        $appTheme = AppTheme::where('store_id', $store->id)->first();

        if ($appTheme != null) {
            $appThemeNew  =   $appTheme->replicate();
            $appThemeNew->store_id =  $this->store_id;
            $appThemeNew->save();
        }

        //Xu ly web theme
        $webTheme = WebTheme::where('store_id', $store->id)->first();

        if ($webTheme != null) {
            $webThemeNew  =   $webTheme->replicate();
            $webThemeNew->store_id =  $this->store_id;
            $webThemeNew->domain =  "";
            $webThemeNew->save();
        }

        //Carousel App
        $carousels = CarouselAppImage::where('store_id', $store->id)->get();
        foreach ($carousels as  $carousel) {
            if ($carousel != null) {
                $carouselNew  =   $carousel->replicate();
                $carouselNew->store_id =  $this->store_id;
                $carouselNew->save();
            }
        }

        //Banner ADS
        $banes = BannerAd::where('store_id', $store->id)->get();
        foreach ($banes as  $bane) {
            if ($bane != null) {
                $baneNew  =   $bane->replicate();
                $baneNew->store_id =  $this->store_id;
                $baneNew->save();
            }
        }

        //Xu ly post
        $posts = Post::where('store_id', $store->id)->take(15)->get()->reverse();
        foreach ($posts as $post) {
            $newPost = $post->replicate();
            $newPost->store_id =  $this->store_id;
            $newPost->save();

            //Liên kết post vs cate
            $postCategory = PostCategoryPost::where('post_id', $post->id)->first();
            if ($postCategory != null) {

                $category = CategoryPost::where('id', $postCategory->categorypost_id)->first();

                if ($category != null) {

                    $categoryNew = CategoryPost::where('title', $category->title)->where('store_id', $this->store_id)->first();

                    if ($categoryNew == null) {
                        $categoryNew  =   $category->replicate();
                        $categoryNew->store_id =  $this->store_id;
                        $categoryNew->save();
                    }

                    PostCategoryPost::create([
                        'post_id' =>  $newPost->id,
                        'categorypost_id' => $categoryNew->id
                    ]);
                }
            }
        }

        //Banner ADS
        $categories = Category::where('store_id', $store->id)->get();
        foreach ($categories as  $category) {
            if ($category != null) {
                $categoryNew  =   $category->replicate();
                $categoryNew->store_id =  $this->store_id;
                $categoryNew->save();
            }
        }

        $products = Product::where('store_id', $store->id)->take(50)->get()->reverse();
        foreach ($products as $product) {
            $newProduct = $product->replicate();
            $newProduct->store_id =  $this->store_id;
            $newProduct->save();

            $productImages = ProductImage::where('product_id', $product->id)
                ->get();
            foreach ($productImages as $productImage) {
                ProductImage::create([
                    'product_id' => $newProduct->id,
                    'image_url' => $productImage->image_url
                ]);
            }

            //Liên kết product vs cate
            $productCategories = ProductCategory::where('product_id', $product->id)->get();
            if (count($productCategories) > 0) {

                foreach ($productCategories as  $productCategory) {
                    $category = Category::where('id', $productCategory->category_id)->first();

                    if ($category != null) {

                        $categoryNew = Category::where('name', $category->name)->where('store_id', $this->store_id)->first();

                        if ($categoryNew == null) {
                            $categoryNew = $category->replicate();
                            $categoryNew->store_id = $this->store_id;
                            $categoryNew->save();
                        }

                        ProductCategory::create([
                            'product_id' =>  $newProduct->id,
                            'category_id' => $categoryNew->id
                        ]);
                    }
                }
            }
        }
    }
}