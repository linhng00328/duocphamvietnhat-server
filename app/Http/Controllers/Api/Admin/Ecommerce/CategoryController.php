<?php

namespace App\Http\Controllers\Api\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\SanCategory;
use App\Models\MsgCode;
use Illuminate\Http\Request;


/**
 * @group  Admin/Danh mục sàn tmdt
 */
class CategoryController extends Controller
{

    /**
     * Thêm danh mục
     * 
     * @bodyParam  name string tên 
     * @bodyParam  image_url string link ảnh
     * @bodyParam  category_index int (0 danh mục chính , 1 danh mục phụ, 2 danh mục con)
     * @bodyParam  parent_id int trường hợp danh mục phụ hoặc danh mục con cần truyền lên parent_id
     * 
     */
    public function create(Request $request)
    {
        if (empty($request->name)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        if ($request->category_index == 1 || $request->category_index == 2) {
            $pa = SanCategory::where('parent_id', $request->parent_id)
                ->where('category_index', $request->category_index - 1)
                ->first();

            if ($pa == null) {
                if ($request->category_index == 1 || $request->category_index == 2) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_PARENT_CATEGORY_ECOMMERCE[0],
                        'msg' => MsgCode::NO_PARENT_CATEGORY_ECOMMERCE[1],
                    ], 400);
                }
            }
        }

        $cateNameExist = SanCategory::where('category_index', $request->category_index)
            ->where('name', $request->name)->first();
        if ($cateNameExist != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }
        $created = SanCategory::create([
            'name' => $request->name,
            'image_url' => $request->image_url,
            'category_index' => $request->category_index,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $created
        ], 200);
    }

    public function getAll(Request $request)
    {

        $all = SanCategory::get();

        //tách danh sách

        $cateByIndex2 = [];
        $cateByIndex1 = [];
        $cateByIndex0 = [];

        foreach ($all as $cate) {
            if ($cate->category_index == 1) {
                if (!isset($cateByIndex1[$cate->parent_id])) {
                    $cateByIndex1[$cate->parent_id] = [$cate];
                } else {
                    array_push($cateByIndex1[$cate->parent_id], $cate);
                }
            }

            if ($cate->category_index == 2) {
                if (!isset($cateByIndex2[$cate->parent_id])) {
                    $cateByIndex2[$cate->parent_id] = [$cate];
                } else {
                    array_push($cateByIndex2[$cate->parent_id], $cate);
                }
            }
        }

       
      
        foreach ($all as $cate) {
            if ($cate->category_index == 0) {

                $cate->children = $cateByIndex1[$cate->id] ?? [];
         

                array_push($cateByIndex0, $cate);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $cateByIndex0
        ], 200);
    }


    /**
     * Xóa 1 danh mục
     * 
     * @urlParam  category_id id danh mục 
     * 
     */
    public function delete(Request $request)
    {

        $category_id = (int)$request->route()->parameter('category_id');

        $categoryExists = SanCategory::where('category_id',  $category_id)->first();

        if ($categoryExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
            ], 400);
        }

        $categoryExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Sửa 1 danh mục
     * 
     * @urlParam  category_id id danh mục 
     * 
     */
    public function update(Request $request)
    {

        $category_id = (int)$request->route()->parameter('category_id');

        $categoryExists = SanCategory::where('category_id',  $category_id)->first();

        if ($categoryExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
            ], 400);
        }

        if (empty($request->name)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $cateNameExist = SanCategory::where('category_index', $request->category_index)
            ->where('name', $request->name)->first();
        if ($cateNameExist != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }

        $categoryExists->update([
            'name' => $request->name,
            'image_url' => $request->image_url,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
