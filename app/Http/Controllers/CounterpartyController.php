<?php

namespace App\Http\Controllers;

use App\Clients\MoySklad;
use App\Clients\MoySkladAsync;
use App\Clients\oldMoySklad;
use App\Exceptions\AgentFindLogicException;
use App\Jobs\ProcessNotes;
use App\Models\MessengerAttributes;
use App\Models\Notes;
use App\Models\organizationModel;
use App\Services\MoySklad\AgentFindLogicService;
use App\Services\ChatApp\AgentMessengerHandler;
use App\Services\ChatApp\ChatService;
use App\Services\ChatApp\MessageService;
use App\Services\ChatappRequest;
use App\Services\HandlerService;
use App\Services\MoySklad\AgentUpdateLogicService;
use App\Services\MoySklad\Attributes\oldCounterpartyS;
use App\Services\MoySklad\CreateNotesLogicService;
use App\Services\Settings\MessengerAttributes\CreatingAttributeService;
use DateTime;
use Error;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use stdClass;


class CounterpartyControllerException extends Exception {}

class CounterpartyController extends Controller
{
    function create(Request $request, string $accountId){
        $messageStack = [];
        try{
            $handlerS = new HandlerService();
            $msC = new oldMoySklad($accountId);
            $setAttrS = new CreatingAttributeService($accountId, $msC);
            //все добавленные в messengerAttributes будут созданы в мс
            $mesAttr = Config::get("messengerAttributes");
            $attrNames = array_keys($mesAttr);
            $agentAttrS = new oldCounterpartyS($accountId, $msC);
            $resAttr = $setAttrS->createAttribute("messengerAttributes", "counterparty", $attrNames, $agentAttrS);
            if(!$resAttr->status)
                return $handlerS->responseHandler($resAttr);
            else
                $messageStack[] = $resAttr->message;

            $orgs = organizationModel::getLineIdByAccountId($accountId);
            $msCnew = new MoySklad($accountId);
            foreach($orgs as $item){
                $employeeId = $item->employeeId;
                $chatS = new ChatService($employeeId);
                $lineId = $item->lineId;
                $chatsRes = $chatS->getAllChatForEmployee(50, $lineId);
                $messageStack[] = $chatsRes->message;
                $agentH = new AgentMessengerHandler($accountId, $msCnew);
                foreach($chatsRes->data as $messenger => $chats){
                    $attribute = MessengerAttributes::getFirst($accountId, "counterparty", $messenger);
                    $attribute_id = $attribute->attribute_id;
                    $attrMeta = $handlerS->FormationMetaById("agentMetadataAttributes", "attributemetadata", $attribute_id);
                    foreach($chats as $chat){
                        $phone = $chat->phone;
                        $username = $chat->username;
                        $name = $chat->name;
                        $chatId = $chat->id;
                        $email = $chat->email;
                        $phoneForCreating = "+{$phone}";

                        $findLogicS = new AgentFindLogicService($accountId, $msCnew);
                        try{
                            $agentByRequisitesRes = $findLogicS->findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id);
                        } catch(AgentFindLogicException $e){
                            if($e->getCode() == 1)
                                continue;
                        }
                        $agents = $agentByRequisitesRes->data->rows;
                        //update
                        if(!empty($agents)){
                            $updateLogicS = new AgentUpdateLogicService($accountId, $msCnew);
                            $atUsername = "@{$username}";
                            $addFieldValue = match($messenger){
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
                $messageStack[] = "Для сотрудника $employeeId были созданы и/или обновлены все контрагенты";
            }
            return response()->json();
            
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

    function importConversationsInNotes(Request $request, string $accountId){
        $messageStack = [];
        try{
            $handlerS = new HandlerService();
            $msC = new oldMoySklad($accountId);
            $setAttrS = new CreatingAttributeService($accountId, $msC);
            //все добавленные в messengerAttributes будут созданы в мс
            $mesAttr = Config::get("messengerAttributes");
            $attrNames = array_keys($mesAttr);
            $agentAttrS = new oldCounterpartyS($accountId, $msC);
            $resAttr = $setAttrS->createAttribute("messengerAttributes", "counterparty", $attrNames, $agentAttrS);
            if(!$resAttr->status)
                return $handlerS->responseHandler($resAttr);
            else
                $messageStack[] = $resAttr->message;

            $orgs = organizationModel::getLineIdByAccountId($accountId);
            $notes = Notes::getByAccountId($accountId);
            if($notes->isEmpty()){
                return response()->json();
            }
            $msCnew = new MoySklad($accountId);
            $firstNote = $notes->first();
            $isAddMessengerInfo = $firstNote->is_messenger;
            $lastStart = $firstNote->last_start;
            $date = new DateTime();
            foreach($orgs as $item){
                $lineId = $item->lineId;
                $lineName = $item->lineName;
                $employeeId = $item->employeeId;
                $chatS = new ChatService($employeeId);
                $chatappReq = new ChatappRequest($employeeId);
                $chatsRes = $chatS->getAllChatForEmployee(50, $lineId);
                $messageStack[] = $chatsRes->message;
                $agentH = new AgentMessengerHandler($accountId, $msCnew);
                $messageS = new MessageService($employeeId, $chatappReq);
                foreach($chatsRes->data as $messenger => $chats){
                    $attribute = MessengerAttributes::getFirst($accountId, "counterparty", $messenger);
                    $attribute_id = $attribute->attribute_id;
                    $attrMeta = $handlerS->FormationMetaById("agentMetadataAttributes", "attributemetadata", $attribute_id);
                    foreach($chats as $chat){
                        $phone = $chat->phone;
                        $username = $chat->username;
                        $name = $chat->name;
                        $chatId = $chat->id;
                        $email = $chat->email;
                        $phoneForCreating = "+{$phone}";

                        $findLogicS = new AgentFindLogicService($accountId, $msCnew);
                        try{
                            $agentByRequisitesRes = $findLogicS->findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id);
                        } catch(AgentFindLogicException $e){
                            if($e->getCode() == 1)
                                continue;
                        }
                        $agents = $agentByRequisitesRes->data->rows;
                        //update
                        if(!empty($agents)){
                            $atUsername = "@{$username}";
                            $usernameOrPhone = match($messenger){
                                "telegram" => $atUsername,
                                "whatsapp" => $chatId,
                                "email" => $email,
                                "vk" => ctype_digit($chatId) ? "id{$chatId}" : $chatId,
                                "instagram" => $atUsername,
                                "telegram_bot" => $atUsername,
                                "avito" => $chatId
                            };
                            $createNotesS = new CreateNotesLogicService($accountId, $msCnew, $employeeId, $chatappReq);
                            $MAX_UPLOAD_MESSAGE = 200;
                            $limitMessages = 100;
                            $messages = $createNotesS->getAllFromOldToNew($lineId, $messenger, $chatId, $limitMessages, $lastStart, $MAX_UPLOAD_MESSAGE);
                            if(count($messages) == 0)
                                continue;
                            $preparedMessages = $messageS->prepareMessages($lineName, $lineId, $messenger, $usernameOrPhone, $isAddMessengerInfo, $messages);
                            //$messageS
                            //$countMessages = count($messages);

                            // $maxCountRequest = 5;
                            // if($maxCountRequest > $countMessages)
                            //     $maxCountRequest = $countMessages;
                            // $chunks = array_chunk($messages, $maxCountRequest);
                            $params = [
                                "headers" => [
                                    'Content-Type' => 'application/json'
                                ]
                            ];
                            $appUrl = Config::get("Global.url", null);
                            if(!is_string($appUrl) || $appUrl == null)
                                throw new Error("url отсутствует или имеет некорректный формат");
                            $preppedUrl = $appUrl . "api/counterparty/sendNotes/$accountId";
                            //foreach ($chunks as $chunk) {
                            if(!empty($preparedMessages)){
                                $body = new stdClass();
                                $body->messages = $preparedMessages;
                                $body->url = $agents[0]->meta->href . "/notes";
                                $params['json'] = $body;
                                ProcessNotes::dispatch($params, $preppedUrl)->onConnection('database')->onQueue("high");
                            }

                            //}
                            
                        //create
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
                $messageStack[] = "Для сотрудника $employeeId были созданы и/или обновлены все контрагенты";
            }
            Notes::where('accountId', $accountId)
                ->update(['last_start' => $date]);
            
            return response()->json();
            
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

    function sendNotes(Request $request, $accountId){
        try{
            $data = json_decode(json_encode($request->all()));
            $url = $data->url;
            $messages = $data->messages;
            // $maxCountRequest = $data->maxRequest;
            // $loop = Loop::get();
            // $promises = [];
            // $start_time = microtime(true);
            // $countStart = 1;
            $msClient = new MoySklad($accountId);

            //$msClientA = new MoySkladAsync($accountId);
            foreach ($messages as $item) {
                $body = (object)[
                    "description" => $item
                ];
                try {
                    $msClient->post($url, $body);
                    // $encodedBody = json_encode($body);
                    // $msClientA->postAsync($url, $encodedBody, $loop)->then(
                    //     function ($res) use ($maxCountRequest, $start_time, &$countStart, $loop) {
                    //         //$this->info("Success {$item['accountId']}");
                    //         $info = $res->getBody()->getContents();
                    //         $promises[] = $info;
                    //         if($countStart == $maxCountRequest){
                    //             $decodedInfo = json_decode($info);
                    //             $end_time =  $decodedInfo->data->time;
                    //             $res_time = round($end_time - $start_time, 2);
                    //             //$this->info("Время выполнения = {$res_time}c.");
                    //             $loop->stop();
                    //             //$this->info(strval($res->body()));
                    //         }
                    //         $countStart++;
                    //     },
                    //     function (Exception $e) use ($item){
                    //         throw $e;
                    //         //$this->info("Fail {$item['accountId']}");
                    //         //$this->info(strval($e->getMessage()));
                    //     }
                    // );

                } catch (RequestException $e) {
                    throw $e;
                    // $this->info('Что-то пошло не так при создании'. $item['UID_ms'] . $e->getMessage());
                    // continue;
                }
            }
            $peakMemoryUsage = memory_get_peak_usage(true);
            //$this->info("Пиковое использование памяти: ".($peakMemoryUsage/1024));
            // $resolvedPromises = all($promises);
            return response()->json();
            // if($countStart == $maxCountRequest){
            //     $loop->run();
            // }

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
