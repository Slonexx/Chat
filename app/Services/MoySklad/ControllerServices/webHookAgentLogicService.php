<?php
namespace App\Services\MoySklad\ControllerServices;

use App\Clients\MoySklad;
use App\Exceptions\AgentFindLogicException;
use App\Exceptions\webHookControllerException;
use App\Services\ChatApp\AgentMessengerHandler;
use App\Services\HandlerService;
use App\Services\MoySklad\AgentFindLogicService;
use App\Services\MoySklad\AgentUpdateLogicService;
use App\Services\Response;

class webHookAgentLogicService{

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->res = new Response();
        $this->accountId = $accountId;
    }
    /**
     * логика по созданию или обновлению контрагента в зависимости от поиска в мс
     */
    function createOrUpdate($userInfo, $messenger, $attribute_id){
        $handlerS = new HandlerService();
        $agentH = new AgentMessengerHandler($this->accountId, $this->msC);
        $findLogicS = new AgentFindLogicService($this->accountId, $this->msC);
        $attrMeta = $handlerS->FormationMetaById("agentMetadataAttributes", "attributemetadata", $attribute_id);

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
                "telegram" => $agentH->telegram($phoneForCreating, $username, $name, $attrMeta),
                "whatsapp" => $agentH->whatsapp($phoneForCreating, $chatId, $name, $attrMeta),
                "email" => $agentH->email($email, $attrMeta),
                "vk" => $agentH->vk($name, $chatId, $attrMeta),
                "instagram" => $agentH->inst($name, $username, $attrMeta),
                "telegram_bot" => $agentH->tg_bot($name, $username, $attrMeta),
                "avito" => $agentH->avito($name, $chatId, $attrMeta),
            };
        //update
        } else {
            $updateLogicS = new AgentUpdateLogicService($this->accountId, $this->msC);
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
            return $updateLogicS->addTagsAndAttr($agents, $messenger, $bodyWithAttr);
        }
        
    }
}