<?php

namespace App\Helper;

use App\Jobs\WebhookJob;
use App\Models\HistorySendWebhook;
use GuzzleHttp\Client as GuzzleClient;
use App\Models\PublicApiSession;
use Exception;

class SendToWebHookUtils
{

    const NEW_ORDER = "NEW_ORDER";
    const DELETE_ORDER = "DELETE_ORDER";
    const UPDATE_ORDER = "UPDATE_ORDER";

    const NEW_PRODUCT = "NEW_PRODUCT";
    const DELETE_PRODUCT = "DELETE_PRODUCT";
    const UPDATE_PRODUCT = "UPDATE_PRODUCT";

    const UPDATE_CUSTOMER = "UPDATE_CUSTOMER";
    const NEW_CUSTOMER = "NEW_CUSTOMER";
    const DELETE_CUSTOMER = "DELETE_CUSTOMER";

    static function sendToWebHook($request, $type,  $data)
    {

        WebhookJob::dispatch(
            $request->store->id,
            $type,
            $data
        );
    }
}
