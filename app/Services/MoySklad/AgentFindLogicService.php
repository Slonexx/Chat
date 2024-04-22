<?php
namespace App\Services\MoySklad;

use App\Clients\oldMoySklad;
use App\Exceptions\AgentFindLogicException;
use App\Services\ChatApp\AgentFindService;
use App\Services\Response;

class AgentFindLogicService{

    private oldMoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, oldMoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new oldMoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id){
        $agentFindS = new AgentFindService($this->accountId, $this->msC);
        if($messenger == "telegram" || $messenger == "whatsapp"){
            if(strlen($phone) < 11)
                throw new AgentFindLogicException("Длина телефона меньше 11", 1);
            $phoneForFinding = "%2b{$phone}";
        }

        $agentFindRes = match($messenger){
            "telegram" => $agentFindS->telegram($phoneForFinding, $name, $username, $attribute_id),
            "whatsapp" => $agentFindS->whatsapp($phoneForFinding, $name, $chatId, $attribute_id),
            "email" => $agentFindS->email($email, $attribute_id),
            "vk" => $agentFindS->vk($chatId, $attribute_id),
            "instagram" => $agentFindS->inst($username, $attribute_id),
            "telegram_bot" => $agentFindS->tg_bot($username, $attribute_id),
            "avito" => $agentFindS->avito($chatId, $attribute_id),
        };
        return $agentFindRes;
    }
}