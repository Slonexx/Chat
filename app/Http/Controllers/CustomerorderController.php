<?php

namespace App\Http\Controllers;

use Error;
use Exception;
use Illuminate\Http\Request;

class CustomerorderController extends Controller
{
    function create(Request $request, $accountId, $employeeId){
        try{

            $handlerS = new HandlerService();
            $msC = new MoySklad($accountId);
            $setAttrS = new CreatingAttributeService($accountId, $msC);
            //все добавленные в messengerAttributes будут созданы в мс
            $mesAttr = Config::get("messengerAttributes");
            $attrNames = array_keys($mesAttr);
            $resAttr = $setAttrS->createAttribute("messengerAttributes", "counterparty", $attrNames, new CounterpartyS($accountId, $msC));
            if(!$resAttr->status)
                return $handlerS->responseHandler($resAttr, true, false);

            $orgs = organizationModel::where("accountId", $accountId)->get()->all();
            foreach($orgs as $item){
                $employeeId = $item->employeeId;
                $chatS = new ChatService($accountId, $employeeId);
                $lineId = $item->lineId;
                $chatsRes = $chatS->getAllChatForEmployee(50, $lineId);
                dd($chatsRes->data['avito']);
                if(!$chatsRes->status)  
                    return $handlerS->responseHandler($chatsRes, true, false);

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
                        $agentByRequisitesRes = $findLogicS->findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id);
                        if(!isset($agentByRequisitesRes))
                            continue;
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
                            };
                            if(!$createdAgent->status)
                                return $handlerS->responseHandler($createdAgent, true, false);
                        }
                    }
                }

                
            }
            return response()->json();
            
        } catch(Exception | Error $e){
            return response()->json($e->getMessage(), 500);
        }

    }
}
