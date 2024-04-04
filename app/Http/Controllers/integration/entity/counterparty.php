<?php

namespace App\Http\Controllers\integration\entity;

use App\Clients\MsClient;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class counterparty extends Controller
{
    public MsClient $msClient;
    public function creatingAgent(Request $request){

        $setAttrS = new SetAttributesService($accountId);
        $resSetAttr = $setAttrS->setTypeSendingAndDocumentId($msEntityType, $msEntityId, $documentId, $typeDocument, $service);

        $settingModel = json_decode(json_encode($request->settingModel));



        $this->msClient = new MsClient($settingModel->accountId);

        $body = [

        ];

    }


    public function metadataStates(Request $request){
        $settingModel = json_decode(json_encode($request->settingModel));
        $this->msClient = new MsClient($settingModel->accountId);

        try {
            $json = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/counterparty/metadata");
        } catch (BadResponseException $e) {
            if ($e->getCode() == 401) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'error' => 'Unauthorized',
                        'message' => 'Токен приложение в МоемСкладе мертвый, сообщить разработчиком приложения.'
                    ]
                ], 401);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'error' => 'error to ms',
                        'message' => $e->getResponse()->getBody()->getContents()
                    ]
                ], 500);
            }
        }

        $res = [];

        if ($json->states == []){
            response()->json([
                'success' => true,
                'data' => [

                ]
            ], 401);
        }


    }

}
