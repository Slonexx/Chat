<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Services\ChatApp\AgentFindService;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\Response;
use stdClass;

class AgentUpdateLogicService{

    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function addTags($agents, $messenger){
        $id = $agents[0]->id;
        $tags = $agents[0]->tags;
        $wasUpdated = false;

        if(!in_array("chatapp", $tags)){
            array_push($tags, "chatapp");
            $wasUpdated = true;
        }
        if(!in_array($messenger, $tags)){
            array_push($tags, $messenger);
            $wasUpdated = true;
        }

        if($wasUpdated == true){
            $agentS = new CounterpartyService($this->accountId, $this->msC);
            $body = new stdClass();
            $body->tags = $tags;
            return $agentS->update($id, $body, "Невозможно обновить теги контрагента");
        } else
            return null;

    }
}