<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  Customer/Danh mục sản phẩm
 */
class CustomerCategoryController extends Controller
{

    /**
     * Danh sách danh mục sản phẩm
     * @urlParam  store_code required Store code cần lấy.
     */
    public function getAll(Request $request)
    {

        $categories = Category::where('store_id', $request->store->id)->orderBy('position', 'ASC')->get();
        foreach ($categories  as $cate) {
            $cate->image_url = empty($cate->image_url) ? null : Helper::pathReduceImage($cate->image_url, 450, 'webp');
            // $cate->image_url = empty($cate->image_url) ? null : strtok($cate->image_url, '?') . "?new-width=450&image-type=webp";
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categories,
        ], 200);
    }
}
