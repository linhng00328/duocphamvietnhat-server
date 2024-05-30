<?php

namespace App\Services;

use App\Models\ImgurImage;
use App\Models\MsgCode;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as GuzzleClient;

class UploadImageService
{
    const END_POINT = 'https://cdndpnhatban.ikitech.vn/api/image-upload';

    public static function uploadImage($imagePath, $imageType = "", $imageExtension)
    {

        $mimeTypeParts = explode('/', $imageExtension);
        $extension = end($mimeTypeParts);


        if ($imagePath == null) {
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
                UploadImageService::END_POINT,
                [
                    'multipart' => [
                        [
                            'name'     => 'image',
                            'contents' => @file_get_contents($imagePath),
                            'Content-type' => 'multipart/form-data',
                            'filename' => 'dsadsad.' . $extension,
                        ],
                        [
                            'name'     => 'type',
                            'contents' =>  "iki"
                        ],
                        [
                            'name'     => 'image_type',
                            'contents' =>  $imageType
                        ],
                    ],
                ]

            );

            if ($response->getStatusCode() != 200) {
                return MsgCode::CANNOT_POST_PICTURES;
            }


            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);


            return $jsonResponse->link;
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            return MsgCode::CANNOT_POST_PICTURES;
        }
    }
}
