<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\BannerAd;
use App\Models\BannerAdApp;
use App\Models\MsgCode;
use Illuminate\Http\Request;

/**
 * @group  User/Banner quảng cáo
 */
class BannerAdController extends Controller
{
    /**
     * Danh sách banner quảng cáo web
     * @urlParam  store_code required Store code
     */
    public function getAll(Request $request)
    {

        $banners = BannerAd::where('store_id', $request->store->id)->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $banners,
        ], 200);
    }

    /**
     * Tạo mục 1 banner web
     * @urlParam  store_code required Store code
     * @bodyParam title string required Tiêu đề quảng cáo
     * @bodyParam image_url required link ảnh
     * @bodyParam type required ( 0 dưới banner,  1 trên sp nổi bật, 2 trên sp mới, 3 trên sản phẩm khuyến mãi, 4 trên danh sách tin tức, 5 trên footer, 6 dưới danh mục sản phẩm, 7 dưới danh mục tin tức, 8 trên header )
     */
    public function create(Request $request)
    {

        $checkBannerAdExists = BannerAd::where(
            'title',
            $request->title
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkBannerAdExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[0],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }

        $BannerAdCreate = BannerAd::create(
            [
                'image_url' => $request->image_url,
                'link_to' =>  $request->link_to,
                'title' => $request->title,
                'store_id' => $request->store->id,
                'type' => $request->type
            ]
        );
        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $BannerAdCreate
        ], 201);
    }


    /**
     * update một BannerAd web
     * @urlParam  store_code required Store code cần update
     * @urlParam  banner_ad_id required BannerAd_id cần update
     * @bodyParam title string required Tên danh mục
     * @bodyParam image_url file required Ảnh (hoặc truyền lên image_url)
     */
    public function updateOneBannerAd(Request $request)
    {

        $imageUrl = $request->image_url;

        $id = $request->route()->parameter('banner_ad_id');
        $checkBannerAdExists = BannerAd::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkBannerAdExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_BANNER_EXISTS[0],
                'msg' => MsgCode::NO_BANNER_EXISTS[1],
            ], 404);
        } else {
            $checkBannerAdExists2 = BannerAd::where(
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
            if ($checkBannerAdExists2 != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_BANNER_EXISTS[0],
                    'msg' => MsgCode::NO_BANNER_EXISTS[1],
                ], 400);
            }

            $checkBannerAdExists->update(Helper::sahaRemoveItemArrayIfNullValue([
                'image_url' => $imageUrl,
                'title' => $request->title,
                'type' => $request->type,
                'link_to' =>  $request->link_to,
            ]));

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => BannerAd::where('id', $id)->first(),
            ], 200);
        }
    }

    /**
     * xóa một 1 banner web
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID BannerAd cần xóa thông tin.
     */
    public function deleteOneBannerAd(Request $request, $id)
    {

        $id = $request->route()->parameter('banner_ad_id');
        $checkBannerAdExists = BannerAd::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkBannerAdExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_BANNER_EXISTS[0],
                'msg' => MsgCode::NO_BANNER_EXISTS[1],
            ], 404);
        } else {
            $idDeleted = $checkBannerAdExists->id;
            $checkBannerAdExists->delete();
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
     * Danh sách banner quảng cáo app
     * @urlParam  store_code required Store code
     */
    public function getAll_app(Request $request)
    {

        $banners = BannerAdApp::where('store_id', $request->store->id)->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $banners,
        ], 200);
    }

    /**
     * Tạo mục 1 banner app
     * @urlParam  store_code required Store code
     * @bodyParam title string required Tiêu đề quảng cáo
     * @bodyParam image_url required link ảnh
     * @bodyParam  type_action string gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE,BONUS_PRODUCT,COMBO
     * @bodyParam  value string giá trị thực thi
     * @bodyParam position required ( 0 dưới banner,  1 trên sp nổi bật, 2 trên sp mới, 3 trên sản phẩm khuyến mãi, 4 trên danh sách tin tức, 5 trên footer, 6 dưới danh mục sản phẩm, 7 dưới danh mục tin tức, 8 trên header )
     * @bodyParam có show hay không
     */
    public function create_app(Request $request)
    {

        $checkBannerAdExists = BannerAdApp::where(
            'store_id',
            $request->store->id
        )->first();

        $BannerAdCreate = BannerAdApp::create(
            [
                'image_url' => $request->image_url,
                'position' =>  $request->position,
                'title' => $request->title,
                'store_id' => $request->store->id,
                'type_action' => $request->type_action,
                'is_show' => $request->is_show,
                'value' => $request->value,
            ]
        );
        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $BannerAdCreate
        ], 201);
    }


    /**
     * update một BannerAd app
     * @urlParam  store_code required Store code
     * @bodyParam title string required Tiêu đề quảng cáo
     * @bodyParam image_url required link ảnh
     * @bodyParam  type_action string gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE,BONUS_PRODUCT,COMBO
     * @bodyParam  value string giá trị thực thi
     * @bodyParam position required ( 0 dưới banner,  1 trên sp nổi bật, 2 trên sp mới, 3 trên sản phẩm khuyến mãi, 4 trên danh sách tin tức, 5 trên footer, 6 dưới danh mục sản phẩm, 7 dưới danh mục tin tức, 8 trên header )
     * @bodyParam có show hay không
     */
    public function updateOneBannerAd_app(Request $request)
    {

        $imageUrl = $request->image_url;

        $id = $request->route()->parameter('banner_ad_id');
        $checkBannerAdExists = BannerAdApp::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkBannerAdExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_BANNER_EXISTS[0],
                'msg' => MsgCode::NO_BANNER_EXISTS[1],
            ], 404);
        } else {


            $checkBannerAdExists->update(Helper::sahaRemoveItemArrayIfNullValue([
                'image_url' => $request->image_url,
                'position' =>  $request->position,
                'title' => $request->title,
                'store_id' => $request->store->id,
                'type_action' => $request->type_action,
                'is_show' => $request->is_show,
                'value' => $request->value,
            ]));

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => BannerAdApp::where('id', $id)->first(),
            ], 200);
        }
    }

    /**
     * xóa một 1 banner app
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID BannerAd cần xóa thông tin.
     */
    public function deleteOneBannerAd_app(Request $request, $id)
    {

        $id = $request->route()->parameter('banner_ad_id');
        $checkBannerAdExists = BannerAdApp::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($checkBannerAdExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_BANNER_EXISTS[0],
                'msg' => MsgCode::NO_BANNER_EXISTS[1],
            ], 404);
        } else {
            $idDeleted = $checkBannerAdExists->id;
            $checkBannerAdExists->delete();
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
