<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Exceptions\AgentUpdateLogicException;
use App\Exceptions\MsException;
use App\Services\MoySklad\Attributes\CounterpartyS;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\MoySklad\Entities\CustomEntityService;
use App\Services\MoySklad\RequestBody\Attributes\UpdateValuesService;
use App\Services\Response;

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

    function addTagsAndAttr($agents, $messenger, $body){
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
            $body->tags = $tags;
        }
        $agentS = new CounterpartyService($this->accountId, $this->msC);
        try{
            return $agentS->update($id, $body);
        } catch(MsException $e){
            throw new AgentUpdateLogicException("Невозможно обновить теги контрагента", previous: $e);
        }
    }

    function agentUpdateLidAttribute($agentId, $lidName, $valueName, UpdateValuesService $updateValuesS, CustomEntityService $customEntityS){
        $agentAttrS = new CounterpartyS($this->accountId, $this->msC);
        $agentAttrRes = $agentAttrS->getAllAttributes(true);
        $agentAllAttributes = $agentAttrRes->data;
        $agentLidAttr = array_filter($agentAllAttributes, fn($value)=> $value->name == $lidName);
        $agentAttr = array_shift($agentLidAttr);

        $agentS = new CounterpartyService($this->accountId, $this->msC);
        
        $bodyForAgentUpdate = $updateValuesS->dictionary($customEntityS, $agentAttr, $valueName);
        $agentS->update($agentId, $bodyForAgentUpdate);
    }
}