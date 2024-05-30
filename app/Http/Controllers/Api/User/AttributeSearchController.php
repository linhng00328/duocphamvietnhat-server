<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\AttributeSearch;
use App\Models\MsgCode;
use App\Services\UploadImageService;
use Illuminate\Http\Request;
use App\Models\AttributeSearchChild;
use App\Models\ProAttSearchChild;

/**
 * @group User/Danh mục sản phẩm
 * 
 * APIs AppTheme
 */
class AttributeSearchController extends Controller
{
    /**
     * Tạo thuộc tính tìm kiếm
     * @urlParam  store_code required Store code
     * @bodyParam name string required Tên danh mục
     * @bodyParam image file required Ảnh (hoặc truyền lên image_url)
     */
    public function create(Request $request)
    {
        $imageUrl = null;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath());
        }

        $checkAttributeSearchExists = AttributeSearch::where(
            'name',
            $request->name
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkAttributeSearchExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ATTRIBUTE_EXISTS[0],
                'msg' => MsgCode::ATTRIBUTE_EXISTS[1],
            ], 400);
        }

        $min_position = AttributeSearch::where('store_id', $request->store->id)
            ->min('position');



        $attribute_searchCreate = AttributeSearch::create(
            [
                'image_url' => $imageUrl,
                'name' => $request->name,
                'store_id' => $request->store->id,
                'position' => $min_position == null ? 0 : $min_position - 1
            ]
        );


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_ADD,
            TypeAction::FUNCTION_TYPE_CATEGORY_PRODUCT,
            "Thêm thuộc tính cha tìm kiếm sản phẩm: " . $request->name,
            $attribute_searchCreate->id,
            $request->name
        );


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $attribute_searchCreate
        ], 201);
    }


    /**
     * Danh sách thuộc tính tìm kiếm
     * @urlParam  store_code required Store code
     */
    public function getAll(Request $request)
    {

        $categories = AttributeSearch::where('store_id', $request->store->id)
            ->orderBy('position', 'ASC')->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categories,
        ], 200);
    }

    /**
     * Sắp xếp lại thứ tự attribute_search
     * @urlParam  store_code required Store code cần xóa.
     * @bodyParam  List<ids> required List id cate VD: [4,8,9]
     * @bodyParam  List<positions> required List vị trí theo danh sách id ở trên [1,2,3]
     */
    public function sort(Request $request, $id)
    {

        $i = 0;
        if (is_array($request->ids) && is_array($request->positions)) {
            foreach ($request->ids as $id) {
                $categorie = AttributeSearch::where('store_id', $request->store->id)->where('id', $id)->first();
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
     * @urlParam  id required ID attribute_search cần xóa thông tin.
     */
    public function deleteOne(Request $request, $id)
    {

        $id = $request->route()->parameter('attribute_search_id');
        $checkAttributeSearchExists = AttributeSearch::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkAttributeSearchExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
            ], 404);
        } else {
            $idDeleted = $checkAttributeSearchExists->id;

            SaveOperationHistoryUtils::save(
                $request,
                TypeAction::OPERATION_ACTION_DELETE,
                TypeAction::FUNCTION_TYPE_CATEGORY_PRODUCT,
                "Xóa thuộc tính tìm kiếm: " . $checkAttributeSearchExists->name,
                $checkAttributeSearchExists->id,
                $checkAttributeSearchExists->name
            );

            $checkAttributeSearchExists->delete();
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
     * update một AttributeSearch
     * @urlParam  store_code required Store code cần update
     * @urlParam  attribute_search_id required AttributeSearch_id cần update
     * @bodyParam name string required Tên danh mục
     * @bodyParam image file required Ảnh (hoặc truyền lên image_url)
     * @bodyParam is_show_home required Có show ở màn hình home không
     */
    public function updateOne(Request $request)
    {

        $imageUrl = $request->image_url;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath());
        }


        $id = $request->route()->parameter('attribute_search_id');
        $checkAttributeSearchExists = AttributeSearch::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkAttributeSearchExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ATTRIBUTE_ID_EXISTS[0],
                'msg' => MsgCode::NO_ATTRIBUTE_ID_EXISTS[1],
            ], 404);
        } else {
            $checkAttributeSearchExists2 = AttributeSearch::where(
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
            if ($checkAttributeSearchExists2 != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ATTRIBUTE_EXISTS[0],
                    'msg' => MsgCode::ATTRIBUTE_EXISTS[1],
                ], 400);
            }

            $checkAttributeSearchExists->update(Helper::sahaRemoveItemArrayIfNullValue([
                'image_url' => $imageUrl,
                'name' => $request->name,
            ]));


            SaveOperationHistoryUtils::save(
                $request,
                TypeAction::OPERATION_ACTION_UPDATE,
                TypeAction::FUNCTION_TYPE_CATEGORY_PRODUCT,
                "Cập nhật thuộc tính tìm kiếm: " . $checkAttributeSearchExists->name,
                $checkAttributeSearchExists->id,
                $checkAttributeSearchExists->name
            );

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => AttributeSearch::where('id', $id)->first(),
            ], 200);
        }
    }


    /**
     * Tạo thuộc tính tìm kiếm con
     * @urlParam  store_code required Store code
     * @urlParam  attribute_search_id required attribute_search_id
     * @bodyParam name string required Tên danh mục con
     * @bodyParam image file required Ảnh (hoặc truyền lên image_url)
     */
    public function createChild(Request $request)
    {
        $attribute_search_id = $request->route()->parameter('attribute_search_id');

        $imageUrl = null;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath());
        } else {
            $imageUrl = $request->image_url;
        }

        $checkAttributeSearchExists = AttributeSearch::where(
            'id',
            $attribute_search_id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkAttributeSearchExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_PARENT_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_PARENT_ID_EXISTS[1],
            ], 404);
        }

        $checkAttributeSearchExists = AttributeSearchChild::where(
            'name',
            $request->name
        )->where(
            'store_id',
            $request->store->id
        )
            ->where(
                'attribute_search_id',
                $attribute_search_id
            )
            ->first();

        if ($checkAttributeSearchExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CATEGORY_CHILD_NAME_EXISTS[0],
                'msg' => MsgCode::CATEGORY_CHILD_NAME_EXISTS[1],
            ], 400);
        }

        $attribute_searchCreate = AttributeSearchChild::create(
            [
                'image_url' => $imageUrl,
                'name' => $request->name,
                'store_id' => $request->store->id,
                'attribute_search_id' =>  $attribute_search_id
            ]
        );
        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $attribute_searchCreate
        ], 201);
    }


    /**
     * xóa một danh mục
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID attribute_search cần xóa thông tin.
     */
    public function deleteOneChild(Request $request, $id)
    {

        $attribute_search_id = $request->route()->parameter('attribute_search_id');
        $child_id = $request->route()->parameter('child_id');


        $checkAttributeSearchExists = AttributeSearch::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();


        $checkAttributeSearchExists = AttributeSearchChild::where(
            'id',
            $child_id
        )->where(
            'attribute_search_id',
            $attribute_search_id
        )
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if (empty($checkAttributeSearchExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
            ], 404);
        }

        $idDeleted = $checkAttributeSearchExists->id;
        $checkAttributeSearchExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }


    /**
     * update một AttributeSearch
     * @urlParam  store_code required Store code cần update
     * @urlParam  attribute_search_id required AttributeSearch_id cần update
     * @bodyParam name string required Tên danh mục
     * @bodyParam image file required Ảnh (hoặc truyền lên image_url)
     */
    public function updateOneChild(Request $request)
    {

        $attribute_search_id = $request->route()->parameter('attribute_search_id');
        $child_id = $request->route()->parameter('child_id');

        $imageUrl = $request->image_url;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath());
        }


        $checkAttributeSearchExists = AttributeSearchChild::where(
            'id',
            $child_id
        )->where(
            'attribute_search_id',
            $attribute_search_id
        )
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if (empty($checkAttributeSearchExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
            ], 404);
        }



        $checkAttributeSearchExists2 = AttributeSearchChild::where(
            'name',
            $request->name
        )->where(
            'attribute_search_id',
            $attribute_search_id
        )->where(
            'store_id',
            $request->store->id
        )->where(
            'id',
            '<>',
            $child_id
        )->first();
        if ($checkAttributeSearchExists2 != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CATEGORY_NAME_EXISTS[0],
                'msg' => MsgCode::CATEGORY_NAME_EXISTS[1],
            ], 400);
        }

        $checkAttributeSearchExists->update(Helper::sahaRemoveItemArrayIfNullValue([
            'image_url' => $imageUrl,
            'name' => $request->name,
        ]));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AttributeSearchChild::where('id',  $child_id)->first(),
        ], 200);
    }


}
