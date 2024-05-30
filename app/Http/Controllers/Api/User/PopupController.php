<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\PopupCustomer;
use Illuminate\Http\Request;

class PopupController extends Controller
{

    /**
     * Thông tin một bài viết
     *   Gửi danh sách button lên type_action gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
     * @urlParam  store_code required Store code cần lấy.
     * @bodyParam  name required Tên
     * @bodyParam  link_image required Ảnh hiển thị lên
     * @bodyParam  show_once required Chỉ show 1 lần
     * @bodyParam type_action gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
     * @bodyParam value_action string giá trị thực thi ví dụ  id cate,product hoặc link (string)
     */
    public function create(Request $request)
    {
        $popupExits = PopupCustomer::where('store_id', $request->store->id)
            ->where('name', $request->name)->first();

        if ($popupExits != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }

        if ($request->link_image == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::IMAGE_URL_IS_REQUIRED[0],
                'msg' => MsgCode::IMAGE_URL_IS_REQUIRED[1],
            ], 400);
        }

        PopupCustomer::create([
            "store_id" => $request->store->id,
            "name" => $request->name,
            "link_image" => $request->link_image,
            "show_once" => $request->show_once,
            "type_action" => $request->type_action,
            "value_action" => $request->value_action,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * update một Popup
     * @urlParam  store_code required Store code cần lấy.
     * @bodyParam  name required Tên
     * @bodyParam  link_image required Ảnh hiển thị lên
     * @bodyParam  show_once required Chỉ show 1 lần
     * @bodyParam type_action gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
     * @bodyParam value_action string giá trị thực thi ví dụ  id cate,product hoặc link (string)
     */
    public function updateOnePopup(Request $request)
    {

        if ($request->link_image == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::IMAGE_URL_IS_REQUIRED[0],
                'msg' => MsgCode::IMAGE_URL_IS_REQUIRED[1],
            ], 400);
        }

        $id = $request->route()->parameter('popup_id');
        $checkPopupExists = PopupCustomer::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkPopupExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST[0],
                'msg' => MsgCode::DOES_NOT_EXIST[1],
            ], 404);
        } else {
            $checkPopupExists->update([
                "name" => $request->name,
                "link_image" => $request->link_image,
                "show_once" => $request->show_once,
                "type_action" => $request->type_action,
                "value_action" => $request->value_action,
            ]);
            
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => PopupCustomer::where('id', $id)->first(),
            ], 200);
        }
    }

    /**
     * xóa một danh mục
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  popup_id required ID Popup cần xóa thông tin.
     */
    public function deleteOnePopup(Request $request, $id)
    {

        $id = $request->route()->parameter('popup_id');
        $checkPopupExists = PopupCustomer::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkPopupExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST[0],
                'msg' => MsgCode::DOES_NOT_EXIST[1],
            ], 404);
        } else {
            $idDeleted = $checkPopupExists->id;
            $checkPopupExists->delete();
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
     * Danh sách popup
     * @urlParam  store_code required Store code
     */
    public function getPopupAll(Request $request)
    {

        $popups = PopupCustomer::where('store_id', $request->store->id)->
        orderBy('created_at','desc')->get();;

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $popups,
        ], 200);
    }
}

