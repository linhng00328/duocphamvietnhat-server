<?php

namespace App\Services;

use App\Models\ImgurVideo;
use App\Models\MsgCode;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as GuzzleClient;

class UploadVideoService
{
    const END_POINT = 'https://cdndpnhatban.ikitech.vn/api/video-upload';

    public static function uploadVideo($videoPath)
    {


        if ($videoPath == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[0],
                'msg' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[1],
            ], 400);
        }

        $client = new GuzzleClient();

        try {
            $response = $client->request(
                'POST',
                UploadVideoService::END_POINT,
                [
                    'multipart' => [
                        [
                            'name'     => 'video',
                            'contents' => @file_get_contents($videoPath),
                            'Content-type' => 'multipart/form-data',
                            'filename' => 'dsadsad.png',
                        ],
                    ],
                ]

            );

            if ($response->getStatusCode() != 200) {
                return MsgCode::CANNOT_POST_VIDEOS;
            }


            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);


            return $jsonResponse->link;
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            return MsgCode::CANNOT_POST_VIDEOS;
        }
    }
}
