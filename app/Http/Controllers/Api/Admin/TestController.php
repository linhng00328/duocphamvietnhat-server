<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Zalo\Zalo;

/**
 * @group  Admin/Api test cac thu
 */
class TestController extends Controller
{

    /**
     * Test
     */
    public function test(Request $request)
    {
        function base64url_encode($plainText)
        {
            $base64 = base64_encode($plainText);
            $base64 = trim($base64, "=");
            $base64url = strtr($base64, "+/", "-_");
            return $base64url;
        }

        $random = bin2hex(openssl_random_pseudo_bytes(32));

        //  $random = "49c1f8eaa06af9657edbdf0fcbec83b89b60210546ae3eb57edb8da812fce900";
        $code_verifier = base64url_encode(pack("H*", $random));
        $code_challenge = base64url_encode(pack("H*", hash("sha256", $code_verifier)));

        // WvCmePUWMoQWWrsbZvTEBe6pLgtchba8nhKkhPgE312yYNEZjwPrBQgWOOoUlNmAzw1xgTMvE2VaWYAVnuDRAyEeNwNclN8kxv5Rav6yQJUteWchlwGC78McTksNibiNx-ueq8MYBbJrl33fgV8TBlknMTE7l38UszDdnUpLRN7AjnVWpBaOHOAdOxI9kMXxxVzFceY17MRsqoZumyalUeBHCSVOjYiagAqWnjNm3cAWZbs-s85dLhRU8OgDtoukhF1Eu971HJtaaM_BlofjVAai3zQV0mPZm009jjilSpZk6LYosYnJEkW_GuhyE7lPcJLKQCU5tVp2gfDI_hpfdBsfvppTbuFLavVCLDoIgipIxTGEu8U-yldaooKc1-Br4Bkg6oy&state=STATE&code_challenge=t7khpOJ3vKoaJ7932vQLsDEF8Z41mU7Iz0xbTrHA9yk

        $config = array(
            'app_id' => '4442897439069436538',
            'app_secret' => 'XHWPpI7ZVdLisYfubRUo'
        );
        $zalo = new Zalo($config);

        $helper = $zalo->getRedirectLoginHelper();
        $callbackUrl = "https://ikitech.vn/";
        $codeChallenge = $code_challenge;
        $state = "STATE";
        $loginUrl = $helper->getLoginUrl($callbackUrl, $codeChallenge, $state); // This is login url




    }
}
