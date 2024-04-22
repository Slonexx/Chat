<?php

namespace App\Http\Controllers;

use App\Clients\oldMoySklad;
use App\Exceptions\AgentFindLogicException;
use App\Models\MessengerAttributes;
use App\Models\organizationModel;
use App\Services\MoySklad\AgentFindLogicService;
use App\Services\ChatApp\AgentMessengerHandler;
use App\Services\ChatApp\ChatService;
use App\Services\HandlerService;
use App\Services\MoySklad\AgentUpdateLogicService;
use App\Services\MoySklad\Attributes\CounterpartyS;
use App\Services\Settings\MessengerAttributes\CreatingAttributeService;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

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
            $agentAttrS = new CounterpartyS($accountId, $msC);
            $resAttr = $setAttrS->createAttribute("messengerAttributes", "counterparty", $attrNames, $agentAttrS);
            if(!$resAttr->status)
                
                return $handlerS->responseHandler($resAttr);
            else
                $messageStack[] = $resAttr->message;

            $orgs = organizationModel::getLineIdByAccountId($accountId);
            foreach($orgs as $item){
                $employeeId = $item->employeeId;
                $chatS = new ChatService($employeeId);
                $lineId = $item->lineId;
                $chatsRes = $chatS->getAllChatForEmployee(50, $lineId);
                $messageStack[] = $chatsRes->message;

                $agentH = new AgentMessengerHandler($accountId, $msC);
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

                        $findLogicS = new AgentFindLogicService($accountId, $msC);
                        try{
                            $agentByRequisitesRes = $findLogicS->findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id);

                        } catch(AgentFindLogicException $e){
                            if($e->getCode() == 1)
                                continue;
                        }
                        $agents = $agentByRequisitesRes->data;
                        if(!$agentByRequisitesRes->status)
                            return $handlerS->responseHandler($agentByRequisitesRes, true, false);
                        else if(!empty($agents)){
                            $updateLogicS = new AgentUpdateLogicService($accountId, $msC);
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
                            $updatedAgentRes = $updateLogicS->addTagsAndAttr($agents, $messenger, $bodyWithAttr);
                            if(!$updatedAgentRes->status)
                                return $handlerS->responseHandler($updatedAgentRes, true, false);
                            
                        } else if(empty($agents)){
                    
                            $createdAgent = match($messenger){
                                "telegram" => $agentH->telegram($phoneForCreating, $username, $name, $attrMeta),
                                "whatsapp" => $agentH->whatsapp($phoneForCreating, $chatId, $name, $attrMeta),
                                "email" => $agentH->email($email, $attrMeta),
                                "vk" => $agentH->vk($name, $chatId, $attrMeta),
                                "instagram" => $agentH->inst($name, $username, $attrMeta),
                                "telegram_bot" => $agentH->tg_bot($name, $username, $attrMeta),
                                "avito" => $agentH->avito($name, $chatId, $attrMeta),
                            };
                            if(!$createdAgent->status)
                                return $handlerS->responseHandler($createdAgent, true, false);
                        }
                    }
                }

                
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
}
