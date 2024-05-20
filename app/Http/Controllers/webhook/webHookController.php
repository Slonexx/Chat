<?php

namespace App\Http\Controllers\webhook;


use App\Http\Controllers\Controller;
use App\Models\MessengerAttributes;
use App\Services\HandlerService;
use Error;
use Exception;
use Illuminate\Http\Request;

class webHookController extends Controller
{
    public function callbackUrls(Request $request, $accountId, $lineId, $messengers)
    {
        if ($request->all() == []) return response()->json();




      /*  $chat = $request->chat ?? [];
        if ($chat == []) return response()->json();
        try {
            $phone = $chat->phone;
            $username = $chat->username;
            $name = $chat->name;
            $chatId = $chat->id;
            $email = $chat->email;
            $phoneForCreating = "+{$phone}";
        } catch (Exception|Error $e) {

        }
        $handlerS = new HandlerService();
        $attribute = MessengerAttributes::getFirst($accountId, "counterparty", $messenger);
        $attribute_id = $attribute->attribute_id;
        $attrMeta = $handlerS->FormationMetaById("agentMetadataAttributes", "attributemetadata", $attribute_id);*/

    }
}
