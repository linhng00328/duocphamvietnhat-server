<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\ImgurImage;
use App\Models\MsgCode;
use App\Services\UploadImageService;
use Illuminate\Http\Request;

class ImgurImageController extends Controller
{

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

        $imageUrl = UploadImageService::uploadImage($request->image->getRealPath());

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $imageUrl
        ], 201);

    }


    public function create(Request $request)
    {
        $imgurImageCreate = ImgurImage::create(
            [
                'link' => $request->link,
            ]
        );
        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $imgurImageCreate
        ], 201);


    }


    public function getAll(Request $request)
    {

        $imgurImages = ImgurImage::paginate(50);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $imgurImages,
        ], 200);
    }
}
