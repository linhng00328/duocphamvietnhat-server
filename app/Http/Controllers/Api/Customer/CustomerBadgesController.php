<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\Helper;
use App\Helper\IPUtils;
use App\Http\Controllers\Api\User\GeneralSettingController;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AgencyRegisterRequest;
use App\Models\CcartItem;
use App\Models\Collaborator;
use App\Models\CollaboratorRegisterRequest;
use App\Models\CollaboratorsConfig;
use App\Models\ConfigUserVip;
use App\Models\Discount;
use App\Models\DynamicLink;
use App\Models\Favorite;
use App\Models\MsgCode;
use App\Models\NotificationCustomer;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OtpConfig;
use App\Models\OtpUnit;
use App\Models\PointSetting;
use App\Models\Post;
use App\Models\Product;
use App\Models\RoomChat;
use App\Models\SpinWheel;
use App\Models\UnreadPost;
use App\Models\Voucher;
use App\Models\WebTheme;

/**
 * @group  Customer/Chỉ số đếm
 */
class CustomerBadgesController extends Controller
{

    static function dataBadges($request)
    {
        $device_id = $request->device_id;
        //total orders
        $orders_waitting_for_progressing = 0;
        $orders_packing = 0;
        $orders_shipping = 0;
        $orders_no_reviews = 0;
        $cart_quantity = 0;
        $total_bought_amount = 0;
        $chats_unread = 0;
        $notification_unread = 0;
        $totalCart = 0;
        $allow_use_point_order = false;
        $unread_post_num = 0;
        $favorite_products = 0;
        $customer_point = 0;
        $voucher_total = 0;
        $products_discount = 0;
        $status_collaborator = -1;
        $status_agency = 0;


        //total voucher
        $now = Helper::getTimeNowString();
        $voucher_total = Voucher::where('store_id', $request->store->id,)
            ->where('is_end', false)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->whereRaw('vouchers.is_show_voucher = true AND (vouchers.amount - vouchers.used > 0 OR vouchers.set_limit_amount = false)')
            ->count();

        //$products_discount 
        $products_discount = Discount::where('store_id', $request->store->id,)
            ->where('is_end', false)
            ->where('start_time', '<', $now)
            ->where('end_time', '>', $now)
            ->orderBy('created_at', 'desc')
            ->whereRaw('(discounts.amount - discounts.used > 0 OR discounts.set_limit_amount = false)')
            ->count();

        //Cho phep dung point ko

        $pointSetting = PointSetting::where(
            'store_id',
            $request->store->id
        )->first();

        if ($pointSetting != null) {
            $allow_use_point_order =   $pointSetting->allow_use_point_order;
        }

        //     
        $configExists = CollaboratorsConfig::where(
            'store_id',
            $request->store->id
        )->first();


        if ($request->customer != null) {

            $orders = Order::where(
                'store_id',
                $request->store->id
            )->where('customer_id', $request->customer->id)
                ->get();
            $customer_point = $request->customer->points;

            foreach ($orders as $order) {
                if ($order->order_status == 0) {
                    $orders_waitting_for_progressing++;
                }
                if ($order->order_status == 1) {
                    $orders_packing++;
                }
                if ($order->order_status == 5) {
                    $orders_shipping++;
                }


                if ($order->order_status == 10 && $order->payment_status == 2) {
                    $total_bought_amount = $total_bought_amount + ($order->total_final ?? 0);

                    //tính products chưa review
                    $has_review = false;
                    foreach ($order->line_items as $line_item) {
                        if ($line_item->reviewed == true) {
                            $has_review = true;
                        }
                    }
                    if ($has_review == false) {
                        $orders_no_reviews++;
                    }
                }
            }

            //total chat
            $lastRooms = RoomChat::where('customer_id', $request->customer->id)
                ->where('store_id', $request->store->id)
                ->get();

            foreach ($lastRooms as $lastRoom) {
                if ($lastRoom->customer_unread) {
                    $chats_unread += $lastRoom->customer_unread;
                }
            }

            //notification_unread
            $notification_unread = $request->customer->notifications_count;

            // $notification_unread = NotificationCustomer::where('store_id', $request->store->id)
            //     ->where(
            //         'customer_id',
            //         $request->customer->id
            //     )
            //     ->where('unread', true)->count();


            //post unread
            $postMax = Post::where('store_id', $request->store->id,)->orderBy('id', 'desc')->first();
            $unread_post_num = 0;
            $unreadpost = UnreadPost::where('store_id', $request->store->id,)
                ->where('customer_id', $request->customer->id)->first();
            if ($postMax == null) {
                $unread_post_num = 0;
            } else {

                if ($unreadpost == null || $unreadpost->id_read_max != $postMax->id) {
                    $unread_post_num = 1;
                }
            }


            //products favorite

            $fivoriteIds = Favorite::where(
                'store_id',
                $request->store->id
            )->where(
                'customer_id',
                $request->customer->id
            )->orderBy('updated_at', 'desc')->get()->pluck("product_id");

            $favorite_products = Product::whereIn(
                'id',
                $fivoriteIds
            )->where(
                'status',
                0
            )->count();

            //total cart

            $device_id = request()->header('device_id');

            $allCart = null;

            $user_id =  $request->user != null ? $request->user->id : null;
            $staff_id = $request->user == null  && $request->staff != null ? $request->staff->id : null;
            $customer_id = $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;



            $cart_quantity = CcartItem::allItem(null,  $request)
                ->count();
        } else {

            $device_id = request()->header('device_id');
            if (!empty($device_id)) {
                //total cart
                $cart_quantity = CcartItem::allItem(null,  $request)
                    ->count();
            }
        }

        $collaborator_register_request = [];
        $collaborator_register_request['has_request'] = false;
        //check status collaborator
        if ($request->customer != null) {
            $collaborator = Collaborator::where('store_id', $request->store->id)
                ->where('customer_id', $request->customer->id)->first();
            if ($collaborator  != null && $request->customer->is_collaborator == true) {
                $status_collaborator = $collaborator->status;
            }
        }

        if ($request->customer != null && $request->customer->is_collaborator == false) {
            $collaboratorRegisterRequest = CollaboratorRegisterRequest::where('store_id', $request->store->id)
                ->orderBy('id', 'desc')
                ->where('customer_id', $request->customer->id)->first();
            if ($collaboratorRegisterRequest  != null) {
                $collaborator_register_request['has_request'] = true;
                $collaborator_register_request['status'] =   $collaboratorRegisterRequest->status;
                $collaborator_register_request['note'] =   $collaboratorRegisterRequest->note;
            }
        }

        $agency_register_request = [];
        $agency_register_request['has_request'] = false;
        //check status agency
        if ($request->customer != null) {
            $agency = Agency::where('store_id', $request->store->id)
                ->where('customer_id', $request->customer->id)->first();
            if ($agency  != null && $request->customer->is_agency == true) {
                $status_agency = $agency->status ?? 0;
            }
        }

        if ($request->customer != null && $request->customer->is_agency == false) {
            $agencyRegisterRequest = AgencyRegisterRequest::where('store_id', $request->store->id)
                ->orderBy('id', 'desc')
                ->where('customer_id', $request->customer->id)->first();
            if ($agencyRegisterRequest  != null) {
                $agency_register_request['has_request'] = true;
                $agency_register_request['status'] =   $agencyRegisterRequest->status;
                $agency_register_request['note'] =   $agencyRegisterRequest->note;
            }
        }


        //User vip
        $config_user_vip = null;
        if ($request->store->user->is_vip == true) {
            $config_user_vip = ConfigUserVip::select('customer_copyright', 'url_customer_copyright')->where('user_id', $request->store->user->id)->first();
        }

        //Domain
        $webThemeExists = WebTheme::where(
            'store_id',
            $request->store->id
        )->first();

        //Config OTP
        $is_use_otp = true;
        $otp_configs = OtpConfig::where('store_id', $request->store->id)
            ->first();
        $otp_unit_used = OtpUnit::where('store_id', $request->store->id)
            ->where('is_use', true)->first();
        if (!empty($otp_configs)) {

            if ($otp_configs->is_use) {

                $is_use_otp = $otp_configs->is_use_from_default == true || ($otp_configs->is_use_from_units == true && $otp_unit_used != null)  ? true : false;
            } else {

                $is_use_otp = false;
            }
        }

        $data = [
            "has_train" => in_array("train", $request->store->user->functions),
            "has_community" => in_array("community", $request->store->user->functions),
            "store_name" => $request->store->name,
            "version_ios" => $request->store->version_ios,
            "version_android" => $request->store->version_android,
            "orders_waitting_for_progressing" => $orders_waitting_for_progressing,
            "orders_packing" => $orders_packing,
            "orders_shipping" => $orders_shipping,
            "orders_no_reviews" => $orders_no_reviews,
            "chats_unread" =>  $chats_unread,
            "posts_unread" =>  $unread_post_num,
            "cart_quantity" => $cart_quantity,
            "favorite_products" => $favorite_products,
            "has_store_config_collaborator" =>  $configExists != null,
            "customer_point" => $customer_point,
            "voucher_total" => $voucher_total,
            "products_discount" => $products_discount,
            "total_bought_amount" => $total_bought_amount,
            "allow_use_point_order" => $allow_use_point_order,
            "notification_unread" =>  $notification_unread,
            'status_collaborator' => $status_collaborator,
            'collaborator_register_request' => ($collaborator_register_request['has_request']  == false || ($request->customer != null && $request->customer->is_collaborator == false &&   2 == ($collaborator_register_request['status'] ?? -1)))  ? null : $collaborator_register_request,
            'status_agency' => $status_agency,
            'agency_register_request' => ($agency_register_request['has_request']  == false || ($request->customer != null && $request->customer->is_agency == false &&   2 == ($agency_register_request['status'] ?? -1)))  ? null : $agency_register_request,
            'config_user_vip' => $config_user_vip,
            'dynamic_links' => [
                'product_ref' => DynamicLink::where('ip', $request->ip())->where('action', 'product')->where('store_id', $request->store->id)->select('id', 'action', 'phone', 'references_id', 'handled')->orderBy('id', 'desc')->first(),
                'phone_ref' => DynamicLink::where('ip', $request->ip())->where('store_id', $request->store->id)->select('id', 'collaborator_by_customer_id', 'action', 'phone', 'references_id', 'handled')->orderBy('id', 'desc')->first(),
                'post_ref' => DynamicLink::where('ip', $request->ip())->where('action', 'post')->where('store_id', $request->store->id)->select('id', 'action', 'phone', 'references_id', 'handled')->orderBy('id', 'desc')->first(),
            ],
            'domain' => ($webThemeExists == null || $webThemeExists->domain == null) ? ($request->store->store_code . ".ikitech.vn") : ($webThemeExists->domain),
            'link_google_play' =>  $request->store->link_google_play,
            'link_apple_store' =>  $request->store->link_apple_store,
            "allow_semi_negative" =>   GeneralSettingController::defaultOfStore($request)['allow_semi_negative'],
            "allow_branch_payment_order" =>   GeneralSettingController::defaultOfStore($request)['allow_branch_payment_order'],
            "auto_choose_default_branch_payment_order" =>   GeneralSettingController::defaultOfStore($request)['auto_choose_default_branch_payment_order'],
            "required_agency_ctv_has_referral_code" =>   GeneralSettingController::defaultOfStore($request)['required_agency_ctv_has_referral_code'],
            "is_default_terms_agency_collaborator" =>   GeneralSettingController::defaultOfStore($request)['is_default_terms_agency_collaborator'],
            "terms_agency" =>   GeneralSettingController::defaultOfStore($request)['terms_agency'],
            "terms_collaborator" =>   GeneralSettingController::defaultOfStore($request)['terms_collaborator'],
            'is_use_otp' => $is_use_otp
        ];

        return $data;
    }

    /**
     * Lấy tất cả chỉ số đếm
     */
    public function getBadges(Request $request, $id)
    {


        $data = CustomerBadgesController::dataBadges($request);


        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $data,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
