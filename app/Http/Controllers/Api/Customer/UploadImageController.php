<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
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

        $imageUrl = UploadImageService::uploadImage($request->image->getRealPath());

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $imageUrl
        ], 201);

    }

}
