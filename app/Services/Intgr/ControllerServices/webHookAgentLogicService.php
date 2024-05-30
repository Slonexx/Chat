<?php
namespace App\Services\Intgr\ControllerServices;

use App\Clients\MoySkladIntgr;
use App\Services\Intgr\AgentMessengerHandler;
use App\Services\HandlerService;
use App\Services\Intgr\AgentFindLogicService;
use App\Services\Intgr\AgentUpdateLogicService;
use App\Services\Response;

class webHookAgentLogicService{

    private MoySkladIntgr $msC;

    function __construct(MoySkladIntgr $MoySklad) {
        $this->msC = $MoySklad;
    }
    /**
     * логика по созданию или обновлению контрагента в зависимости от поиска в мс
     */
    function createOrUpdate($userInfo, $messenger, $attribute_id){
        $handlerS = new HandlerService();
        $agentH = new AgentMessengerHandler($this->msC);
        $findLogicS = new AgentFindLogicService($this->msC);
        $attrMetaAgent = $handlerS->FormationMetaById("agentMetadataAttributes", "attributemetadata", $attribute_id);
        
        $phone = $userInfo->phone;
        $username = $userInfo->username;
        $name = $userInfo->name;
        $chatId = $userInfo->id;
        $email = $userInfo->email;
        $phoneForCreating = "+{$phone}";
        
        $agentByRequisitesRes = $findLogicS->findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id);
        
        $agents = $agentByRequisitesRes->data->rows;

        //create
        if (empty($agents)) {
            return match ($messenger) {
                "telegram" => $agentH->telegram($phoneForCreating, $username, $name, $attrMetaAgent),
                "whatsapp" => $agentH->whatsapp($phoneForCreating, $chatId, $name, $attrMetaAgent),
                "email" => $agentH->email($email, $attrMetaAgent),
                "vk" => $agentH->vk($name, $chatId, $attrMetaAgent),
                "instagram" => $agentH->inst($name, $username, $attrMetaAgent),
                "telegram_bot" => $agentH->tg_bot($name, $username, $attrMetaAgent),
                "avito" => $agentH->avito($name, $chatId, $attrMetaAgent),
            };
        //update
        } else {
            $updateLogicS = new AgentUpdateLogicService($this->msC);
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
            $bodyWithAttr = $handlerS->FormationAttribute($attrMetaAgent, $addFieldValue);
            return $updateLogicS->addTagsAndAttr($agents, $messenger, $bodyWithAttr);
        }
        
    }
}