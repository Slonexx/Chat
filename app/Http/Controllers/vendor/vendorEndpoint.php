<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class vendorEndpoint extends Controller
{
    public function put(Request $request, $apps, $accountId){

        $data = json_decode(json_encode($request->all()));
        $app = Lib::load($apps, $accountId);

        $accessToken = $data->access[0]->access_token;

        if (!$app->getStatusName()) {
            $app->TokenMoySklad = $accessToken;
            $app->status = Lib::SETTINGS_REQUIRED;
            $app->persist();

        }


        if (!$app->getStatusName()) {
            http_response_code(404);
        } else {
            header("Content-Type: application/json");
            echo '{"status": "' . $app->getStatusName() . '"}';
        }
    }

    public function delete(Request $request, $apps, $accountId){

        $data = json_decode(json_encode($request->all()));
        $app = Lib::load($apps, $accountId);

        try {
            unlink( public_path().'/data/'.$accountId.'.json');
        } catch (BadResponseException) {

        }

        if (!$app->getStatusName()) {
            http_response_code(404);
        }
    }

}
