<?php

namespace App\Http\Controllers\Api\User\Ecommerce\Zalo;

use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\SanCategory;
use App\Models\SanProduct;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Zalo\Zalo;

/**
 * @group  User/Sản phẩm
 */
class ZaloController extends Controller
{

    /**
     * Danh sách sản phẩm
     * 
     * @queryParam on_sale boolean phải onsale ko
     * 
     */
    public function zalo(Request $request)
    {

        $config = array(
            'app_id' => '4442897439069436538',
            'app_secret' => 'XHWPpI7ZVdLisYfubRUo'
        );
        $zalo = new Zalo($config);

        $helper = $zalo->getRedirectLoginHelper();
        $callbackUrl = "https://www.callbackack.com";
        $codeChallenge = "gdfgdfgd";
        $state = "your state";
        $loginUrl = $helper->getLoginUrl($callbackUrl, $codeChallenge, $state); // This is login url

        dd($loginUrl );
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
