<?php

namespace App\Http\Controllers\webhook;

use App\Clients\MoySkladIntgr;
use App\Exceptions\AgentFindLogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerorderIntgr;
use App\Http\Requests\WebhookAgentIntgr;
use App\Jobs\HandleCustomerorder;
use App\Jobs\HandleWebhookAgent;
use App\Services\Intgr\ChatService;
use App\Services\HandlerService;
use App\Services\Intgr\AgentFindLogicService;
use App\Services\Intgr\AgentMessengerHandler;
use App\Services\Intgr\MessageService;
use App\Services\Intgr\ControllerServices\webHookAgentLogicService;
use App\Services\Intgr\CustomerorderCreateLogicService;
use App\Services\Intgr\Entities\CounterpartyNotesService;
use App\Services\Intgr\ControllerServices\AgentControllerLogicService;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Telescope\Telescope;
use stdClass;

class webHookController extends Controller
{
    public function callbackUrls(Request $request, $accountId, $lineId, $messenger)
    {
        try {
            Telescope::tag(function () use ($accountId) {
                return [
                    "accountId: $accountId",
                    "webhook_message",
                ];
            });
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

            $preparedChats = [];
            $preparedMessages = [];
            foreach($requestData as $itemData){
                $itemMessage = new stdClass();
                $itemMessage->type = $itemData->type;
                $itemMessage->text = $itemData->message->text;
                $itemMessage->fromMe = $itemData->fromMe;

                $userInfo = $itemData->chat;

                $chatItem = new stdClass();

                $chatItem->phone = $userInfo->phone;
                $chatItem->username = $userInfo->username;
                $chatItem->name = $userInfo->name;
                $chatItem->id = $userInfo->id;
                $chatItem->email = $userInfo->email;
                $chatItem->unreadMessages = $userInfo->unreadMessages;

                $itemMessage->chat = $chatItem;
                $preparedMessages[] = $itemMessage;

                $preparedChats[] = $chatItem;
            }

            //send to agent
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
            $connection = "webhook_agent";
            HandleWebhookAgent::dispatch($params, $preppedUrl, $connection)->onConnection($connection)->onQueue("high");
            //send to customerorder
            $params["json"] = $preparedChats;
            $preppedUrl = $appUrl . "api/customerorder/create/$accountId/line/$lineId/messenger/$messenger";
            $connection = "customerorder";
            HandleCustomerorder::dispatch($params, $preppedUrl, $connection)->onConnection($connection)->onQueue("high");

            return response()->json((object)["status" => true]);
        } catch (Exception | Error $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    public function callbackUrlsIntrg(Request $request)
    {
        Telescope::tag(function ()  {
            return [ 
                "webhookForCounterparty",
                "integration"
            ];
        });
        try {
            $requestData = $request->all();
            $request = json_decode(json_encode($requestData));
            $compliances = [
                "grWhatsApp" => "whatsapp",
                "telegram" => "telegram",
                "email" => "email",
                "vkontakte" => "vk",
                "instagram" => "instagram",
                "telegramBot" => "telegram_bot",
                "avito" => "avito"
            ];

            $preparedBody = new stdClass();
            $preparedBody->settings = new stdClass();
            $preparedBody->webhook = new stdClass();
            $preparedBody->webhook->meta = new stdClass();

            $reqSets = $request->settings;
            $webhookMeta = $request->webhook->meta;
            $webhookData = $request->webhook->data;

            $messenger_attribute_id = $reqSets->messenger_attribute_id;

            $preparedBody->settings->lineName = $reqSets->lineName;
            $preparedBody->settings->is_messenger = $reqSets->is_messenger;
            $preparedBody->settings->ms_token = $reqSets->ms_token;

            $messenger = $webhookMeta->messenger;

            $preparedBody->webhook->meta->messenger = $messenger;

            $findedMessengerAttribute = array_filter($messenger_attribute_id, fn($value) => $value->name ==  $compliances[$messenger]);

            if(empty($findedMessengerAttribute))
                throw new Exception("по данному мессенджеру не найдено attribute_id");

            $firstAttr = array_shift($findedMessengerAttribute);
            $preparedBody->settings->messenger_attribute_id = $firstAttr->attribute_id;

            $preparedMessages = [];
            foreach($webhookData as $itemData){
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
            $preparedBody->webhook->data = $preparedMessages;

            $params = [
                "headers" => [
                    'Content-Type' => 'application/json'
                ],
                "json" => $preparedBody
            ];
            $appUrl = Config::get("Global.url", null);
            if (!is_string($appUrl) || $appUrl == null)
                throw new Error("url отсутствует или имеет некорректный формат");
            $preppedUrl = $appUrl . "api/integration/counterparty/notes/create";
            $connection = "webhook_agent_intgr";
            HandleWebhookAgent::dispatch($params, $preppedUrl, $connection)->onConnection($connection)->onQueue("high");
            return response()->json((object)["status" => true]);
        } catch(Exception $e){
            return response()->json($e->getMessage(), 400);
        } catch (Error $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    public function createCounterpartyNotesIntgr(WebhookAgentIntgr $request){
        Telescope::tag(function () {
            return [
                "typeEntity: counterparty",
                "fromWebhook",
                "integration"
            ];
        });
        $messageStack = [];
        $compliances = [
            "grWhatsApp" => "whatsapp",
            "telegram" => "telegram",
            "email" => "email",
            "vkontakte" => "vk",
            "instagram" => "instagram",
            "telegramBot" => "telegram_bot",
            "avito" => "avito"
        ];
        try {
            $request->validated();
            $requestData = $request->all();
            $request = json_decode(json_encode($requestData));
            $attribute_id = $request->settings->messenger_attribute_id;
            $lineName = $request->settings->lineName;
            $isAddMessengerInfo = $request->settings->is_messenger;
            $ms_token = $request->settings->ms_token;

            $messenger = $request->webhook->meta->messenger;

            $messages = $request->webhook->data;
            //проверить наличие доп поля в мс по переданному токену, если нет выдать ошибку


            $msIntgrClient = new MoySkladIntgr($ms_token);
            foreach($messages as $itemData){
                $userInfo = $itemData->chat;
                $message = new stdClass();
                $message->text = $itemData->text;
                $message->fromMe = $itemData->fromMe;

                $agentLogicS = new webHookAgentLogicService($msIntgrClient);

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

                    if ($itemData->type != "text"){
                        $messageStack[$usernameOrPhone][] = "Сообщение '$message->text' не является типом 'text'";
                        continue;
                    }

                    $messageS = new MessageService();
                    $preparedMessage = $messageS->prepareMessage($lineName, $messenger, $usernameOrPhone, $isAddMessengerInfo, $message);

                    $agentId = $agent->data->id;
                    $agentNotesS = new CounterpartyNotesService($msIntgrClient);
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

    public function callbackUrlsCustomerorderIntrg(Request $request)
    {
        Telescope::tag(function ()  {
            return [ 
                "webhookForCustomerorder",
                "integration"
            ];
        });
        try {
            $requestData = $request->all();
            $request = json_decode(json_encode($requestData));

            $preparedBody = new stdClass();
            $preparedBody->ms_token = $request->ms_token;
            $preparedBody->org = [];

            $org = $request->setting->org;
            $lid = $request->setting->lid;
            $messengerAttribute = $request->setting->messenger_attribute;

            foreach($org as $orgItem){
                $item = new stdClass();
                $item->accessToken = $orgItem->employeeModel->accessToken;
                $item->lineId = $orgItem->lineId;
                $item->lineName = $orgItem->lineName;
                $preparedBody->org[] = $item;
            }

            $preparedLid = new stdClass();

            $preparedLid->responsible = $lid->responsible;
            $preparedLid->responsible_uuid = $lid->responsible_uuid;
            $preparedLid->is_activity_order = $lid->is_activity_order;
            $preparedLid->organization = $lid->organization;
            $preparedLid->organization_account = $lid->organization_account;
            $preparedLid->sales_channel_uid = $lid->sales_channel_uid;
            $preparedLid->project_uid = $lid->project_uid;
            $preparedLid->states = $lid->states;
            $preparedLid->tasks = $lid->tasks;

            $preparedBody->lid = $preparedLid;

            foreach($messengerAttribute as $attrItem){
                $item = new stdClass();
                $item->name = $attrItem->name;
                $item->attribute_id = $attrItem->attribute_id;
                $preparedBody->messengerAttributes[] = $item;
            }
            $params = [
                "headers" => [
                    'Content-Type' => 'application/json'
                ],
                "json" => $preparedBody
            ];
            $appUrl = Config::get("Global.url", null);
            if (!is_string($appUrl) || $appUrl == null)
                throw new Error("url отсутствует или имеет некорректный формат");
            $preppedUrl = $appUrl . "api/integration/customerorder/create";
            $connection = "customerorder_intgr";
            HandleCustomerorder::dispatch($params, $preppedUrl, $connection)->onConnection($connection)->onQueue("high");
            return response()->json((object)["status" => true]);
        } catch(Exception $e){
            return response()->json($e->getMessage(), 400);
        } catch (Error $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    function createOrderIntgr(CustomerorderIntgr $request){
        Telescope::tag(function () {
            return [
                "typeEntity: customerorder",
                "fromWebhook",
                "integration"
            ];
        });
        set_time_limit(3600);
        $messageStack = [];
        $request->validated();
        $requestData = $request->all();
        $request = json_decode(json_encode($requestData));
        try{
            $handlerS = new HandlerService();
            $ms_token = $request->ms_token;
            $orgs = $request->org;
            $messengerAttributes = $request->messengerAttributes;
            $lid = $request->lid;
            $msIntgrClient = new MoySkladIntgr($ms_token);
            //$orgEmployees = $orgsReq->pluck("employeeId")->all();
            foreach($orgs as $orgItem){
                //$employeeId = $orgItem->employeeId;
                $lineId = $orgItem->lineId;
                $lineName = $orgItem->lineName;
                $accessToken = $orgItem->accessToken;
                $chatS = new ChatService($accessToken);
                $chatsRes = $chatS->getAllChatForEmployee(50, $lineId);
                $messageStack[] = $chatsRes->message;
                $agentH = new AgentMessengerHandler($msIntgrClient);
                foreach($chatsRes->data as $messenger => $chats){
                    $messengerAttribute = array_filter($messengerAttributes, fn($value) => $value->name == $messenger);
                    $findedAttribute = array_shift($messengerAttribute);
                    $attribute_id = $findedAttribute->attribute_id;
                    $attrMeta = $handlerS->FormationMetaById("agentMetadataAttributes", "attributemetadata", $attribute_id);
                    foreach($chats as $chat){

                        if (property_exists($chat, 'unreadMessages') and $chat->unreadMessages == 0) continue;

                        $phone = $chat->phone;
                        $username = $chat->username;
                        $name = $chat->name;
                        $chatId = $chat->id;
                        $email = $chat->email;
                        $phoneForCreating = "+{$phone}";

                        $findLogicS = new AgentFindLogicService($msIntgrClient);
                        try{
                            $agentByRequisitesRes = $findLogicS->findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id);
                        } catch(AgentFindLogicException $e){
                            if($e->getCode() == 1)
                                continue;
                        }
                        $agents = $agentByRequisitesRes->data->rows;
                        if(!empty($agents)){

                            $agentHref = $agents[0]->meta->href;
                            $customOrderS = new CustomerorderCreateLogicService($msIntgrClient);
                            $ordersByAgentRes = $customOrderS->findFirst(10, $agentHref);

                            $customerOrders = $ordersByAgentRes->data->rows;
                            $agentControllerS = new AgentControllerLogicService($msIntgrClient);
                            $infoForTask = new stdClass();
                            $infoForTask->lineName = $lineName;
                            $infoForTask->messenger = $messenger;

                            if(count($customerOrders) == 0){
                                $agentControllerS->createOrderAndAttributes($lid, $agents[0], $customOrderS, $infoForTask);
                            } else {
                                $isCreate = $customOrderS->checkStateTypeEqRegular($customerOrders);
                                if(!$isCreate){
                                     //Final
                                    $agentControllerS->createOrderAndAttributes($lid, $agents[0], $customOrderS, $infoForTask);
                                } else {
                                    //Regular
                                    $agentControllerS->updateAttributesIfNecessary($customerOrders);
                                }

                            }

                        } else if(empty($agents)){
                            match($messenger){
                                "telegram" => $agentH->telegram($phoneForCreating, $username, $name, $attrMeta),
                                "whatsapp" => $agentH->whatsapp($phoneForCreating, $chatId, $name, $attrMeta),
                                "email" => $agentH->email($email, $attrMeta),
                                "vk" => $agentH->vk($name, $chatId, $attrMeta),
                                "instagram" => $agentH->inst($name, $username, $attrMeta),
                                "telegram_bot" => $agentH->tg_bot($name, $username, $attrMeta),
                                "avito" => $agentH->avito($name, $chatId, $attrMeta),
                            };
                        }
                    }
                }
                $successMessage = "Для линии $lineName были созданы и/или обновлены все контрагенты";
                $messageStack[] = $lid->is_activity_order ? $successMessage . " и созданы заказы" : $successMessage;
                $messageStack[] = "Для линии $lineName была создана задача";
            }

            return response()->json($messageStack);

        } catch(Exception | Error $e){
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
                    if($nextError === null){
                        $messageStack["message"] = $text;
                        $code = $current->getCode();
                        if($code >= 400)
                            $statusCode = $code;
                    }
                } else {
                    $value = [
                        "message" => $message
                    ];
                    if($nextError === null){
                        $messageStack["message"] = $message;
                        $code = $current->getCode();
                        if($code >= 400)
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
