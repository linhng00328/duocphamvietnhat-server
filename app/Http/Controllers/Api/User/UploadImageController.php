<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\ImageGallery;
use App\Models\ImgurImage;
use App\Models\MsgCode;
use App\Services\UploadImageService;
use Illuminate\Http\Request;

/**
 * @group  Upload ảnh
 */
class UploadImageController extends Controller
{


    /**
     * Upload 1 ảnh
     * @bodyParam image file required File ảnh
     */
    public function upload(Request $request)
    {
        if (empty($request->image)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[0],
                'msg' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[1],
            ], 400);
        }

        // if(!exif_imagetype($request->image)) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::INVALID_PHOTO[0],
        //         'msg' => MsgCode::INVALID_PHOTO[1],
        //     ], 400);
        // }

        $imageUrl = UploadImageService::uploadImage($request->image->getRealPath(), $request->image_type, $request->image->getClientMimeType());

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $imageUrl
        ], 201);
    }

    /**
     * Upload 1 ảnh
     * @bodyParam image file required File ảnh
     */
    public function uploadv2(Request $request)
    {

        if (empty($request->image)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[0],
                'msg' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[1],
            ], 400);
        }


        $md5 = md5_file($request->image);
        $has_file = ImageGallery::where('md5', $md5)->first();

        $data = getimagesize($request->image);
        $width = $data[0];
        $height = $data[1];
        $weight =  $request->image->getSize();
        $filename = $request->image->getClientOriginalName();


        if ($has_file != null) {
            $created = ImageGallery::create([
                'store_id' => $request->store->id,
                'width' =>  $width,
                'filename' =>  $filename,
                'weight' =>  $weight,
                'height' =>  $height,
                'image_url' =>  $has_file->image_url,
                'md5' => $md5,
            ]);
        } else {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath(), $request->image_type, $request->image->getClientMimeType());

            $created = ImageGallery::create([
                'store_id' => $request->store->id,
                'width' =>  $width,
                'filename' =>  $filename,
                'weight' =>  $weight,
                'height' =>  $height,
                'image_url' =>  $imageUrl,
                'md5' => $md5,
            ]);
        }



        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $created
        ], 201);
    }


    /**
     * Cập nhật 1 ảnh
     * @bodyParam remi_name string required Tên ảnh gợi ý
     */
    public function update(Request $request)
    {

        $imageExists = ImageGallery::where('store_id', $request->store->id)->where('id',  $request->image_id)->first();

        if (empty($imageExists)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[0],
                'msg' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[1],
            ], 400);
        }

        $imageExists->update([
            'remi_name' => $request->remi_name
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $imageExists
        ], 200);
    }


    /**
     * Danh sách ảnh
     */
    public function getAll(Request $request)
    {

        $search = request('search');

        $data = ImageGallery::where(
            'store_id',
            $request->store->id
        )->when(!empty($search), function ($query) use ($search, $request) {
            $query->search($search);
        })->paginate(request('limit') == null ? 20 : request('limit'));

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data
        ], 201);
    }



    /**
     * Thông tin 1 ảnh
     */
    public function getOne(Request $request)
    {

        $imageExists = ImageGallery::where('store_id', $request->store->id)->where('id',  $request->image_id)->first();

        if (empty($imageExists)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[0],
                'msg' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[1],
            ], 400);
        }

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $imageExists
        ], 201);
    }


    /**
     * Xoa 1 ảnh
     */
    public function delete(Request $request)
    {

        $imageExists = ImageGallery::where('store_id', $request->store->id)->where('id',  $request->image_id)->first();

        if (empty($imageExists)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[0],
                'msg' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[1],
            ], 400);
        }

        $imageExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
