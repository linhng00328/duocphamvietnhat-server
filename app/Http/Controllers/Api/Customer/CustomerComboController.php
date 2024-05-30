<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\AgencyUtils;
use App\Helper\CollaboratorUtils;
use App\Helper\GroupCustomerUtils;
use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Combo;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group  Customer/Combo
 */
class CustomerComboController extends Controller
{

    /**
     * Lấy danh sách combo đang phát hành
     * @urlParam  store_code required Store code cần lấy.
     */
    public function getAllAvailable(Request $request, $id)
    {
        $now = Helper::getTimeNowString();
        $Combos = Combo::where('store_id', $request->store->id)
            ->where('is_end', false)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->whereRaw('((combos.amount - combos.used > 0) OR combos.set_limit_amount = false)')
            ->get();


        $request = request();
        $customer = request('customer', $default = null);

        $CombosRes = [];
        foreach ($Combos as  $ComboItem) {

            $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                $request,
                $ComboItem->group_customer,
                $ComboItem->agency_type_id,
                $ComboItem->group_type_id,
                $customer,
                $request->store->id,
                $ComboItem->group_customers,
                $ComboItem->agency_types,
                $ComboItem->group_types
            );

            if ($ok_customer) {
                array_push($CombosRes, $ComboItem);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $CombosRes,
        ], 200);
    }
}
