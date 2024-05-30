<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\AdminBanner;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  Admin/Banner
 */
class AdminBannerController extends Controller
{
    /**
     * Thêm banner
     * @urlParam  store_code required Store code cần lấy
     * @bodyParam image_url 
     * @bodyParam  title
     * @bodyParam  action_link
     */
    public function create(Request $request)
    {

        if ($request->image_url == null) {
            return response()->json([
                'code' => 400,
                'success' => true,
                'msg_code' => MsgCode::IMAGE_URL_IS_REQUIRED[0],
                'msg' => MsgCode::IMAGE_URL_IS_REQUIRED[1],
            ], 400);
        }
        if ($request->title == null) {
            return response()->json([
                'code' => 400,
                'success' => true,
                'msg_code' => MsgCode::TITLE_IS_REQUIRED[0],
                'msg' => MsgCode::TITLE_IS_REQUIRED[1],
            ], 400);
        }
        $bannerCreated = AdminBanner::create(
            [
                "image_url" => $request->image_url,
                "title" => $request->title,
                "action_link" => $request->action_link,
            ]
        );

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $bannerCreated
        ], 201);
    }

    /**
     * Danh sách banner
     */
    public function getAll(Request $request)
    {

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  AdminBanner::get()
        ], 201);
    }


    /**
     * update một Banner
     * @bodyParam image_url 
     * @bodyParam  title
     * @bodyParam  action_link
     */
    public function updateOneBanner(Request $request)
    {


        $id = $request->route()->parameter('banner_id');
        $checkBannerExists = AdminBanner::where(
            'id',
            $id
        )->first();

        if (empty($checkBannerExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_BANNER_EXISTS[0],
                'msg' => MsgCode::NO_BANNER_EXISTS[1],
            ], 404);
        } else {
            $checkBannerExists->update(Helper::sahaRemoveItemArrayIfNullValue([
                'image_url' => $request->image_url,
                'title' => $request->title,
                'action_link' => $request->action_link,
                
            ]));

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => AdminBanner::where('id', $id)->first(),
            ], 200);
        }
    }

    /**
     * Xóa 1 banner
     * @bodyParam image_url 
     * @bodyParam  title
     * @bodyParam  action_link
     */
    public function deleteOneBanner(Request $request)
    {

        $id = $request->route()->parameter('banner_id');
        $checkBannerExists = AdminBanner::where(
            'id',
            $id
        )->first();

        if (empty($checkBannerExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_ID_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_ID_EXISTS[1],
            ], 404);
        } else {
            $idDeleted = $checkBannerExists->id;
            $checkBannerExists->delete();
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => ['idDeleted' => $idDeleted],
            ], 200);
        }
    }
}
