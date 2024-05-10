<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class vendorEndpoint extends Controller
{
    public function put(Request $request, $apps, $accountId){

        $data = json_decode(json_encode($request->all()));
        $app = Lib::load($apps, $accountId);

        if (property_exists($data, 'access')) $accessToken = $data->access[0]->access_token;
        else $accessToken = $app->TokenMoySklad;

        if (!$app->getStatusName()) {
            $app->TokenMoySklad = $accessToken;
            $app->status = Lib::SETTINGS_REQUIRED;
            $app->persist();

        }


        if (!$app->getStatusName()) {
            http_response_code(404);
        } else {
            return Response::json([
                'status' => $app->getStatusName()
            ]);
        }
    }

    public function delete(Request $request, $apps, $accountId){

        $data = json_decode(json_encode($request->all()));
        $app = Lib::load($apps, $accountId);

        if (file_exists(public_path().'/data/'.$accountId.'.json')) {
            unlink( public_path().'/data/'.$accountId.'.json');
        }



        if (!$app->getStatusName()) {
            http_response_code(404);
        }
    }

}
