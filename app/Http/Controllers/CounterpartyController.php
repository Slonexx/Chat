<?php

namespace App\Http\Controllers;

use App\Clients\MoySklad;
use App\Clients\oldMoySklad;
use App\Exceptions\AgentFindLogicException;
use App\Exceptions\CounterpartyControllerException;
use App\Models\MessengerAttributes;
use App\Models\Notes;
use App\Models\organizationModel;
use App\Services\MoySklad\AgentFindLogicService;
use App\Services\ChatApp\AgentMessengerHandler;
use App\Services\ChatApp\ChatService;
use App\Services\ChatApp\MessageService;
use App\Services\HandlerService;
use App\Services\MoySklad\ControllerServices\webHookAgentLogicService;
use App\Services\MoySklad\AgentUpdateLogicService;
use App\Services\MoySklad\Attributes\oldCounterpartyS;
use App\Services\MoySklad\Entities\CounterpartyNotesService;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\Settings\MessengerAttributes\CreatingAttributeService;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use stdClass;

class CounterpartyController extends Controller
{
    function create(string $accountId)
    {
        $messageStack = [];
        try {
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

            $orgs = organizationModel::getLineIdByAccountId($accountId);
            $msCnew = new MoySklad($accountId);
            foreach ($orgs as $item) {
                $employeeId = $item->employeeId;
                $chatS = new ChatService($employeeId);
                $lineId = $item->lineId;
                $chatsRes = $chatS->getAllChatForEmployee(50, $lineId);
                $messageStack[] = $chatsRes->message;
                $agentH = new AgentMessengerHandler($accountId, $msCnew);
                foreach ($chatsRes->data as $messenger => $chats) {
                    $attribute = MessengerAttributes::getFirst($accountId, "counterparty", $messenger);
                    $attribute_id = $attribute->attribute_id;
                    $attrMeta = $handlerS->FormationMetaById("agentMetadataAttributes", "attributemetadata", $attribute_id);
                    foreach ($chats as $chat) {
                        $phone = $chat->phone;
                        $username = $chat->username;
                        $name = $chat->name;
                        $chatId = $chat->id;
                        $email = $chat->email;
                        $phoneForCreating = "+{$phone}";

                        $findLogicS = new AgentFindLogicService($accountId, $msCnew);
                        try {
                            $agentByRequisitesRes = $findLogicS->findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id);
                        } catch (AgentFindLogicException $e) {
                            if ($e->getCode() == 1)
                                continue;
                        }
                        $agents = $agentByRequisitesRes->data->rows;
                        //update
                        if (!empty($agents)) {
                            $updateLogicS = new AgentUpdateLogicService($accountId, $msCnew);
                            $atUsername = "@{$username}";
                            $addFieldValue = match ($messenger) {
                                "telegram" => $atUsername,
                                "whatsapp" => $chatId,
                                "email" => $email,
                                "vk" => ctype_digit($chatId) ? "id{$chatId}" : $chatId,
                                "instagram" => $atUsername,
                                "telegram_bot" => $atUsername,
                                "avito" => $chatId
                            };
                            $bodyWithAttr = $handlerS->FormationAttribute($attrMeta, $addFieldValue);
                            $updateLogicS->addTagsAndAttr($agents, $messenger, $bodyWithAttr);
                            //create
                        } else if (empty($agents)) {
                            match ($messenger) {
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
                $messageStack[] = "Для сотрудника $employeeId были созданы и/или обновлены все контрагенты";
            }
            return response()->json();

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

    // function importConversationsInNotes(string $accountId)
    // {
    //     $messageStack = [];
    //     try {
    //         $handlerS = new HandlerService();
    //         $msC = new oldMoySklad($accountId);
    //         $setAttrS = new CreatingAttributeService($accountId, $msC);
    //         //все добавленные в messengerAttributes будут созданы в мс
    //         $mesAttr = Config::get("messengerAttributes");
    //         $attrNames = array_keys($mesAttr);
    //         $agentAttrS = new oldCounterpartyS($accountId, $msC);
    //         $resAttr = $setAttrS->createAttribute("messengerAttributes", "counterparty", $attrNames, $agentAttrS);
    //         if (!$resAttr->status)
    //             return $handlerS->responseHandler($resAttr);
    //         else
    //             $messageStack[] = $resAttr->message;

    //         $orgs = organizationModel::getLineIdByAccountId($accountId);
    //         $notes = Notes::getByAccountId($accountId);
    //         if ($notes->isEmpty()) {
    //             return response()->json();
    //         }
    //         $msCnew = new MoySklad($accountId);
    //         $firstNote = $notes->first();
    //         $isAddMessengerInfo = $firstNote->is_messenger;
    //         $lastStart = $firstNote->last_start;
    //         $date = new DateTime();
    //         foreach ($orgs as $item) {
    //             $lineId = $item->lineId;
    //             $lineName = $item->lineName;
    //             $employeeId = $item->employeeId;
    //             $chatS = new ChatService($employeeId);
    //             $chatappReq = new ChatappRequest($employeeId);
    //             $chatsRes = $chatS->getAllChatForEmployee(50, $lineId);
    //             $messageStack[] = $chatsRes->message;
    //             $agentH = new AgentMessengerHandler($accountId, $msCnew);
    //             $messageS = new MessageService($employeeId, $chatappReq);
    //             foreach ($chatsRes->data as $messenger => $chats) {
    //                 $attribute = MessengerAttributes::getFirst($accountId, "counterparty", $messenger);
    //                 $attribute_id = $attribute->attribute_id;
    //                 $attrMeta = $handlerS->FormationMetaById("agentMetadataAttributes", "attributemetadata", $attribute_id);
    //                 foreach ($chats as $chat) {
    //                     $phone = $chat->phone;
    //                     $username = $chat->username;
    //                     $name = $chat->name;
    //                     $chatId = $chat->id;
    //                     $email = $chat->email;
    //                     $phoneForCreating = "+{$phone}";

    //                     $findLogicS = new AgentFindLogicService($accountId, $msCnew);
    //                     try {
    //                         $agentByRequisitesRes = $findLogicS->findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id);
    //                     } catch (AgentFindLogicException $e) {
    //                         if ($e->getCode() == 1)
    //                             continue;
    //                     }
    //                     $agents = $agentByRequisitesRes->data->rows;
    //                     //update
    //                     if (!empty($agents)) {
    //                         $atUsername = "@{$username}";
    //                         $usernameOrPhone = match ($messenger) {
    //                             "telegram" => $atUsername,
    //                             "whatsapp" => $chatId,
    //                             "email" => $email,
    //                             "vk" => ctype_digit($chatId) ? "id{$chatId}" : $chatId,
    //                             "instagram" => $atUsername,
    //                             "telegram_bot" => $atUsername,
    //                             "avito" => $chatId
    //                         };
    //                         $createNotesS = new GetNotesLogicService($accountId, $msCnew, $employeeId, $chatappReq);
    //                         $MAX_UPLOAD_MESSAGE = 200;
    //                         $limitMessages = 100;
    //                         $messages = $createNotesS->getAllFromOldToNew($lineId, $messenger, $chatId, $limitMessages, $lastStart, $MAX_UPLOAD_MESSAGE);
    //                         if (count($messages) == 0)
    //                             continue;
    //                         $preparedMessages = $messageS->prepareMessages($lineName, $lineId, $messenger, $usernameOrPhone, $isAddMessengerInfo, $messages);
    //                         //$messageS
    //                         //$countMessages = count($messages);

    //                         // $maxCountRequest = 5;
    //                         // if($maxCountRequest > $countMessages)
    //                         //     $maxCountRequest = $countMessages;
    //                         // $chunks = array_chunk($messages, $maxCountRequest);
    //                         $params = [
    //                             "headers" => [
    //                                 'Content-Type' => 'application/json'
    //                             ]
    //                         ];
    //                         $appUrl = Config::get("Global.url", null);
    //                         if (!is_string($appUrl) || $appUrl == null)
    //                             throw new Error("url отсутствует или имеет некорректный формат");
    //                         $preppedUrl = $appUrl . "api/counterparty/sendNotes/$accountId";
    //                         //foreach ($chunks as $chunk) {
    //                         if (!empty($preparedMessages)) {
    //                             $body = new stdClass();
    //                             $body->messages = $preparedMessages;
    //                             $body->url = $agents[0]->meta->href . "/notes";
    //                             $params['json'] = $body;
    //                             ProcessNotes::dispatch($params, $preppedUrl)->onConnection('database')->onQueue("high");
    //                         }

    //                         //}

    //                         //create
    //                     } else if (empty($agents)) {
    //                         match ($messenger) {
    //                             "telegram" => $agentH->telegram($phoneForCreating, $username, $name, $attrMeta),
    //                             "whatsapp" => $agentH->whatsapp($phoneForCreating, $chatId, $name, $attrMeta),
    //                             "email" => $agentH->email($email, $attrMeta),
    //                             "vk" => $agentH->vk($name, $chatId, $attrMeta),
    //                             "instagram" => $agentH->inst($name, $username, $attrMeta),
    //                             "telegram_bot" => $agentH->tg_bot($name, $username, $attrMeta),
    //                             "avito" => $agentH->avito($name, $chatId, $attrMeta),
    //                         };
    //                     }
    //                 }
    //             }
    //             $messageStack[] = "Для сотрудника $employeeId были созданы и/или обновлены все контрагенты";
    //         }
    //         Notes::where('accountId', $accountId)
    //             ->update(['last_start' => $date]);

    //         Telescope::stopRecording();
    //         return response()->json();

    //     } catch (Exception|Error $e) {
    //         $current = $e;
    //         $messages = [];
    //         $statusCode = 500;//or HTTP Exception code

    //         while ($current !== null) {
    //             $filePath = $current->getFile();
    //             $fileLine = $current->getLine();
    //             $message = $current->getMessage();

    //             $nextError = $current->getPrevious();

    //             $parts = explode('|', $message);

    //             if (count($parts) === 2) {
    //                 $text = $parts[0];
    //                 $json_str = array_pop($parts);

    //                 $value = [
    //                     "message" => $text,
    //                     "data" => json_decode($json_str)
    //                 ];
    //                 if ($nextError === null) {
    //                     $messageStack["message"] = $text;
    //                     $code = $current->getCode();
    //                     if ($code >= 400)
    //                         $statusCode = $code;
    //                 }
    //             } else {
    //                 $value = [
    //                     "message" => $message
    //                 ];
    //                 if ($nextError === null) {
    //                     $messageStack["message"] = $message;
    //                     $code = $current->getCode();
    //                     if ($code >= 400)
    //                         $statusCode = $code;
    //                 }
    //             }


    //             $fileName = basename($filePath);

    //             $key = "{$fileName}:{$fileLine}";

    //             $messages[] = [
    //                 $key => $value
    //             ];
    //             $current = $current->getPrevious();
    //         }
    //         $messageStack["error"] = $messages;
    //         return response()->json($messageStack, $statusCode);
    //     }
    // }

    // function sendNotes(Request $request, $accountId)
    // {
    //     try {
    //         $data = json_decode(json_encode($request->all()));
    //         $url = $data->url;
    //         $messages = $data->messages;
    //         $msClient = new MoySklad($accountId);

    //         foreach ($messages as $item) {
    //             $body = (object)[
    //                 "description" => $item
    //             ];
    //             Telescope::stopRecording();
    //             $msClient->post($url, $body);
    //         }

    //         return response()->json();
    //     } catch (Exception|Error $e) {
    //         $statusCode = 500;
    //         $messages = [];
    //         $current = $e;

    //         while ($current !== null) {
    //             $message = $current->getMessage();
    //             $filePath = $current->getFile();
    //             $fileLine = $current->getLine();
    //             $parts = explode('|', $message);

    //             $value = count($parts) === 2
    //                 ? ["message" => $parts[0], "data" => json_decode($parts[1])]
    //                 : ["message" => $message];

    //             if ($current->getPrevious() === null) {
    //                 $statusCode = max($statusCode, $current->getCode());
    //                 $messageStack["message"] = $message;
    //             }

    //             $messages[] = [
    //                 basename($filePath) . ":$fileLine" => $value
    //             ];
    //             $current = $current->getPrevious();
    //         }

    //         $messageStack["error"] = $messages;
    //         return response()->json($messageStack, $statusCode);
    //     }
    // }

    function checkRate($accountId)
    {
        $messageStack = [];
        try {
            $msClient = new MoySklad($accountId);
            $agentS = new CounterpartyService($accountId, $msClient);
            $agentRes = $agentS->getWithLimit(1);

            $counterparty = $agentRes->data->rows;
            $messageStack[] = $agentRes->message;
            if (count($counterparty) == 0)
                throw new CounterpartyControllerException("Отсутствуют контрагенты в МС");

            $counterpartyId = $counterparty[0]->id;

            $body = new stdClass();
            $body->description = "Проверка создания заметки";

            $counterpartyNotesS = new CounterpartyNotesService($accountId, $msClient);

            $noteCreateRes = $counterpartyNotesS->create($counterpartyId, $body);
            $noteId = $noteCreateRes->data[0]->id;
            $messageStack[] = $noteCreateRes->message;

            $noteDeleteRes = $counterpartyNotesS->delete($counterpartyId, $noteId);
            $messageStack[] = $noteDeleteRes->message;
            return response()->json($messageStack);

        } catch (Exception $e) {
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
