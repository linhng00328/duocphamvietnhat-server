<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DataDemoNewStoreJob;
use App\Models\AppTheme;
use App\Models\AttributeField;
use App\Models\BannerAd;
use App\Models\CarouselAppImage;
use App\Models\Category;
use App\Models\CategoryPost;
use App\Models\ConfigDataExample;
use App\Models\MsgCode;
use App\Models\Post;
use App\Models\PostCategoryPost;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\Store;
use App\Models\WebTheme;
use Illuminate\Http\Request;

/**
 * @group  Admin/Cấu hình data ví dụ
 */

class ExampleDataShopController extends Controller
{
    /**
     * Cấu hình data ví dụ
     * 
     * 
     * @bodyParam data_setup List data dạng [  {"type_id": 11, "store_code": "sy"},  ]
     * 
     */
    public function setupShopData(Request $request)
    {
        $config_request = [];

        if ($request->data_setup != null && is_array($request->data_setup)) {
            $config_request = $request->data_setup;
        } else {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::DATA_IS_REQUIRED[0],
                'msg' => MsgCode::DATA_IS_REQUIRED[1],
            ], 400);
        }

        $types = config('saha.type_store.type_store');
        $carrers = $types[0]['childs'];

        $arr_id_carrer = [];
        foreach ($carrers   as $item_carrer) {;
            array_push($arr_id_carrer, $item_carrer['id']);
        }

        $arr_id_request = [];
        foreach ($config_request  as $item_config) {

            if (isset($item_config['type_id']) && in_array($item_config['type_id'], $arr_id_carrer)) {
                array_push($arr_id_request, $item_config['type_id']);
            } else {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::DATA_IS_REQUIRED[0],
                    'msg' => MsgCode::DATA_IS_REQUIRED[1],
                ], 400);
            }
        }

        if (count($carrers) != count($arr_id_request)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::DATA_IS_REQUIRED[0],
                'msg' => MsgCode::DATA_IS_REQUIRED[1],
            ], 400);
        }

        foreach ($config_request  as $item_config) {

            if (isset($item_config['store_code'])) {

                $store = Store::where('store_code', $item_config['store_code'])->first();

                if ($store == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                        'msg' => ($item_config['store_code']) . " không tồn tại",
                    ], 400);
                }
            } else {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::DATA_IS_REQUIRED[0],
                    'msg' => MsgCode::DATA_IS_REQUIRED[1],
                ], 400);
            }
        }

        ConfigDataExample::where('id', '!=', null)->delete();


        foreach ($config_request  as $item_config) {

            if (isset($item_config['store_code'])) {

                $store = Store::where('store_code', $item_config['store_code'])->first();

                if ($store == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                        'msg' => ($item_config['store_code']) . " không tồn tại",
                    ], 400);
                }
            } else {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::DATA_IS_REQUIRED[0],
                    'msg' => MsgCode::DATA_IS_REQUIRED[1],
                ], 400);
            }

            ConfigDataExample::create([
                "type_id" => $item_config['type_id'],
                "store_code" => $item_config['store_code'] ?? "",
                "note" => $item_config['note'] ?? "",
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $carrers
        ], 200);
    }

    /**
     * Lấy thông tin cấu hình
     */
    public function getSetupShopData(Request $request)
    {

        $data_config = ConfigDataExample::get();

        $type_with_type_id_arr = [];


        foreach ($data_config as $item_config) {
            $type_with_type_id_arr[$item_config->type_id] = $item_config->store_code;
            $note_with_type_id_arr[$item_config->type_id] = $item_config->note;
        }


        $types = config('saha.type_store.type_store');
        $carrers = $types[0]['childs'];

        $config_example = [];


        foreach ($carrers   as $item_carrer) {;
            array_push($config_example, [
                "type_id" => $item_carrer["id"],
                "store_code" => $type_with_type_id_arr[$item_carrer["id"]],
                "name" => $item_carrer["name"] ?? "",
                "note" => $note_with_type_id_arr[$item_carrer["id"]] ?? "",
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $config_example
        ], 200);
    }


    // Test tạo tự động
    public function test_init_store()
    {
        // DataDemoNewStoreJob::dispatch(
        //     1132,
        //     11,
        // );
    }


  
}
