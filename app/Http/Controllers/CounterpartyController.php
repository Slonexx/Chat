<?php

namespace App\Http\Controllers;

use App\Clients\MoySklad;
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

class CounterpartyController extends Controller
{
    function create(Request $request, $accountId){
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
            $chatS = new ChatService($accountId);
            foreach($orgs as $item){
                $employeeId = $item->employeeId;
                $lineId = $item->lineId;
                $chatsRes = $chatS->getAllChatForEmployee(50, $employeeId, $lineId);
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
