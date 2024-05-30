<?php
namespace App\Services\Intgr;

use App\Clients\MoySkladIntgr;
use App\Exceptions\AgentFindLogicException;
use App\Services\Intgr\AgentFindService;
use App\Services\Response;

class AgentFindLogicService{

    private MoySkladIntgr $msC;

    private Response $res;

    function __construct(MoySkladIntgr $MoySklad) {
        $this->msC = $MoySklad;
        $this->res = new Response();
    }

    function findByRequisites($messenger, $chatId, $username, $name, $phone, $email, $attribute_id){
        $agentFindS = new AgentFindService($this->msC);
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