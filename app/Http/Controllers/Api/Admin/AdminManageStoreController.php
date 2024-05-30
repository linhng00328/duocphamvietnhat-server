<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Store;
use App\Models\WebTheme;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group  Admin/Quản lý Store
 *
 * APIs Quản lý Store
 */
class AdminManageStoreController extends Controller
{
    /**
     * Danh sách store
     * /stores?page=1&search=name&sort_by=id&descending=false&store_ids=1,2,3

     * @queryParam  page Lấy danh sách ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: samsung
     * @queryParam  sort_by Sắp xếp theo VD: price
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  type_compare_date_expried kiểu so sánh(>,<,=, default: <)
     * @queryParam  date_expried Giá trị so sánh với time hết hạn
     * @queryParam  begin_date_expried Ngay het han bat dau tu
     * 
     */

    public function getAll(Request $request)
    {

        $stores = Store::sortByRelevance(true)
            ->when(Store::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when(request('sort_by') == null, function ($query) {
                $query->orderBy('stores.created_at', 'desc');
            })
            ->when(request('date_expried') != null && request('type_compare_date_expried')  != null, function ($query) {
                $query->where('stores.date_expried', request('type_compare_date_expried') ?? '<', request('date_expried'));
            })
            ->when(request('begin_date_expried') != null, function ($query) {
                $t2 =  Helper::get_begin_date_string(new Carbon(request('begin_date_expried')));
                $query->where('stores.date_expried', '>=', $t2);
            })
            ->when(request('end_date_expried') != null, function ($query) {
                $t1 =  Helper::get_end_date_string(new Carbon(request('end_date_expried')));
                $query->where('stores.date_expried', '<=', $t1);
            })

            ->when(request('begin_date_register') != null, function ($query) {
                $t2 =  Helper::get_begin_date_string(new Carbon(request('begin_date_register')));
                $query->where('stores.created_at', '>=', $t2);
            })
            ->when(request('end_date_register') != null, function ($query) {
                $t1 =  Helper::get_end_date_string(new Carbon(request('end_date_register')));
                $query->where('stores.created_at', '<=', $t1);
            })

            ->search(StringUtils::convert_name(request('search')))
            ->paginate(request('limit') == null ? 20 : request('limit'));


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $stores,
        ], 200);
    }

    /**
     * Thông tin một Store
     * @urlParam  id required ID store cần lấy thông tin.
     */
    public function getOneStore(Request $request, $id)
    {
        $id = $request->route()->parameter('store_id');
        $storeExists = Store::where(
            'id',
            $id
        )->first();

        if (empty($storeExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                'msg' => MsgCode::NO_STORE_EXISTS[1],
            ], 404);
        } else {

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' =>  $storeExists,
            ], 200);
        }
    }

    /**
     * Thông tin một Store
     * @urlParam  id required ID store cần lấy thông tin.
     */
    public function getOneStoreByCode(Request $request, $id)
    {
        $store_code = $request->route()->parameter('store_code');
        $storeExists = Store::where(
            'store_code',
            $store_code
        )->first();

        if (empty($storeExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                'msg' => MsgCode::NO_STORE_EXISTS[1],
            ], 404);
        } else {

            $webThemeExists = WebTheme::where(
                'store_id',
                $storeExists->id
            )->select('domain')->first();

            $domain = ($webThemeExists->domain ?? "");
            $domain  = str_replace("http://", "", $domain);
            $domain  = str_replace("https://", "", $domain);
            $domain  = "https://" . $domain;

            $storeExists->domain_customer =  $webThemeExists == null || empty($webThemeExists->domain) ? null : $webThemeExists->domain;

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' =>  $storeExists,
            ], 200);
        }
    }


    /**
     * update một Store
     * Gửi một trong các trường sau các trường null sẽ ko nhận và lấy giá trị cũ
     * 
     * @urlParam  store_id required Store id cần update
     * @urlParam  name required Name Store
     * @bodyParam store_code string required store_code
     * @bodyParam date_expried required Ngày hết hạn "2012-1-1"
     * @bodyParam address string required Địa chỉ
     * @bodyParam logo_url string required Logo url
     * @bodyParam has_upload_store required Đã up lên store hay chưa
     * @bodyParam link_google_play required Link tải google play
     * @bodyParam link_apple_store required Link tải apple store
     * @bodyParam store_code_fake_for_ios required store_code_fake_for_ios Chọn store để fake data
     * 
     * 
 
     */
    public function updateOneStore(Request $request)
    {


        $id = $request->route()->parameter('store_id');
        $checkStoreExists = Store::where(
            'id',
            $id
        )->first();

        $store_code = null;

        if ($request->store_code != null) {
            $store_code  = $request->store_code;

            if (!preg_match('/^[a-zA-Z]+[a-zA-Z0-9_]+$/',  $request->store_code)  || strlen($request->store_code) < 2) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_CODE_STORE[0],
                    'msg' => MsgCode::INVALID_CODE_STORE[1],
                ], 400);
            }

            $listCant = [
                "admin", "user", "account", "partner", "api", "quanly", "ad", "store", "data", "app", "login", "register",
                "ship", "call", "doapp", "do", "my", "contact", "web", "manage"
            ];

            if (in_array($request->store_code, $listCant)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::CAN_NOT_USE[0],
                    'msg' => MsgCode::CAN_NOT_USE[1],
                ], 400);
            }

            $checkStoreCodeExists = Store::where(
                'store_code',
                $request->store_code
            )->where('id', '!=', $id)->first();

            if ($checkStoreCodeExists  != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::CODE_ALREADY_EXISTS[0],
                    'msg' => MsgCode::CODE_ALREADY_EXISTS[1],
                ], 400);
            }
        }
        if (empty($checkStoreExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                'msg' => MsgCode::NO_STORE_EXISTS[1],
            ], 404);
        } else {
            $checkStoreExists->update(Helper::sahaRemoveItemArrayIfNullValue([
                'name' => $request->name,
                'address' => $request->address,
                'logo_url' => $request->logo_url,
                'has_upload_store' => $request->has_upload_store === null ? null : filter_var($request->has_upload_store, FILTER_VALIDATE_BOOLEAN),
                'is_block_app' => $request->is_block_app === null ? false : filter_var($request->is_block_app, FILTER_VALIDATE_BOOLEAN),
                'link_google_play' => $request->link_google_play,
                'link_apple_store' => $request->link_apple_store,
                'store_code_fake_for_ios' => $request->store_code_fake_for_ios,
                'date_expried' =>  $request->date_expried,
                'store_code' => $store_code  ?? $checkStoreExists->store_code
            ]));


            $checkStoreExists->update([
                'store_code_fake_for_ios' => $request->store_code_fake_for_ios,
            ]);

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => Store::where('id', $id)->first(),
            ], 200);
        }
    }


    /**
     * xóa một Store
     * @urlParam  store_id required Store id cần delete
     */
    public function deleteOneStore(Request $request)
    {
        $id = $request->route()->parameter('store_id');
        $checkStoreExists = Store::where(
            'id',
            $id
        )->first();

        if ($checkStoreExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_CODE_EXISTS[0],
                'msg' => MsgCode::NO_STORE_CODE_EXISTS[1],
            ], 400);
        }


        $now = Helper::getTimeNowString();
        $time1 = Carbon::parse($checkStoreExists->date_expried);
        $time1 = $time1->addDays(90);
        $time2 = Carbon::parse($now);


        if ($checkStoreExists->has_upload_store == true || $checkStoreExists->link_google_play || $checkStoreExists->link_apple_store) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Shop đã up lên cửa hàng không thể xóa",
            ], 400);
        }

        if ($time1 > $time2) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Phải quá hạn 90 ngày",
            ], 400);
        }

        $checkStoreExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
