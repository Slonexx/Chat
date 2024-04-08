<?php

namespace App\Http\Controllers;

use App\Clients\newClient;
use App\Models\employeeModel;
use App\Models\MessengerAttributes;
use App\Models\organizationModel;
use App\Services\ChatApp\AgentFindService;
use App\Services\ChatApp\AgentMessengerHandler;
use App\Services\ChatApp\ChatService;
use App\Services\HandlerService;
use App\Services\MoySklad\Attributes\CounterpartyS;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\Response;
use App\Services\Settings\MessengerAttributes\CreatingAttributeService;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use stdClass;

class CounterpartyController extends Controller
{
    function create(Request $request, $accountId){
        try{
            $handlerS = new HandlerService();
            $setAttrS = new CreatingAttributeService($accountId);
            //все добавленные в messengerAttributes будут созданы в мс
            $mesAttr = Config::get("messengerAttributes");
            $attrNames = array_keys($mesAttr);
            $resAttr = $setAttrS->createAttribute("messengerAttributes", "counterparty", $attrNames, new CounterpartyS($accountId));
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

                $agentH = new AgentMessengerHandler($accountId);
                $agentFindS = new AgentFindService($accountId);
                foreach($chatsRes->data as $messenger => $chats){
                    $attribute = MessengerAttributes::getFirst($accountId, "counterparty", $messenger);
                    $attribute_id = $attribute->attribute_id;
                    $attrMeta = $handlerS->FormationMetaById("agentMetadataAttributes", "attributemetadata", $attribute_id);
                    foreach($chats as $chat){
                        $phone = $chat->phone;
                        $username = $chat->username;
                        $name = $chat->name;
                        $chatId = $chat->id;

                        if(strlen($phone) < 11)
                            continue;
                        $phoneForCreating = "+{$phone}";
                        $phoneForFinding = "%2b{$phone}";
                        $agentFindRes = match($messenger){
                            "telegram" => $agentFindS->telegram($phoneForFinding, $name, $username, $attribute_id),
                            "whatsapp" => $agentFindS->whatsapp($phoneForFinding, $name, $chatId, $attribute_id),
                        };
                        $agents = $agentFindRes->data;
                        if(!$agentFindRes->status)
                            return $agentFindRes;
                        else if(!empty($agents)){
                            $id = $agents[0]->id;
                            $tags = $agents[0]->tags;
                            array_push($tags, "chatapp");
                            array_push($tags, $messenger);
                            $agentS = new CounterpartyService($accountId);
                            $agentS->addTags($id, $tags);
                            
                        } else if(empty($agentFindRes->data)){
                    
                            match($messenger){
                                "telegram" => $agentH->telegram($phoneForCreating, $username, $name, $attrMeta),
                                "whatsapp" => $agentH->whatsapp($phoneForCreating, $chatId, $name, $attrMeta),
                            };
                            
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
