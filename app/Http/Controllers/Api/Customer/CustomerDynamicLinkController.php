<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\IPUtils;
use App\Helper\Place;
use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\DynamicLink;
use App\Models\MsgCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group  Customer/Giới thiệu tải qua app
 */
class CustomerDynamicLinkController extends Controller
{

    /**
     * Lấy thông tin khi giới thiệu link
     * 
     * 
     */
    public function getDynamicLink(Request $request, $id)
    {
        $ip = IPUtils::getIP();

        $address = DynamicLink::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)
            ->where('ip', $ip)
            ->orderBy('created_at', 'desc')->first();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $address,
        ], 200);
    }

    /**
     * Xu ly thông tin khi giới thiệu link
     * 
     * 
     */
    public function handle(Request $request)
    {

        DynamicLink::where('store_id', $request->store->id)->where('id', $request->dynamic_link_id)->update([
            'handled' => true
        ]);
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Xu ly thông tin khi giới thiệu link
     * 
     * 
     */
    public function redirectAndSaveIp(Request $request)
    {

        $action = request('action');
        $phone = request('phone');
        $references_id = request('references_id');
        $cid = request('cid');
        $store_id = request('store_id');
        $link = base64_decode(request('link')) ?? "";

        if (!empty($action) || !empty($phone) || !empty($cid) || !empty($references_id)) {

            $collaborator_by_customer_id = null;
            $phone_number =  $phone;
            if (!empty($phone)) {
                $customer = DB::table('customers')->where('phone_number', $phone)->where('store_id', $store_id)->first();

                if ($customer != null) {
                    $collaborator_by_customer_id = $customer->id;
                }
            } else if (!empty($cid)) {
                $customer = DB::table('customers')
                    ->where('store_id', $store_id)
                    ->where('id', $cid)->first();

                if ($customer != null) {
                    $phone_number = $customer->phone_number;
                    $collaborator_by_customer_id = $customer->id;
                }
            }


            DB::table('dynamic_links')->insert([
                "store_id" => $store_id,
                'ip' => $request->ip(),
                'action'  => $action,
                "phone"  => $phone_number,
                'collaborator_by_customer_id' =>  $collaborator_by_customer_id,
                "references_id"  => $references_id,
                "created_at" =>  Carbon::now()
            ]);
        }
        $urlRedirect = "Location: $link";
        header($urlRedirect);
        die();
    }

    /**
     * Xu ly thông tin khi giới thiệu link
     * 
     * 
     */
    public function redirectToLink(Request $request)
    {


        $link = request('link') ?? "";
        $appDeepLink = "https://$link";

        $urlRedirect = "Location: $appDeepLink";
        header($urlRedirect);
        die();
    }
}
