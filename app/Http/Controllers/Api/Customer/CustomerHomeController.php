<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\CacheUtils;
use App\Helper\GroupCustomerUtils;
use App\Helper\Helper;
use App\Helper\HomeLayout;
use App\Helper\StatusGuessNumberDefineCode;
use App\Helper\StatusSpinWheelDefineCode;
use App\Http\Controllers\Api\User\AppThemeController;
use App\Http\Controllers\Controller;
use App\Http\Middleware\UpSpeed;
use App\Models\BannerAd;
use App\Models\BannerAdApp;
use App\Models\CarouselAppImage;
use App\Models\Category;
use App\Models\CategoryPost;
use App\Models\Discount;
use App\Models\GuessNumber;
use App\Models\HomeButton;
use App\Models\LayoutSort;
use App\Models\MsgCode;
use App\Models\PopupCustomer;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\SpinWheel;
use App\Models\WebTheme;
use App\Utils\ProductNew;
use App\Utils\ProductTopSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

//name
const BUTTONS = "buttons"; //Banner
const BANNER = "banner"; //Banner
const POPUPS = "popups"; //Popups

//type
const CAROUSEL = "CAROUSEL"; //Banner
const CATEGORIES = "CATEGORIES"; //Danh sách danh mục
const PRODUCTS = "PRODUCTS"; // Danh sách sản phẩm
const POSTS = "POSTS"; // Tin tức bài viết

/**
 * @group  Customer/HomeApp
 */
class CustomerHomeController extends Controller

