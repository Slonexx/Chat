<?php

namespace App\Http\Controllers;

use App\Clients\MoySklad;
use App\Clients\newClient;
use App\Models\Lid;
use App\Models\MessengerAttributes;
use App\Models\organizationModel;
use App\Services\ChatApp\AgentMessengerHandler;
use App\Services\ChatApp\ChatService;
use App\Services\HandlerService;
use App\Services\MoySklad\AgentControllerLogicService;
use App\Services\MoySklad\AgentFindLogicService;
use App\Services\MoySklad\AgentUpdateLogicService;
use App\Services\MoySklad\Attributes\CounterpartyS;
use App\Services\MoySklad\CustomerorderCreateLogicService;
use App\Services\MoySklad\LidAttributesCreateService;
use App\Services\Settings\MessengerAttributes\CreatingAttributeService;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class CustomerorderController extends Controller
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
            $lid = Lid::where("accountId", $accountId)->get()->first();
            //$orgEmployees = $orgsReq->pluck("employeeId")->all();
            foreach($orgs as $orgItem){
                $chatappC = new newClient($orgItem->employeeId);
                $chatS = new ChatService($accountId, $orgItem->employeeId, $chatappC);
                $chatsRes = $chatS->getAllChatForEmployee(50, $orgItem->lineId);
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
                            if(empty($lid)){
                                $res = $handlerS->createResponse(false, "настройки lid не пройдены");
                                return $handlerS->responseHandler($res, true, false);
                            }

                            $responsible = $lid->responsible;
                            $responsibleUuid = $lid->responsible_uuid;
                            $isCreateOrder = $lid->is_activity_order;
                            
                            $agentHref = $agents[0]->meta->href;
                            $customOrderS = new CustomerorderCreateLogicService($accountId, $msC);
                            $ordersByAgentRes = $customOrderS->findFirst(10, $agentHref);
                            if(!$ordersByAgentRes->status)
                                return $handlerS->responseHandler($ordersByAgentRes, true, false);

                            $customerOrders = $ordersByAgentRes->data;
                            $organId = $orgItem->organId;
                            $agentControllerS = new AgentControllerLogicService($accountId, $msC);
                            if(count($customerOrders) == 0){
                                $res = $agentControllerS->createOrderAndAttributes($organId, $agents[0], $customOrderS, $responsible, $responsibleUuid, $isCreateOrder);
                                if(!$res->status)
                                    return $handlerS->responseHandler($res, true, false);
                            } else {
                                $isCreate = $customOrderS->checkStateTypeEqRegular($customerOrders);
                                if($isCreate){
                                    //Regular
                                    $customerOrderRes = $agentControllerS->createOrderAndAttributes($organId, $agents[0], $customOrderS, $responsible, $responsibleUuid, $isCreateOrder);
                                    if(!$customerOrderRes->status)
                                        return $handlerS->responseHandler($customerOrderRes, true, false);

                                } else {
                                    //Final
                                    $customerOrderRes = $agentControllerS->updateAttributesIfNecessary($customerOrders);
                                    if(!$customerOrderRes->status)
                                        return $handlerS->responseHandler($customerOrderRes, true, false);
                                }

                            }
                            
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
            return response()->json($e->getMessage(), 500);
        }

    }
}
