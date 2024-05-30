<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\AppTheme;
use App\Models\MsgCode;
use App\Models\WebTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * @group  Customer/AppWebTheme
 */
class CustomerAppWebThemeController extends Controller
{
    /**
     * Theme App
     * @urlParam  store_code required Store code cần lấy.
     */
    public function getAppTheme(Request $request)
    {

        $columns = Schema::getColumnListing('app_themes');


        $appThemeExists = AppTheme::where(
            'store_id',
            $request->store->id
        )->first();



        $appThemeResponse = new AppTheme();

        foreach ($columns as $column) {

            if ($appThemeExists != null && array_key_exists($column, $appThemeExists->toArray())) {
                $appThemeResponse->$column =  $appThemeExists->$column;
            } else {
                $appThemeResponse->$column = null;
            }
        }
        unset($appThemeResponse['store_id']);
        unset($appThemeResponse['user_id']);
        unset($appThemeResponse['id']);
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $appThemeResponse,
        ], 200);
    }

    /**
     * Theme Web
     * @urlParam  store_code required Store code cần lấy.
     */
    public function getWebTheme(Request $request)
    {

        $columns = Schema::getColumnListing('web_themes');


        $appThemeExists = WebTheme::where(
            'store_id',
            $request->store->id
        )->first();



        $appThemeResponse = new WebTheme();

        foreach ($columns as $column) {

            if ($appThemeExists != null && array_key_exists($column, $appThemeExists->toArray())) {
                $appThemeResponse->$column =  $appThemeExists->$column;
            } else {
                $appThemeResponse->$column = null;
            }
        }
        unset($appThemeResponse['store_id']);
        unset($appThemeResponse['id']);
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $appThemeResponse,
        ], 200);
    }
}
