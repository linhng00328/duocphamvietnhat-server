<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\AttributeField;
use App\Models\MsgCode;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * @group User/Thuộc tính sản phẩm
 */
class AttributeFieldController extends Controller
{

    /**
     * Xem tất cả thuộc tính
     * @urlParam  store_code required Store code
     * @queryParam  with_product_id Với attribute của product
     * @queryParam  no_attribute_default không cần thiết đặt
     */
    public function getAll(Request $request, $id)
    {

        $no_attribute_default = filter_var(request('no_attribute_default'), FILTER_VALIDATE_BOOLEAN);

        $with_product_id = request('with_product_id');

        $arr_with_pro = [];
        if ($with_product_id != null) {
            $product = Product::where(
                'store_id',
                $request->store->id
            )->where('id',   $with_product_id)->first();
            if ($product != null &&  $product->attributes != null && count($product->attributes) > 0) {
                $arr_with_pro = $product->attributes->pluck("name")->toArray();
            }
        }

        if ($no_attribute_default == true) {
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' =>   $arr_with_pro

            ], 200);
        }

        $attributeFieldExists = AttributeField::where(
            'store_id',
            $request->store->id
        )->first();

        $list = [];

        if ($attributeFieldExists != null && $attributeFieldExists->fields != null) {
            $list = json_decode($attributeFieldExists->fields, true);
        }

        if ($list != null && is_array($list)) {
        } else {
            $list = [];
        }

        foreach ($arr_with_pro as $a) {
            if (!in_array($a, $list)) {
                array_push($list, $a);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $list

        ], 200);
    }



    /**
     * Cập nhật danh sách thuộc tính
     * @urlParam  store_code required Store code cần xóa.
     * @bodyParam
     */
    public function updateAttributeField(Request $request)
    {

        if ($request->list != null && count($request->list) == 0) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::INVALID_ATTRIBUTE_FIELDS[0],
                'msg' => MsgCode::INVALID_ATTRIBUTE_FIELDS[1],
            ], 404);
        }
        $fields = json_encode($request->list);


        $attributeFieldExists = AttributeField::where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($attributeFieldExists)) {
            AttributeField::create(
                [
                    'store_id' => $request->store->id,
                    'fields' =>  $fields
                ]
            );
        } else {
            $attributeFieldExists->update(
                [
                    'fields' =>  $fields
                ]
            );
        }

        $attributeFieldExists = AttributeField::where(
            'store_id',
            $request->store->id
        )->first();
        $list = json_decode($attributeFieldExists->fields, true);

        if ($list != null && is_array($list)) {
        } else {
            $list = [];
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $list

        ], 200);
    }
}
