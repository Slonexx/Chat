<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Services\ChatApp\AgentFindService;
use App\Services\Response;

class AgentFindLogicService{

    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id){
        $agentFindS = new AgentFindService($this->accountId, $this->msC);
        if($messenger == "telegram" || $messenger == "whatsapp"){
            if(strlen($phone) < 11)
                return null;
            $phoneForFinding = "%2b{$phone}";
        }
        $agentFindRes = match($messenger){
            "telegram" => $agentFindS->telegram($phoneForFinding, $name, $username, $attribute_id),
            "whatsapp" => $agentFindS->whatsapp($phoneForFinding, $name, $chatId, $attribute_id),
            "email" => $agentFindS->email($email, $attribute_id),
            "vk" => $agentFindS->vk($name, $chatId, $attribute_id),
        };
        return $agentFindRes;
    }
}