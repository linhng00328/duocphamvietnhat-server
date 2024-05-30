<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MsgCode;
use App\Services\UploadImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


/**
 * @group User/Danh mục sản phẩm
 * 
 * APIs AppTheme
 */
class CategoryController extends Controller
{
    /**
     * Tạo danh mục sản phẩm
     * @urlParam  store_code required Store code
     * @bodyParam name string required Tên danh mục
     * @bodyParam image file required Ảnh (hoặc truyền lên image_url)
     */
    public function create(Request $request)
    {
        $imageUrl = null;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath(), $request->image_type, $request->image->getClientMimeType());
        } else {
            $imageUrl = $request->image_url;
        }

        $checkCategoryExists = Category::where(
            'name',
            $request->name
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
        if ($request->category_url != null) {
            $checkCategoryUrlExists = Category::where(
                'category_url',
                $request->category_url
            )->where(
                'store_id',
                $request->store->id
            )->first();

            if ($checkCategoryUrlExists != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => "CATEGORY_URL_EXISTS",
                    'msg' => "URL này đã được sử dụng",
                ], 400);
            }
        }



        $min_position = Category::where('store_id', $request->store->id)
            ->min('position');


        $ads = array();
        if (is_array($request->banner_ads)) {
            foreach ($request->banner_ads as $a) {
                if (isset($a['image'])) {
                    array_push($ads, [
                        'image' => $a['image'],
                        'link' => $a['link'],
                    ]);
                }
            }
        }
        if ($request->category_url == null) {
            $slug = Str::slug($request->name);
        } else {
            $slug = $request->category_url;
        }

        $slugCreate = DB::table('slugs')->insert([
            'type' => 'product-category',
            'value' => $slug,
        ]);


        $categoryCreate = Category::create(
            [
                'image_url' => $imageUrl,
                'name' => $request->name,
                'category_url' => $request->category_url ? $request->category_url : Str::slug($request->name),
                'meta_robots_index' => $request->meta_robots_index,
                'meta_robots_follow' => $request->meta_robots_follow,
                'canonical_url' => $request->canonical_url,
                'store_id' => $request->store->id,
                'is_show_home' => filter_var($request->is_show_home, FILTER_VALIDATE_BOOLEAN),
                'position' => $min_position == null ? 0 : $min_position - 1,
                'banner_ads_json' => json_encode($ads),
                'description' => $request->description,
                "seo_title" => $request->txtSeoTitle,
                "seo_description" => $request->txtSeoDescription,
            ]
        );


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_ADD,
            TypeAction::FUNCTION_TYPE_CATEGORY_PRODUCT,
            "Thêm danh mục sản phẩm: " . $request->name,
            $categoryCreate->id,
            $request->name
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
     * Danh sách danh mục sản phẩm
     * @urlParam  store_code required Store code
     */
    public function getAll(Request $request)
    {

        $categories = Category::where('store_id', $request->store->id)
            ->orderBy('position', 'ASC')->get();

        foreach ($categories as $category) {
            $category->total_products = $category->getTotalProducts();
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categories,
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
                $categorie = Category::where('store_id', $request->store->id)->where('id', $id)->first();
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

    /**
     * xóa một danh mục
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID category cần xóa thông tin.
     */
    public function deleteOneCategory(Request $request, $id)
    {

        $id = $request->route()->parameter('category_id');
        $checkCategoryExists = Category::where(
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
                TypeAction::FUNCTION_TYPE_CATEGORY_PRODUCT,
                "Xóa danh mục sản phẩm: " . $checkCategoryExists->name,
                $checkCategoryExists->id,
                $checkCategoryExists->name
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
     * update một Category
     * @urlParam  store_code required Store code cần update
     * @urlParam  category_id required Category_id cần update
     * @bodyParam name string required Tên danh mục
     * @bodyParam image file required Ảnh (hoặc truyền lên image_url)
     * @bodyParam is_show_home required Có show ở màn hình home không
     */
    public function updateOneCategory(Request $request)
    {
        $imageUrl = $request->image_url;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath(), $request->image_type, $request->image->getClientMimeType());
        }


        $id = $request->route()->parameter('category_id');
        $checkCategoryExists = Category::where(
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
            $checkCategoryExists2 = Category::where(
                'name',
                $request->name
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
            if ($request->category_url != null) {
                $checkCategoryExistsUrl = Category::where(
                    'category_url',
                    $request->category_url
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
                        'msg_code' => "CATEGORY_URL_EXISTS",
                        'msg' => "URL này đã được sử dụng",
                    ], 400);
                }
            }

            $ads = array();
            if (is_array($request->banner_ads)) {
                foreach ($request->banner_ads as $a) {
                    if (isset($a['image'])) {
                        array_push($ads, [
                            'image' => $a['image'],
                            'link' => $a['link'],
                        ]);
                    }
                }
            }

            $slug = Str::slug($request->name);

            if ($checkCategoryExists->category_url == null) {
                $slugExsits = DB::table('slugs')
                    ->where('type', 'product-category')
                    ->where('value', Str::slug($checkCategoryExists->name))->first();
            } else {
                $slugExsits = DB::table('slugs')
                    ->where('type', 'product-category')
                    ->where('value', $checkCategoryExists->category_url)->first();
            }

            if ($slugExsits != null) {
                if ($request->category_url == null) {
                    $slug = Str::slug($request->name);
                } else {
                    $slug = $request->category_url;
                }
                DB::table('slugs')
                    ->where('id', $slugExsits->id)->update([
                        'value' => $slug,
                    ]);
            } else {
                if ($request->category_url == null) {
                    $slug = Str::slug($request->name);
                } else {
                    $slug = $request->category_url;
                }


                $slugCreate = DB::table('slugs')->insert([
                    'type' => 'product-category',
                    'value' => $slug,
                ]);
            }

            $checkCategoryExists->update(Helper::sahaRemoveItemArrayIfNullValue([
                'image_url' => $imageUrl,
                'name' => $request->name,
                'category_url' => $request->category_url ? $request->category_url : Str::slug($request->name),
                'meta_robots_index' => $request->meta_robots_index,
                'meta_robots_follow' => $request->meta_robots_follow,
                'canonical_url' => $request->canonical_url,
                'banner_ads_json' => json_encode($ads),
                'is_show_home' => filter_var($request->is_show_home, FILTER_VALIDATE_BOOLEAN),
                "seo_title" => $request->txtSeoTitle,
                "seo_description" => $request->txtSeoDescription,
            ]));


            SaveOperationHistoryUtils::save(
                $request,
                TypeAction::OPERATION_ACTION_UPDATE,
                TypeAction::FUNCTION_TYPE_CATEGORY_PRODUCT,
                "Cập nhật danh mục sản phẩm: " . $checkCategoryExists->name,
                $checkCategoryExists->id,
                $checkCategoryExists->name
            );

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => Category::where('id', $id)->first(),
            ], 200);
        }
    }
}
