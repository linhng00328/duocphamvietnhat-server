<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\Distribute;
use App\Models\InventoryEleDis;
use App\Models\InventoryHistory;
use App\Models\InventorySubDis;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReferralPhoneCustomer;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


/**
 * @group  User/Báo cáo tồn kho tất cả chi nhánh
 */
class ReportAllBranchController extends Controller
{
    /**
     * Báo cáo tồn kho chi nhánh
     * @bodyParam phone_number string required Số điện thoại
     * @bodyParam email string required Email
     * @bodyParam password string required Password
     * @bodyParam otp string gửi tin nhắn (DV SAHA gửi tới 8085)
     * @bodyParam otp_from string  phone(từ sdt)  email(từ email) mặc định là phone
     */
    
}
