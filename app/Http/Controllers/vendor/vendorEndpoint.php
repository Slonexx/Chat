<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class vendorEndpoint extends Controller
{
    public function Put(Request $request, $apps, $accountId){

        $data = json_decode(json_encode($request->all()));
        $app = Lib::load($apps, $accountId);

        $appUid = $data->appUid;
        $accessToken = $data->access[0]->access_token;

        if (!$app->getStatusName()) {
            $app->TokenMoySklad = $accessToken;
            $app->status = Lib::SETTINGS_REQUIRED;
            $app->persist();

        }

        return response('',200);
    }

}
