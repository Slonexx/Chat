<?php

namespace App\Http\Controllers;

use App\Clients\MoySklad;
use App\Clients\oldMoySklad;
use App\Exceptions\AgentFindLogicException;
use App\Models\Lid;
use App\Models\MessengerAttributes;
use App\Models\organizationModel;
use App\Services\ChatApp\AgentMessengerHandler;
use App\Services\ChatApp\ChatService;
use App\Services\HandlerService;
use App\Services\MoySklad\AgentControllerLogicService;
use App\Services\MoySklad\AgentFindLogicService;
use App\Services\MoySklad\Attributes\oldCounterpartyS;
use App\Services\MoySklad\CustomerorderCreateLogicService;
use App\Services\Settings\MessengerAttributes\CreatingAttributeService;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use stdClass;

class CustomerorderController extends Controller
{
    function create(Request $request, $accountId){
        set_time_limit(3600);
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
                return $handlerS->responseHandler($resAttr, true, false);
            else
                $messageStack[] = $resAttr->message;

            $orgs = organizationModel::getLineIdByAccountId($accountId);
            $lid = Lid::getFirstByAccountId($accountId);
            $msCnew = new MoySklad($accountId);
            //$orgEmployees = $orgsReq->pluck("employeeId")->all();
            foreach($orgs as $orgItem){
                $employeeId = $orgItem->employeeId;
                $lineId = $orgItem->lineId;
                $lineName = $orgItem->lineName;
                $chatS = new ChatService($employeeId);
                $chatsRes = $chatS->getAllChatForEmployee(50, $lineId);
                $messageStack[] = $chatsRes->message;
                $agentH = new AgentMessengerHandler($accountId, $msCnew);
                foreach($chatsRes->data as $messenger => $chats){
                    $attribute = MessengerAttributes::getFirst($accountId, "counterparty", $messenger);
                    $attribute_id = $attribute->attribute_id;
                    $attrMeta = $handlerS->FormationMetaById("agentMetadataAttributes", "attributemetadata", $attribute_id);
                    foreach($chats as $chat){

                        //dd($chat);

                        if (property_exists($chat, 'unreadMessages') and $chat->unreadMessages == 0) continue;

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
                        if(!empty($agents)){
                            if(empty($lid)){
                                throw new Error("настройки lid не пройдены");
                            }

                            $responsible = $lid->responsible;
                            $responsibleUuid = $lid->responsible_uuid;
                            $isCreateOrder = $lid->is_activity_order;

                            $orderDbSettings = new stdClass();

                            $orderDbSettings->organization = $lid->organization;
                            $orderDbSettings->organization_account = $lid->organization_account;
                            $orderDbSettings->project_uid = $lid->project_uid;
                            $orderDbSettings->sales_channel_uid = $lid->sales_channel_uid;
                            $orderDbSettings->states = $lid->states;
                            $orderDbSettings->lid = $lid->lid;
                            $orderDbSettings->tasks = $lid->tasks;

                            $agentHref = $agents[0]->meta->href;
                            $customOrderS = new CustomerorderCreateLogicService($accountId, $msCnew);
                            $ordersByAgentRes = $customOrderS->findFirst(10, $agentHref);

                            $customerOrders = $ordersByAgentRes->data->rows;
                            $agentControllerS = new AgentControllerLogicService($accountId, $msCnew);
                            $infoForTask = new stdClass();
                            $infoForTask->lineId = $lineId;
                            $infoForTask->lineName = $lineName;
                            $infoForTask->messenger = $messenger;

                            if(count($customerOrders) == 0){
                                $agentControllerS->createOrderAndAttributes($orderDbSettings, $agents[0], $customOrderS, $responsible, $responsibleUuid, $isCreateOrder, $infoForTask);
                            } else {
                                $isCreate = $customOrderS->checkStateTypeEqRegular($customerOrders);
                                if(!$isCreate){
                                     //Final
                                    $agentControllerS->createOrderAndAttributes($orderDbSettings, $agents[0], $customOrderS, $responsible, $responsibleUuid, $isCreateOrder, $infoForTask);
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
                $successMessage = "Для сотрудника $employeeId были созданы и/или обновлены все контрагенты";
                $messageStack[] = $isCreateOrder ? $successMessage : $successMessage . " и созданы заказы";
                $messageStack[] = "Для сотрудника $employeeId была создана задача";
            }

            return response()->json([],200);

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
