<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\CategoryPost;
use App\Models\MsgCode;
use App\Services\UploadImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


/**
 * @group User/Danh mục bài viết
 * 
 * Danh mục bài viết
 */


class CategoryPostController extends Controller
{
    /**
     * Tạo danh mục bài viết
     * @urlParam  store_code required Store code
     * @bodyParam title string required Tiêu đề danh mục
     * @bodyParam image file required Ảnh (hoặc truyền lên image_url)
     * @bodyParam description string required Nội dung mô tả danh mục
     */
    public function create(Request $request)
    {
        $imageUrl = null;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath(), $request->image_type, $request->image->getClientMimeType());
        } else {
            $imageUrl = $request->image_url;
        }

        $checkCategoryExists = CategoryPost::where(
            'title',
            $request->title
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkCategoryExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CATEGORY_EXISTS[0],
                'msg' => MsgCode::CATEGORY_EXISTS[1],
            ], 400);
        }

        $checkCategoryExistsUrl = CategoryPost::where(
            'post_category_url',
            $request->post_category_url
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkCategoryExistsUrl != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CATEGORY_NAME_EXISTS[0],
                'msg' => "URL này đã được sử dụng",
            ], 400);
        }

        if ($request->post_category_url == null) {
            $slug = Str::slug($request->name);
        } else {
            $slug = $request->post_category_url;
        }

        $slugCreate = DB::table('slugs')->insert([
            'type' => 'post-category',
            'value' => $slug,
        ]);

        $categoryCreate = CategoryPost::create(
            [
                'image_url' => $imageUrl,
                'store_id' => $request->store->id,
                'description' => $request->description,
                'title' => $request->title,
                'is_show_home' => $request->is_show_home,
                'post_category_url' => $request->post_category_url ? $request->post_category_url : Str::slug($request->title),
                'meta_robots_index' => $request->meta_robots_index,
                'meta_robots_follow' => $request->meta_robots_follow,
                'canonical_url' => $request->canonical_url,
                "seo_title" => $request->txtSeoTitle,
                "seo_description" => $request->txtSeoDescription,
            ]
        );

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_ADD,
            TypeAction::FUNCTION_TYPE_CATEGORY_POST,
            "Tạo danh mục bài viết: " . $categoryCreate->title,
            $categoryCreate->id,
            $categoryCreate->title
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
     * Danh sách danh mục Post
     * @urlParam  store_code required Store code
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

    /**
     * xóa một danh mục Post
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID category cần xóa thông tin.
     */
    public function deleteOneCategory(Request $request, $id)
    {

        $id = $request->route()->parameter('category_id');
        $checkCategoryExists = CategoryPost::where(
            'id',
            $id
        )->where(
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
        } else {
            $idDeleted = $checkCategoryExists->id;

            SaveOperationHistoryUtils::save(
                $request,
                TypeAction::OPERATION_ACTION_DELETE,
                TypeAction::FUNCTION_TYPE_CATEGORY_POST,
                "Xóa danh mục bài viết: " . $checkCategoryExists->title,
                $checkCategoryExists->id,
                $checkCategoryExists->title
            );

            $checkCategoryExists->delete();
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => ['idDeleted' => $idDeleted],
            ], 200);
        }
    }


    /**
     * update một Category Post
     * @urlParam  store_code required Store code cần update
     * @urlParam  category_id required Category_id cần update
     * @bodyParam title string required Tên danh mục
     * @bodyParam description
     * @bodyParam image file required Ảnh (hoặc truyền lên image_url)
     */
    public function updateOneCategory(Request $request)
    {

        $imageUrl = $request->image_url;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath(), $request->image_type, $request->image->getClientMimeType());
        }


        $id = $request->route()->parameter('category_id');
        $checkCategoryExists = CategoryPost::where(
            'id',
            $id
        )->where(
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
        } else {


            $checkCategoryExists2 = CategoryPost::where(
                'title',
                $request->title
            )->where(
                'store_id',
                $request->store->id
            )->where(
                'id',
                '<>',
                $id
            )->first();
            if ($checkCategoryExists2 != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::CATEGORY_NAME_EXISTS[0],
                    'msg' => MsgCode::CATEGORY_NAME_EXISTS[1],
                ], 400);
            }

            $checkCategoryExistsUrl = CategoryPost::where(
                'post_category_url',
                $request->post_category_url
            )->where(
                'store_id',
                $request->store->id
            )->where(
                'id',
                '<>',
                $id
            )->first();
            if ($checkCategoryExistsUrl != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::CATEGORY_NAME_EXISTS[0],
                    'msg' => "URL này đã được sử dụng",
                ], 400);
            }

            $slug = Str::slug($request->title);

            if ($checkCategoryExists->post_category_url == null) {
                $slugExsits = DB::table('slugs')
                    ->where('type', 'post-category')
                    ->where('value', Str::slug($checkCategoryExists->title))->first();
            } else {
                $slugExsits = DB::table('slugs')
                    ->where('type', 'post-category')
                    ->where('value', $checkCategoryExists->post_category_url)->first();
            }

            if ($slugExsits != null) {
                if ($request->post_category_url == null) {
                    $slug = Str::slug($request->title);
                } else {
                    $slug = $request->post_category_url;
                }
                DB::table('slugs')
                    ->where('id', $slugExsits->id)->update([
                        'value' => $slug,
                    ]);
            } else {
                if ($request->post_category_url == null) {
                    $slug = Str::slug($request->title);
                } else {
                    $slug = $request->post_category_url;
                }


                $slugCreate = DB::table('slugs')->insert([
                    'type' => 'post-category',
                    'value' => $slug,
                ]);
            }

            $checkCategoryExists->update(Helper::sahaRemoveItemArrayIfNullValue([
                'image_url' => $imageUrl,
                'title' => $request->title,
                'description' => $request->description,
                'is_show_home' => $request->is_show_home,
                'post_category_url' => $request->post_category_url ? $request->post_category_url : Str::slug($request->title),
                'meta_robots_index' => $request->meta_robots_index,
                'meta_robots_follow' => $request->meta_robots_follow,
                'canonical_url' => $request->canonical_url,
                "seo_title" => $request->txtSeoTitle,
                "seo_description" => $request->txtSeoDescription,
            ]));


            SaveOperationHistoryUtils::save(
                $request,
                TypeAction::OPERATION_ACTION_UPDATE,
                TypeAction::FUNCTION_TYPE_CATEGORY_POST,
                "Cập nhật danh mục bài viết: " . $checkCategoryExists->title,
                $checkCategoryExists->id,
                $checkCategoryExists->title
            );

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => CategoryPost::where('id', $id)->first(),
            ], 200);
        }
    }
}
