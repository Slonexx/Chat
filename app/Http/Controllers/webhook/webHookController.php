<?php

namespace App\Http\Controllers\webhook;

use App\Clients\MoySklad;
use App\Clients\oldMoySklad;
use App\Http\Controllers\Controller;
use App\Models\MessengerAttributes;
use App\Models\Notes;
use App\Models\organizationModel;
use App\Services\ChatApp\MessageService;
use App\Services\HandlerService;
use App\Services\MoySklad\Attributes\oldCounterpartyS;
use App\Services\MoySklad\ControllerServices\webHookAgentLogicService;
use App\Services\MoySklad\Entities\CounterpartyNotesService;
use App\Services\Settings\MessengerAttributes\CreatingAttributeService;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use stdClass;

class webHookController extends Controller
{
    public function callbackUrls(Request $request, $accountId, $lineId, $messenger)
    {
        if ($request->all() == []) return response()->json();

        $requestData = json_decode(json_encode($request->data));

        if (empty($requestData)){
            return response()->json((object)[
                "message" => "отсутствует поле data"
            ],400);
        }

        if (!is_array($requestData)){
            return response()->json((object)[
                "message" => "поле data не является массивом"
            ],400);
        }
        $hasMessage = false;
        foreach($requestData as $itemData){
            if ($itemData->type == "text")
                $hasMessage = true;
        }
        if(!$hasMessage){
            return response()->json((object)[
                "message" => "Прошлись по всей data. Ни в одной из них type webhook'a != text"
            ]);
        }

        $messageStack = [];
        try {
            $org = organizationModel::getByAccountIdAndLine($accountId, $lineId);
            $notes = Notes::getByAccountId($accountId);

            $handlerS = new HandlerService();
            $msC = new oldMoySklad($accountId);
            $setAttrS = new CreatingAttributeService($accountId, $msC);
            //все добавленные в messengerAttributes будут созданы в мс
            $mesAttr = Config::get("messengerAttributes");
            $attrNames = array_keys($mesAttr);
            $agentAttrS = new oldCounterpartyS($accountId, $msC);
            $resAttr = $setAttrS->createAttribute("messengerAttributes", "counterparty", $attrNames, $agentAttrS);
            if (!$resAttr->status)
                return $handlerS->responseHandler($resAttr);
            else
                $messageStack[] = $resAttr->message;

            $msCnew = new MoySklad($accountId);

            $compliances = [
                "grWhatsApp" => "whatsapp",
                "telegram" => "telegram",
                "email" => "email",
                "vkontakte" => "vk",
                "instagram" => "instagram",
                "telegramBot" => "telegram_bot",
                "avito" => "avito"
            ];
            $attribute = MessengerAttributes::getFirst($accountId, "counterparty", $compliances[$messenger]);
            $attribute_id = $attribute->attribute_id;

            $msCnew = new MoySklad($accountId);
            $firstNote = $notes->first();
            foreach($requestData as $itemData){
                
                $userInfo = $itemData->chat;
                $message = new stdClass();
                $message->text = $itemData->message->text;
                $message->fromMe = $itemData->fromMe;
                $message->time = $itemData->time;

                $agentLogicS = new webHookAgentLogicService($accountId, $msCnew);

                try{
                    $agent = $agentLogicS->createOrUpdate($userInfo, $compliances[$messenger], $attribute_id);

                    $atUsername = "@{$userInfo->username}";
                    $email = $userInfo->email;
                    $chatId = $userInfo->id;

                    $usernameOrPhone = match ($compliances[$messenger]) {
                        "telegram" => $atUsername,
                        "whatsapp" => $chatId,
                        "email" => $email,
                        "vk" => ctype_digit($chatId) ? "id{$chatId}" : $chatId,
                        "instagram" => $atUsername,
                        "telegram_bot" => $atUsername,
                        "avito" => $chatId
                    };

                    $messageStack[] = "контрагент $usernameOrPhone создан";

                    if (empty($org)) {
                        $messageStack[] = "отсутствуют организации у контрагента $usernameOrPhone";
                        continue;
                    }
                    if(empty($firstNote)) {
                        $messageStack[] = "отсутствуют настройки заметок у контрагента $usernameOrPhone";
                        continue;
                    }
                    if ($itemData->type != "text"){
                        $messageId = $itemData->id;
                        $messageStack[] = "Сообщение $messageId не является типом 'text'";
                        continue;
                    }
                    

                    $lineName = $org->lineName;

                    $isAddMessengerInfo = $firstNote->is_messenger;
                    $messageS = new MessageService();
                    $preparedMessage = $messageS->prepareMessage($lineName, $lineId, $messenger, $usernameOrPhone, $isAddMessengerInfo, $message);

                    $agentId = $agent->data->id;
                    $agentNotesS = new CounterpartyNotesService($accountId, $msCnew);
                    $body = (object)[
                        "description" => $preparedMessage
                    ];
                    $agentNotesS->create($agentId, $body);

                    $messageStack[] = "Сообщение '$message->text' создано у контрагента $usernameOrPhone";

                } catch(Exception $e){
                    $messageStack[] = $e->getMessage();
                    continue;
                }


            }
            // Notes::where('accountId', $accountId)
            //     ->update(['last_start' => $date]);

            return response()->json($messageStack);

        } catch (Exception|Error $e) {
            $current = $e;
            $messages = [];
            $statusCode = 500;//or HTTP Exception code

            while ($current !== null) {
                $filePath = $current->getFile();
                $fileLine = $current->getLine();
                $message = $current->getMessage();

                $nextError = $current->getPrevious();

                $parts = explode('|', $message);

                if (count($parts) === 2) {
                    $text = $parts[0];
                    $json_str = array_pop($parts);

                    $value = [
                        "message" => $text,
                        "data" => json_decode($json_str)
                    ];
                    if ($nextError === null) {
                        $messageStack["message"] = $text;
                        $code = $current->getCode();
                        if ($code >= 400)
                            $statusCode = $code;
                    }
                } else {
                    $value = [
                        "message" => $message
                    ];
                    if ($nextError === null) {
                        $messageStack["message"] = $message;
                        $code = $current->getCode();
                        if ($code >= 400)
                            $statusCode = $code;
                    }
                }


                $fileName = basename($filePath);

                $key = "{$fileName}:{$fileLine}";

                $messages[] = [
                    $key => $value
                ];
                $current = $current->getPrevious();
            }
            $messageStack["error"] = $messages;
            return response()->json($messageStack, $statusCode);
        }

    }
}
