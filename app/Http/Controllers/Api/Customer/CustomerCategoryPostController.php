<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryPost;
use App\Models\MsgCode;
use Illuminate\Http\Request;

    /**
    * @group  Customer/Danh mục bài viết
    */
class CustomerCategoryPostController extends Controller
{

    /**
	* Danh sách danh mục bài viết
    * @urlParam  store_code required Store code cần lấy.
	*/
    public function getAll(Request $request)
    {

        $categories = CategoryPost::where('store_id', $request->store->id)->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categories,
        ], 200);
    }
}
