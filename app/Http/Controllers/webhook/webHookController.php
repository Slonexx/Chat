<?php

namespace App\Http\Controllers\webhook;

use App\Clients\MoySklad;
use App\Clients\oldMoySklad;
use App\Http\Controllers\Controller;
use App\Jobs\HandleWebhookAgent;
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
        try {
            if ($request->all() == []) return response()->json();

            $requestData = json_decode(json_encode($request->data));

            if (empty($requestData)){
                return response()->json((object)[
                    "message" => "отсутствует поле data"
                ]);
            }

            if (!is_array($requestData)){
                return response()->json((object)[
                    "message" => "поле data не является массивом"
                ]);
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

            $preparedMessages = [];
            foreach($requestData as $itemData){
                $itemMessage = new stdClass();
                $itemMessage->type = $itemData->type;
                $itemMessage->text = $itemData->message->text;
                $itemMessage->fromMe = $itemData->fromMe;

                $userInfo = $itemData->chat;

                $itemMessage->chat = new stdClass();
                $itemMessage->chat->phone = $userInfo->phone;
                $itemMessage->chat->username = $userInfo->username;
                $itemMessage->chat->name = $userInfo->name;
                $itemMessage->chat->id = $userInfo->id;
                $itemMessage->chat->email = $userInfo->email;
                $preparedMessages[] = $itemMessage;
            }
            $params = [
                "headers" => [
                    'Content-Type' => 'application/json'
                ],
                "json" => $preparedMessages
            ];
            $appUrl = Config::get("Global.url", null);
            if (!is_string($appUrl) || $appUrl == null)
                throw new Error("url отсутствует или имеет некорректный формат");
            $preppedUrl = $appUrl . "api/counterparty/notes/create/$accountId/line/$lineId/messenger/$messenger";
            HandleWebhookAgent::dispatch($params, $preppedUrl)->onConnection("webhook_agent")->onQueue("high");
            return response()->json((object)["status" => true]);
        } catch (Exception | Error $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    public function createCounterpartyNotes(Request $request, $accountId, $lineId, $messenger){
        $requestData = json_decode(json_encode($request->all()));
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
                $message->text = $itemData->text;
                $message->fromMe = $itemData->fromMe;

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

                    $messageStack[$usernameOrPhone] = [];
                    $messageStack[$usernameOrPhone][] = $agent->message;

                    if (empty($org)) {
                        $messageStack[$usernameOrPhone][] = "отсутствуют организации у контрагента $usernameOrPhone";
                        continue;
                    }
                    if(empty($firstNote)) {
                        $messageStack[$usernameOrPhone][] = "отсутствуют настройки заметок у контрагента $usernameOrPhone";
                        continue;
                    }
                    if ($itemData->type != "text"){
                        $messageStack[$usernameOrPhone][] = "Сообщение '$message->text' не является типом 'text'";
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

                    $messageStack[$usernameOrPhone][] = "Сообщение '$message->text' создано";

                } catch(Exception $e){
                    $messageStack[] = $e->getMessage();
                    continue;
                }


            }

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
