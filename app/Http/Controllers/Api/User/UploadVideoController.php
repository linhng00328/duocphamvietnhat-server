<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\VideoGallery;
use App\Models\ImgurVideo;
use App\Models\MsgCode;
use App\Services\UploadVideoService;
use Illuminate\Http\Request;

/**
 * @group  Upload video
 */
class UploadVideoController extends Controller
{

    /**
     * Upload 1 video
     * @bodyParam video file required File video
     */
    public function upload(Request $request)
    {

        if (empty($request->video)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_VIDEO[0],
                'msg' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_VIDEO[1],
            ], 400);
        }

        $videoUrl = UploadVideoService::uploadVideo($request->video->getRealPath());

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $videoUrl
        ], 201);
    }
}
