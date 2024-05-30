<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\StringUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Models\CategoryPost;
use App\Models\MsgCode;
use App\Models\NotificationCustomer;
use App\Models\Order;
use App\Models\Post;
use App\Models\PostCategoryPost;
use App\Services\UploadImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group  In/in hóa đơn
 */
class PrintController extends Controller
{
    /**
     * In hóa đơn
     */
    public function print_bill(Request $request)
    {

        $orderExists = Order::where('order_code', $request->order_code)
            ->with('line_items')
            ->first();

            if( $orderExists  == null) {
                return;
            }

        return response()->view(
            'print_bill',
            [
                'order' => $orderExists
            ]
        );
    }
}
