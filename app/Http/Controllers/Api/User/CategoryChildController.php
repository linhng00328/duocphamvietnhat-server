<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryChild;
use App\Models\MsgCode;
use App\Services\UploadImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


/**
 * @group User/Danh mục sản phẩm con
 * 
 * APIs Danh mục sản phẩm
 */
class CategoryChildController extends Controller
{
    /**
     * Tạo danh mục sản phẩm con
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

        $checkCategoryExists = Category::where(
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

        $checkCategoryExists = CategoryChild::where(
            'name',
            $request->name
        )->where(
            'store_id',
            $request->store->id
        )
            ->where(
                'category_id',
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

        if ($request->category_children_url) {
            $checkCategoryUrlExists = CategoryChild::where(
                'category_children_url',
                $request->category_children_url
            )->where(
                'store_id',
                $request->store->id
            )
                ->where(
                    'category_id',
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
        }



        if ($request->category_children_url == null) {
            $slug = Str::slug($request->name);
        } else {
            $slug = $request->category_children_url;
        }

        $slugCreate = DB::table('slugs')->insert([
            'type' => 'product-category',
            'value' => $slug,
        ]);

        $categoryCreate = CategoryChild::create(
            [
                'image_url' => $imageUrl,
                'name' => $request->name,
                'store_id' => $request->store->id,
                'category_id' =>  $category_id,
                'description' => $request->description,
                'category_children_url' => $request->category_children_url ? $request->category_children_url : Str::slug($request->name),
                'meta_robots_index' => $request->meta_robots_index,
                'meta_robots_follow' => $request->meta_robots_follow,
                'canonical_url' => $request->canonical_url,
                "seo_title" => $request->txtSeoTitle,
                "seo_description" => $request->txtSeoDescription,
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


        $checkCategoryExists = Category::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();


        $checkCategoryExists = CategoryChild::where(
            'id',
            $category_children_id
        )->where(
            'category_id',
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


        $checkCategoryExists = CategoryChild::where(
            'id',
            $category_children_id
        )->where(
            'category_id',
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
        if ($request->category_children_url != null) {
            $checkCategoryExistsUrl = CategoryChild::where(
                'category_children_url',
                $request->category_children_url
            )->where(
                'category_id', '<>',
                $category_id
            )
                ->where(
                    'store_id',
                    $request->store->id
                )->first();
            if ($checkCategoryExistsUrl != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => "CATEGORY_URL_EXISTS",
                    'msg' => "URL này đã được sử dụng",
                ], 400);
            }
        }



        $checkCategoryExists2 = CategoryChild::where(
            'name',
            $request->name
        )->where(
            'category_id',
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

        $slug = Str::slug($request->name);

        if ($checkCategoryExists->category_children_url == null) {
            $slugExsits = DB::table('slugs')
                ->where('type', 'product-category')
                ->where('value', Str::slug($checkCategoryExists->name))->first();
        } else {
            $slugExsits = DB::table('slugs')
                ->where('type', 'product-category')
                ->where('value', $checkCategoryExists->category_children_url)->first();
        }

        if ($slugExsits != null) {
            if ($request->category_children_url == null) {
                $slug = Str::slug($request->name);
            } else {
                $slug = $request->category_children_url;
            }
            DB::table('slugs')
                ->where('id', $slugExsits->id)->update([
                    'value' => $slug,
                ]);
        } else {
            if ($request->category_children_url == null) {
                $slug = Str::slug($request->name);
            } else {
                $slug = $request->category_children_url;
            }


            $slugCreate = DB::table('slugs')->insert([
                'type' => 'product-category',
                'value' => $slug,
            ]);
        }

        $checkCategoryExists->update(Helper::sahaRemoveItemArrayIfNullValue([
            'image_url' => $imageUrl,
            'name' => $request->name,
            'description' => $request->description,
            'category_children_url' => $request->category_children_url ? $request->category_children_url : Str::slug($request->name),
            'meta_robots_index' => $request->meta_robots_index,
            'meta_robots_follow' => $request->meta_robots_follow,
            'canonical_url' => $request->canonical_url,
            "seo_title" => $request->txtSeoTitle,
            "seo_description" => $request->txtSeoDescription,
        ]));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => CategoryChild::where('id',  $category_children_id)->first(),
        ], 200);
    }

    /**
     * Sắp xếp lại thứ tự category
     * @urlParam  store_code required Store code cần xóa.
     * @bodyParam  List<ids> required List id cate VD: [4,8,9]
     * @bodyParam  List<positions> required List vị trí theo danh sách id ở trên [1,2,3]
     */
    public function sortCategory(Request $request, $id)
    {

        $i = 0;
        if (is_array($request->ids) && is_array($request->positions)) {
            foreach ($request->ids as $id) {
                $categorie = CategoryChild::where('store_id', $request->store->id)->where('id', $id)->first();
                if ($categorie != null) {
                    $categorie->update(["position" => $request->positions[$i]]);
                }
                $i++;
            }
        }
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
