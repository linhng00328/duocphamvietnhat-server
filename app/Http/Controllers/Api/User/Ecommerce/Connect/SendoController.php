<?php

namespace App\Http\Controllers\Api\User\Ecommerce\Connect;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\EcommercePlatform;
use App\Models\MsgCode;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * @group  User/Kết nối sàn
 */
class SendoController extends Controller
{

    /**
     * Kết nối sàn sendo
     * 
     * @bodyParam shop_name Tên gian hàng
     * @bodyParam token Mã bảo mật
     * @bodyParam shop_id Shop id
     * 
     */

    public function connect_sendo(Request $request)
    {

      
    }
}