{
    const FROM_WEB = "FROM_WEB";
    const FROM_APP = "FROM_APP";
    /**
     * Lấy giao diện home
     * 
     * @queryParam from string home từ đâu (FROM_APP,FROM_WEB)
     * 
     */
    public function getHomeApp(Request $request, $id)
    {
        if ($request->store != null && $request->store->user != null && $request->store->user->is_block == true) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Cửa hàng đã bị khóa, vui lòng liên hệ chăm sóc khách hàng!",
            ], 400);
        }
        // banner
        // category
        // sp uu dai
        // sp moi
        // s p ban chay
        // tin tuc


        $from = CustomerHomeController::FROM_WEB;
        if (request('from') != null) {
            $from = request('from');
        }



        $now = Helper::getTimeNowString();

        $topsalesId = ProductTopSale::getTopSaleProductIds($request->store->id);
        $topSell = [];
        $topsales =  Product::where('store_id', $request->store->id)
            ->where(
                'status',
                0
            )
            ->where('sold',  '>', 0)
            ->whereIn(
                'id',
                $topsalesId
            )->orderBy('sold', 'desc')->get();

        foreach ($topsalesId as $id) {
            foreach ($topsales as $topsale) {
                if ($topsale->id == $id) {
                    array_push($topSell, $topsale);
                    break;
                }
            }
        }


        $request = request();
        $customer = request('customer', $default = null);


        //product discount
        $product_dis = ProductDiscount::where('product_discounts.store_id', $request->store->id,)

            ->leftJoin('discounts', 'discounts.id', '=', 'product_discounts.discount_id')
            ->where('discounts.is_end', false)
            ->where('discounts.start_time', '<', $now)
            ->where('discounts.end_time', '>', $now)
            ->orderBy('discounts.created_at', 'desc')
            ->whereRaw('(discounts.amount - discounts.used > 0 OR discounts.set_limit_amount = false)')
            ->take(10)->get();

        $product_dis_ids_res = [];
        foreach ($product_dis  as  $product_dis_item) {

            $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                $request,
                $product_dis_item->group_customer,
                $product_dis_item->agency_type_id,
                $product_dis_item->group_type_id,
                $customer,
                $request->store->id,
                $product_dis_item->group_customers,
                $product_dis_item->agency_types,
                $product_dis_item->group_types
            );


            if ($ok_customer) {
                array_push($product_dis_ids_res, $product_dis_item->product_id);
            }
        }


        $productDiscounts =  Product::where('store_id', $request->store->id)->where(
            'status',
            0
        )->whereIn('id', $product_dis_ids_res)->get();

        //////

        $productIdNews = ProductNew::getNewProductIds($request->store->id);

        // $productIdNews =  DB::table('products')->select('id')->orderBy('id', 'desc')->where(
        //     'status',
        //     0
        // )->where('store_id', $request->store->id)->take(15)->get()->pluck('id');

        $productNews =  Product::where(
            'status',
            0
        )->where('store_id', $request->store->id)->take(15)->whereIn('id',   $productIdNews)
            ->orderBy('id', 'desc')
            ->get();

        //Button home
        $homeButtonSaved = HomeButton::where(
            'store_id',
            $request->store->id
        )->first();

        $homeButtonShow = $homeButtonSaved == null ? [] : $homeButtonSaved->buttons;
        if (count($homeButtonShow) == 0) {
            $homeButtonShow = AppThemeController::home_button_default();
        }


        //Lấy danh sách tin tức trước khi sort
        $product_by_cates = [];

        $listCateShow = Category::where('store_id', $request->store->id)->where('is_show_home', true)
            ->orderBy('position', 'ASC')->get();
        if (count($listCateShow) > 0) {
            foreach ($listCateShow  as $cateShow) {

                $products =   Product::where(
                    'products.store_id',
                    $request->store->id
                )->where(
                    'products.status',
                    0
                )
                    ->when(true, function ($query) use ($cateShow) {
                        $query->whereHas('categories', function ($query) use ($cateShow) {
                            $query->whereIn('categories.id', [$cateShow->id]);
                        });
                    })->orderBy('created_at', 'desc')->take(10)->get();

                if (count($products) > 0) {
                    $product_by_cates[$cateShow->name] = [
                        "title" => $cateShow->name,
                        "banner_ads" => $cateShow->banner_ads,
                        "model" => "Product",
                        'image_url' => $cateShow->image_url,
                        "category_children" => $cateShow->category_children,
                        "type_layout" => HomeLayout::PRODUCT_BY_CATEGORY,
                        "type_action_more" => 'PRODUCTS_TOP_SALES',
                        "value_action" => $cateShow->id,
                        "hide" => false,
                        "list" =>   $products
                    ];
                }
            }
        }

        //layout home sắp xếp
        $dataLayouts = [];

        $layoutSaved = LayoutSort::where(
            'store_id',
            $request->store->id
        )->first();

        $layoutShow = $layoutSaved == null ? [] : $layoutSaved->layouts;
        $layouts = AppThemeController::standard($layoutShow);
        foreach ($layouts as $layout) {
            $type_layout = $layout['type_layout'];
            $hide =  $layout["hide"] ?? false;
            $list = null;

            if ($type_layout == HomeLayout::BUTTONS) {
                $list =  $homeButtonShow;
            }

            if ($type_layout == HomeLayout::PRODUCTS_DISCOUNT) {
                $list =   $productDiscounts;
            }

            if ($type_layout == HomeLayout::PRODUCTS_TOP_SALES) {
                $list = $topsales;
            }

            if ($type_layout == HomeLayout::PRODUCTS_NEW) {
                $list =  $productNews;
            }

            if ($type_layout == HomeLayout::POSTS_NEW) {
                $list =  Post::where('store_id', $request->store->id)
                    ->where('published', 1)
                    ->orderBy('created_at', 'DESC')->take(5)->get();
            }

            if ($type_layout == HomeLayout::CATEGORY) {
                $list = Category::where('store_id', $request->store->id)->take(10)->orderBy('position', 'ASC')->get();
            }

            if ($type_layout == HomeLayout::PRODUCT_BY_CATEGORY) {
                $lay = $product_by_cates[$layout['title'] ?? ""] ?? null;
                if ($lay != null) {
                    array_push($dataLayouts,  $product_by_cates[$layout['title'] ?? ""]);
                    unset($product_by_cates[$layout['title']]);
                }
            } else {
                array_push($dataLayouts, [
                    "title" => $layout['title'],
                    "model" => $layout['model'],
                    "type_layout" => $layout['type_layout'],
                    "type_action_more" => $layout['type_action_more'],
                    "hide" => $layout["hide"] ?? true,
                    "list" => $list
                ]);
            }
        }

        if (count($product_by_cates) > 0) {
            foreach ($product_by_cates as $product_by_cate) {
                array_push($dataLayouts,  $product_by_cate);
            }
        }

        $listCatePostShow = CategoryPost::where('store_id', $request->store->id)->where('is_show_home', true)->orderBy('created_at', 'ASC')->get();

        if (count($listCatePostShow) > 0) {
            foreach ($listCatePostShow  as $cateShow) {

                $posts =   Post::where(
                    'posts.store_id',
                    $request->store->id
                )
                    ->where(
                        'published',
                        1
                    )
                    ->when(true, function ($query) use ($cateShow) {
                        $query->whereHas('category_posts', function ($query) use ($cateShow) {
                            $query->whereIn('category_posts.id', [$cateShow->id]);
                        });
                    })->orderBy('created_at', 'desc')->take(10)->get();

                if (count($posts) > 0) {
                    array_push($dataLayouts, [
                        "title" => $cateShow->name,
                        "model" => "Post",
                        "type_layout" => HomeLayout::POST_BY_CATEGORY,
                        "type_action_more" => 'POST',
                        "hide" => $layout["hide"] ?? true,
                        "list" =>   $posts
                    ]);
                }
            }
        }


        $popups = PopupCustomer::where('store_id', $request->store->id)->orderBy('created_at', 'desc')->get();
        //Banner Ads
        $homeData = [
            BANNER => [
                "name" => BANNER,
                "type" => CAROUSEL,
                "list" => CarouselAppImage::where('store_id', $request->store->id)->get(),

            ],
            POPUPS => $popups,
            "layouts" => $dataLayouts,
            "banner_ads" => $from == CustomerHomeController::FROM_WEB ? [
                "type_0" => BannerAd::where('store_id', $request->store->id)->where("type", 0)->get(),
                "type_1" => BannerAd::where('store_id', $request->store->id)->where("type", 1)->get(),
                "type_2" => BannerAd::where('store_id', $request->store->id)->where("type", 2)->get(),
                "type_3" => BannerAd::where('store_id', $request->store->id)->where("type", 3)->get(),
                "type_4" => BannerAd::where('store_id', $request->store->id)->where("type", 4)->get(),
                "type_5" => BannerAd::where('store_id', $request->store->id)->where("type", 5)->get(),
                "type_6" => BannerAd::where('store_id', $request->store->id)->where("type", 6)->get(),
                "type_7" => BannerAd::where('store_id', $request->store->id)->where("type", 7)->get(),
                "type_8" => BannerAd::where('store_id', $request->store->id)->where("type", 8)->get(),
            ] : [],
            "banner_ads_app" => $from == CustomerHomeController::FROM_APP ?  [
                "position_0" => BannerAdApp::where('store_id', $request->store->id)->where("position", 0)->get(),
                "position_1" => BannerAdApp::where('store_id', $request->store->id)->where("position", 1)->get(),
                "position_2" => BannerAdApp::where('store_id', $request->store->id)->where("position", 2)->get(),
                "position_3" => BannerAdApp::where('store_id', $request->store->id)->where("position", 3)->get(),
                "position_4" => BannerAdApp::where('store_id', $request->store->id)->where("position", 4)->get(),
                "position_5" => BannerAdApp::where('store_id', $request->store->id)->where("position", 5)->get(),
                "position_6" => BannerAdApp::where('store_id', $request->store->id)->where("position", 6)->get(),
                "position_7" => BannerAdApp::where('store_id', $request->store->id)->where("position", 7)->get(),
                "position_8" => BannerAdApp::where('store_id', $request->store->id)->where("position", 8)->get(),
            ] : [],
        ];

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $homeData,
        ], 200);
    }



    /**
     * Lấy Danh sách layout
     * 
     * @queryParam from string home từ đâu (FROM_APP,FROM_WEB)
     * 
     */
    public function getHomeAppLayouts(Request $request)
    {

        if ($request->store != null && $request->store->user != null && $request->store->user->is_block == true) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Cửa hàng đã bị khóa, vui lòng liên hệ chăm sóc khách hàng!",
            ], 400);
        }

        $dataLayouts =   Cache::remember(json_encode([CacheUtils::CACHE_KEY_LAYOUT_APP,  $request->store->id]), 60 * 60 * 24, function () use ($request) {
            //layout home sắp xếp
            $dataLayouts = [];
            $layoutSaved = LayoutSort::where(
                'store_id',
                $request->store->id
            )->first();
            $layoutShow = $layoutSaved == null ? [] : $layoutSaved->layouts;
            $layouts = AppThemeController::standard($layoutShow);
            foreach ($layouts as $layout) {
                array_push($dataLayouts, [
                    "title" => $layout['title'],
                    "model" => $layout['model'],
                    "type_layout" => $layout['type_layout'],
                    "type_action_more" => $layout['type_action_more'],
                    "hide" => $layout["hide"] ?? true,
                ]);
            }

            return $dataLayouts;
        });

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $dataLayouts,
        ], 200);
    }

    /**
     * Lấy Danh sách button
     * 
     * @queryParam from string home từ đâu (FROM_APP,FROM_WEB)
     * 
     */
    public function getHomeAppButtons(Request $request)
    {
        $nowTime = Helper::getTimeNowCarbon();

        //Button home
        $homeButtonSaved = HomeButton::where(
            'store_id',
            $request->store->id
        )->first();

        $homeButtonShow = $homeButtonSaved == null ? [] : $homeButtonSaved->buttons;
        if (count($homeButtonShow) == 0) {
            $homeButtonShow = AppThemeController::home_button_default();
        }

        // mini game spin wheel
        $idSpinWheelMoreThan2Gift = DB::table('gift_spin_wheels')
            ->selectRaw('spin_wheel_id,count(*) as count')
            ->groupBy('spin_wheel_id')
            ->havingRaw('count(*) >= 2')
            ->distinct()
            ->pluck('spin_wheel_id');
        $list_mini_game_spin_wheel = SpinWheel::where([
            ['store_id', $request->store->id],
            ['status', StatusSpinWheelDefineCode::COMPLETED],
            ['time_start', '<=', $nowTime->format('Y-m-d H:i:s')],
            // ['time_end', '>=', $nowTime->format('Y-m-d H:i:s')]
        ])
            ->when(count($idSpinWheelMoreThan2Gift) > 0, function ($query) use ($idSpinWheelMoreThan2Gift) {
                $query->whereIn('id', $idSpinWheelMoreThan2Gift);
            })
            ->get();
        if (count($list_mini_game_spin_wheel) > 0) {
            $games = [];
            foreach ($list_mini_game_spin_wheel as $mngame) {
                $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                    $request,
                    $mngame->apply_for,
                    $mngame->agency_id,
                    $mngame->group_customer_id,
                    $request->customer,
                    $request->store->id,
                    $mngame->apply_fors,
                    $mngame->agency_types,
                    $mngame->group_types
                );

                if ($ok_customer) {
                    array_push($games, [
                        "title" => $mngame->name,
                        "type_action" => "SPIN_WHEEL",
                        "value" => $mngame->id,
                        "image_url" => null,
                    ]);
                }
            }
            array_push($homeButtonShow, ...$games);
        }

        // mini game guess number
        $list_mini_game_guess_number = GuessNumber::where([
            ['store_id', $request->store->id],
            ['is_show_game', true],
            ['status', StatusGuessNumberDefineCode::COMPLETED]
        ])
            ->get();

        if (count($list_mini_game_guess_number) > 0) {
            $games = [];
            foreach ($list_mini_game_guess_number as $game_guess_number) {
                $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                    $request,
                    $game_guess_number->apply_for,
                    $game_guess_number->agency_type_id,
                    $game_guess_number->group_customer_id,
                    $request->customer,
                    $request->store->id,
                    $game_guess_number->apply_fors,
                    $game_guess_number->agency_types,
                    $game_guess_number->group_types
                );
                if ($ok_customer) {
                    array_push($games, [
                        "title" => $game_guess_number->name,
                        "type_action" => "GUESS_NUMBER",
                        "value" => $game_guess_number->id,
                        "image_url" => null,
                    ]);
                }
            }
            array_push($homeButtonShow, ...$games);
        }

        $homeButtonShow = array_reverse($homeButtonShow);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $homeButtonShow,
        ], 200);
    }

    /**
     * Lấy Danh sách banner
     * 
     * @queryParam from string home từ đâu (FROM_APP,FROM_WEB)
     * 
     */
    public function getHomeWebBanners(Request $request)
    {
        $up_speed_banner_ios = request('up_speed_banner_ios', $default = null);
        $images =  CarouselAppImage::where('store_id', $request->store->id)->get();
        foreach ($images as $image) {

            if ($up_speed_banner_ios  == UpSpeed::SPEED_BANNER_IOS_APP_CUSTOMER) {
                $image->image_url =  empty($image->image_url) ? null :  Helper::pathReduceImage($image->image_url, 702, 'webp');
            } else {
                $image->image_url =  empty($image->image_url) ? null :  Helper::pathReduceImage($image->image_url, 1918, 'webp');
            }
            // if ($up_speed_banner_ios  == UpSpeed::SPEED_BANNER_IOS_APP_CUSTOMER) {
            //     $image->image_url =  empty($image->image_url) ? null :  strtok($image->image_url, '?') . "?new-width=702&image-type=webp";
            // } else {
            //     $image->image_url =  empty($image->image_url) ? null :  strtok($image->image_url, '?') . "?new-width=1918&image-type=webp";
            // }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $images,
        ], 200);
    }

    /**
     * Lấy Danh sách product discount
     * 
     * @queryParam from string home từ đâu (FROM_APP,FROM_WEB)
     * 
     */
    public function getHomeWebProductDiscount(Request $request)
    {

        $now = Helper::getTimeNowString();

        $request = request();
        $customer = request('customer', $default = null);
        //product discount
        $customerRole = null;
        if($customer != null) {
            
            $customerRole = 5;
            if($customer->is_collaborator) {
                $customerRole = 1;
            }
            if($customer->is_agency) {
                $customerRole = 2;
            }
        } else {
            $customerRole = 6;
        }
       
        
        $product_dis = ProductDiscount::where('product_discounts.store_id', $request->store->id,)
            ->leftJoin('discounts', 'discounts.id', '=', 'product_discounts.discount_id')
            ->where(function ($query) use ($customerRole) {
                $query->where(function ($subquery) {
                    $subquery->leftJoin('discounts', 'discounts.id', '=', 'product_discounts.discount_id')
                        ->whereJsonContains('discounts.group_customers', 0);
                })->orWhere(function ($subquery) use ($customerRole) {
                    $subquery->leftJoin('discounts', 'discounts.id', '=', 'product_discounts.discount_id')
                        ->whereJsonContains('discounts.group_customers', [$customerRole]);
                });
            })
            ->where('discounts.is_end', false)
            ->where('discounts.start_time', '<', $now)
            ->where('discounts.end_time', '>', $now)
            ->orderBy('discounts.created_at', 'desc')
            ->whereRaw('(discounts.amount - discounts.used > 0 OR discounts.set_limit_amount = false)')
            // ->take(10)
            ->get();

        $product_dis_ids_res = [];
        foreach ($product_dis  as  $product_dis_item) {
            $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                $request,
                $product_dis_item->group_customer,
                $product_dis_item->agency_type_id,
                $product_dis_item->group_type_id,
                $customer,
                $request->store->id,
                $product_dis_item->group_customers,
                $product_dis_item->agency_types,
                $product_dis_item->group_types
            );
            if ($ok_customer) {
                array_push($product_dis_ids_res, $product_dis_item->product_id);
            }
        }
        $productDiscounts =  Product::where('store_id', $request->store->id)->where('status', 0)
            ->whereIn('id', $product_dis_ids_res)->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $productDiscounts,
        ], 200);
    }


    /**
     * Lấy Danh sách product discount
     * 
     * @queryParam from string home từ đâu (FROM_APP,FROM_WEB)
     * 
     */
    public function getHomeWebProductTopSales(Request $request)
    {
        
        $topsalesId = ProductTopSale::getTopSaleProductIds($request->store->id);
        $topSell = array();

        $webThemeSaved = WebTheme::where(
            'store_id',
            $request->store->id
        )->first();

        $topsales = json_decode("[]");

        if ($webThemeSaved != null && $webThemeSaved->is_show_product_top_sale == true) {
            $topsales =  Product::where('store_id', $request->store->id)
                ->where(
                    'status',
                    0
                )
                ->where('sold',  '>', 0)
                ->whereIn(
                    'id',
                    $topsalesId
                )->orderBy('sold', 'desc')->get();

            foreach ($topsalesId as $id) {
                foreach ($topsales as $topsale) {
                    if ($topsale->id == $id) {
                        array_push($topSell, $topsale);
                        break;
                    }
                }
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $topsales,
        ], 200);
    }


    /**
     * Lấy Danh sách ProductNews
     * 
     * @queryParam from string home từ đâu (FROM_APP,FROM_WEB)
     * 
     */
    public function getHomeWebProductNews(Request $request)
    {
        $productNews = array();

        $webThemeSaved = WebTheme::where(
            'store_id',
            $request->store->id
        )->first();

        if ($webThemeSaved != null && $webThemeSaved->is_show_product_new == true) {
            $productIdNews = ProductNew::getNewProductIds($request->store->id);
            $productNews =  Product::where(
                'status',
                0
            )->where('store_id', $request->store->id)->take(10)->whereIn('id',   $productIdNews)
                ->orderBy('id', 'desc')
                ->get();
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $productNews,
        ], 200);
    }



    public function getHomeWebProductWithCategory(Request $request)
    {
        //Lấy danh sách tin tức trước khi sort
        $product_by_cates = [];

        $listCateShow = Category::where('store_id', $request->store->id)->where('is_show_home', true)
            ->orderBy('position', 'ASC')->get();
        if (count($listCateShow) > 0) {
            foreach ($listCateShow  as $cateShow) {

                $products =   Product::inRandomOrder()->where(
                    'products.store_id',
                    $request->store->id
                )->where(
                    'products.status',
                    0
                )
                    ->when(true, function ($query) use ($cateShow) {
                        $query->whereHas('categories', function ($query) use ($cateShow) {
                            $query->whereIn('categories.id', [$cateShow->id]);
                        });
                    })->orderBy('created_at', 'desc')->take(10)->get();

                if (count($products) > 0) {
                    array_push(
                        $product_by_cates,
                        [
                            "title" => $cateShow->name,
                            "banner_ads" => $cateShow->banner_ads,
                            "model" => "Product",
                            "type_layout" => HomeLayout::PRODUCT_BY_CATEGORY,
                            'image_url' => $cateShow->image_url,
                            "category_children" => $cateShow->category_children,
                            "type_action_more" => 'PRODUCTS_TOP_SALES',
                            "value_action" => $cateShow->id,
                            "hide" => false,
                            "list" =>   $products
                        ]
                    );
                }
            }
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $product_by_cates,
        ], 200);
    }


    /**
     * Lấy Danh sách post new
     * 
     * @queryParam from string home từ đâu (FROM_APP,FROM_WEB)
     * 
     */
    public function getHomeWebPostNews(Request $request)
    {

        $list =  Post::where('store_id', $request->store->id)
            ->where('published', 1)
            ->orderBy('created_at', 'DESC')->take(5)->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $list,
        ], 200);
    }

    public function getHomeWebPostWithCategory(Request $request)
    {
        $listCatePostShow = CategoryPost::where('store_id', $request->store->id)->where('is_show_home', true)->orderBy('created_at', 'ASC')->get();

        $dataLayouts = [];
        if (count($listCatePostShow) > 0) {
            foreach ($listCatePostShow  as $cateShow) {

                $posts =   Post::where(
                    'posts.store_id',
                    $request->store->id
                )
                    ->where(
                        'published',
                        1
                    )
                    ->when(true, function ($query) use ($cateShow) {
                        $query->whereHas('category_posts', function ($query) use ($cateShow) {
                            $query->whereIn('category_posts.id', [$cateShow->id]);
                        });
                    })->orderBy('created_at', 'desc')->take(10)->get();

                if (count($posts) > 0) {
                    array_push($dataLayouts, [
                        "title" => $cateShow->name,
                        "model" => "Post",
                        "type_layout" => HomeLayout::POST_BY_CATEGORY,
                        "type_action_more" => 'POST',
                        "hide" => $layout["hide"] ?? true,
                        "list" =>   $posts
                    ]);
                }
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $dataLayouts,
        ], 200);
    }


    public function getHomeWebAds(Request $request)
    {
        $listCatePostShow = CategoryPost::where('store_id', $request->store->id)->where('is_show_home', true)->orderBy('created_at', 'ASC')->get();

        $dataLayouts = [];
        if (count($listCatePostShow) > 0) {
            foreach ($listCatePostShow  as $cateShow) {

                $posts =   Post::where(
                    'posts.store_id',
                    $request->store->id
                )
                    ->where(
                        'published',
                        1
                    )
                    ->when(true, function ($query) use ($cateShow) {
                        $query->whereHas('category_posts', function ($query) use ($cateShow) {
                            $query->whereIn('category_posts.id', [$cateShow->id]);
                        });
                    })->orderBy('created_at', 'desc')->take(10)->get();

                if (count($posts) > 0) {
                    array_push($dataLayouts, [
                        "title" => $cateShow->name,
                        "model" => "Post",
                        "type_layout" => HomeLayout::POST_BY_CATEGORY,
                        "type_action_more" => 'POST',
                        "hide" => $layout["hide"] ?? true,
                        "list" =>   $posts
                    ]);
                }
            }
        }

        $popups = PopupCustomer::where('store_id', $request->store->id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                'popups' =>  $popups,
                "type_0" => BannerAd::where('store_id', $request->store->id)->where("type", 0)->get(),
                "type_1" => BannerAd::where('store_id', $request->store->id)->where("type", 1)->get(),
                "type_2" => BannerAd::where('store_id', $request->store->id)->where("type", 2)->get(),
                "type_3" => BannerAd::where('store_id', $request->store->id)->where("type", 3)->get(),
                "type_4" => BannerAd::where('store_id', $request->store->id)->where("type", 4)->get(),
                "type_5" => BannerAd::where('store_id', $request->store->id)->where("type", 5)->get(),
                "type_6" => BannerAd::where('store_id', $request->store->id)->where("type", 6)->get(),
                "type_7" => BannerAd::where('store_id', $request->store->id)->where("type", 7)->get(),
                "type_8" => BannerAd::where('store_id', $request->store->id)->where("type", 8)->get(),
            ],
        ], 200);
    }

    public function getHomeAppAds(Request $request)
    {
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "position_0" => BannerAdApp::where('store_id', $request->store->id)->where("position", 0)->get(),
                "position_1" => BannerAdApp::where('store_id', $request->store->id)->where("position", 1)->get(),
                "position_2" => BannerAdApp::where('store_id', $request->store->id)->where("position", 2)->get(),
                "position_3" => BannerAdApp::where('store_id', $request->store->id)->where("position", 3)->get(),
                "position_4" => BannerAdApp::where('store_id', $request->store->id)->where("position", 4)->get(),
                "position_5" => BannerAdApp::where('store_id', $request->store->id)->where("position", 5)->get(),
                "position_6" => BannerAdApp::where('store_id', $request->store->id)->where("position", 6)->get(),
                "position_7" => BannerAdApp::where('store_id', $request->store->id)->where("position", 7)->get(),
                "position_8" => BannerAdApp::where('store_id', $request->store->id)->where("position", 8)->get(),
            ],
        ], 200);
    }
}
