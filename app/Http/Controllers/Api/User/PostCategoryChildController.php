<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryPost;
use App\Models\CategoryPostChild;
use App\Models\MsgCode;
use App\Services\UploadImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


/**
 * @group User/Danh mục tin tức con
 * 
 * APIs Danh mục tin tức
 */
class PostCategoryChildController extends Controller
{
    /**
     * Tạo danh mục  tin tức con
     * @urlParam  store_code required Store code
     * @urlParam  category_id required category_id
     * @bodyParam name string required Tên danh mục con
     * @bodyParam image file required Ảnh (hoặc truyền lên image_url)
     */
    public function create(Request $request)
    {
        $category_id = $request->route()->parameter('category_id');

        $imageUrl = null;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath(), $request->image_type, $request->image->getClientMimeType());
        }

        $checkCategoryExists = CategoryPost::where(
            'id',
            $category_id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkCategoryExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_PARENT_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_PARENT_ID_EXISTS[1],
            ], 404);
        }

        $checkCategoryExists = CategoryPostChild::where(
            'name',
            $request->name
        )->where(
            'store_id',
            $request->store->id
        )
            ->where(
                'category_post_id',
                $category_id
            )
            ->first();

        if ($checkCategoryExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CATEGORY_CHILD_NAME_EXISTS[0],
                'msg' => MsgCode::CATEGORY_CHILD_NAME_EXISTS[1],
            ], 400);
        }

        $checkCategoryUrlExists = CategoryPostChild::where(
            'post_category_children_url',
            $request->post_category_children_url
        )->where(
            'store_id',
            $request->store->id
        )
            ->where(
                'category_post_id',
                $category_id
            )
            ->first();

        if ($checkCategoryUrlExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CATEGORY_CHILD_NAME_EXISTS[0],
                'msg' => "URL này đã được sử dụng",
            ], 400);
        }

        if ($request->post_category_children_url == null) {
            $slug = Str::slug($request->name);
        } else {
            $slug = $request->post_category_children_url;
        }

        $slugCreate = DB::table('slugs')->insert([
            'type' => 'post-category',
            'value' => $slug,
        ]);

        $categoryCreate = CategoryPostChild::create(
            [
                'image_url' => $imageUrl,
                'name' => $request->name,
                'store_id' => $request->store->id,
                'category_post_id' =>  $category_id,
                'post_category_children_url' => $request->post_category_children_url ? $request->post_category_children_url : Str::slug($request->name),
                'meta_robots_index' => $request->meta_robots_index,
                'meta_robots_follow' => $request->meta_robots_follow,
                'canonical_url' => $request->canonical_url,
            ]
        );
        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categoryCreate
        ], 201);
    }


    /**
     * xóa một danh mục
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID category cần xóa thông tin.
     */
    public function deleteOneCategory(Request $request, $id)
    {

        $category_id = $request->route()->parameter('category_id');
        $category_children_id = $request->route()->parameter('category_children_id');


        $checkCategoryExists = CategoryPost::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();


        $checkCategoryExists = CategoryPostChild::where(
            'id',
            $category_children_id
        )->where(
            'category_post_id',
            $category_id
        )
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if (empty($checkCategoryExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
            ], 404);
        }

        $idDeleted = $checkCategoryExists->id;
        $checkCategoryExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }


    /**
     * update một Category
     * @urlParam  store_code required Store code cần update
     * @urlParam  category_id required Category_id cần update
     * @bodyParam name string required Tên danh mục
     * @bodyParam image file required Ảnh (hoặc truyền lên image_url)
     */
    public function updateOneCategory(Request $request)
    {

        $category_id = $request->route()->parameter('category_id');
        $category_children_id = $request->route()->parameter('category_children_id');

        $imageUrl = $request->image_url;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath(), $request->image_type, $request->image->getClientMimeType());
        }


        $checkCategoryExists = CategoryPostChild::where(
            'id',
            $category_children_id
        )->where(
            'category_post_id',
            $category_id
        )
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if (empty($checkCategoryExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
            ], 404);
        }



        $checkCategoryExists2 = CategoryPostChild::where(
            'name',
            $request->name
        )->where(
            'category_post_id',
            $category_id
        )->where(
            'store_id',
            $request->store->id
        )->where(
            'id',
            '<>',
            $category_children_id
        )->first();
        if ($checkCategoryExists2 != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CATEGORY_NAME_EXISTS[0],
                'msg' => MsgCode::CATEGORY_NAME_EXISTS[1],
            ], 400);
        }

        $checkCategoryUrlExists = CategoryPostChild::where(
            'post_category_children_url',
            $request->post_category_children_url
        )->where(
            'category_post_id',
            $category_id
        )->where(
            'store_id',
            $request->store->id
        )->where(
            'id',
            '<>',
            $category_children_id
        )->first();

        if ($checkCategoryUrlExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CATEGORY_NAME_EXISTS[0],
                'msg' => "URL này đã được sử dụng",
            ], 400);
        }

        $slug = Str::slug($request->name);

            if ($checkCategoryExists->post_category_children_url == null) {
                $slugExsits = DB::table('slugs')
                    ->where('type', 'post-category')
                    ->where('value', Str::slug($checkCategoryExists->name))->first();
            } else {
                $slugExsits = DB::table('slugs')
                    ->where('type', 'post-category')
                    ->where('value', $checkCategoryExists->post_category_children_url)->first();
            }

            if ($slugExsits != null) {
                if ($request->post_category_children_url == null) {
                    $slug = Str::slug($request->name);
                } else {
                    $slug = $request->post_category_children_url;
                }
                DB::table('slugs')
                    ->where('id', $slugExsits->id)->update([
                        'value' => $slug,
                    ]);
            } else {
                if ($request->post_category_children_url == null) {
                    $slug = Str::slug($request->name);
                } else {
                    $slug = $request->post_category_children_url;
                }


                $slugCreate = DB::table('slugs')->insert([
                    'type' => 'post-category',
                    'value' => $slug,
                ]);
            }

        $checkCategoryExists->update(Helper::sahaRemoveItemArrayIfNullValue([
            'image_url' => $imageUrl,
            'name' => $request->name,
            'post_category_children_url' => $request->post_category_children_url ? $request->post_category_children_url : Str::slug($request->name),
            'meta_robots_index' => $request->meta_robots_index,
            'meta_robots_follow' => $request->meta_robots_follow,
            'canonical_url' => $request->canonical_url,
        ]));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CategoryPostChild::where('id',  $category_children_id)->first(),
        ], 200);
    }
}
