<?php
namespace App\Services\Intgr;

use App\Clients\MoySkladIntgr;
use App\Exceptions\AgentUpdateLogicException;
use App\Exceptions\MsException;
use App\Services\Intgr\Attributes\CounterpartyS;
use App\Services\Intgr\Entities\CounterpartyService;
use App\Services\Intgr\Entities\CustomEntityService;
use App\Services\MoySklad\RequestBody\Attributes\UpdateValuesService;
use App\Services\Response;
use Illuminate\Support\Facades\Config;

class AgentUpdateLogicService{

    private MoySkladIntgr $msC;

    private Response $res;

    function __construct(MoySkladIntgr $MoySklad) {
        $this->msC = $MoySklad;
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
        $agentS = new CounterpartyService($this->msC);
        try{
            $serviceFieldsNames = [
                "lid",
            ];
    
            $config = Config::get("lidAttributes");
            $serviceFields = array_filter($config, fn($key) => in_array($key, $serviceFieldsNames), ARRAY_FILTER_USE_KEY);
            $lidName = $serviceFields["lid"]->name;
            //ожидает ответа
            $waitAnswerValueName = $serviceFields["lid"]->values[0]->name;
    
            $lidAttrS = new LidAttributesCreateService($this->msC);
            $lidAttrS->findOrCreate($serviceFields, false);
    
            $agentAttrS = new CounterpartyS($this->msC);
            $agentAttrRes = $agentAttrS->getAllAttributes(true);
            $agentAllAttributes = $agentAttrRes->data;
            $agentLidAttr = array_filter($agentAllAttributes, fn($value)=> $value->name == $lidName);
            $agentAttr = array_shift($agentLidAttr);
    
            $customEntityS = new CustomEntityService($this->msC);
            $updateValuesS = new UpdateValuesService();
            $preparedDictionary = $updateValuesS->dictionary($customEntityS, $agentAttr, $waitAnswerValueName);
            $body->attributes[] = $preparedDictionary->attributes[0];
            return $agentS->update($id, $body);
        } catch(MsException $e){
            throw new AgentUpdateLogicException("Невозможно обновить теги и аттрибуты контрагента", previous: $e);
        }
    }

    function agentUpdateLidAttribute($agentId, $lidName, $valueName, UpdateValuesService $updateValuesS, CustomEntityService $customEntityS){
        $agentAttrS = new CounterpartyS($this->msC);
        $agentAttrRes = $agentAttrS->getAllAttributes(true);
        $agentAllAttributes = $agentAttrRes->data;
        $agentLidAttr = array_filter($agentAllAttributes, fn($value)=> $value->name == $lidName);
        $agentAttr = array_shift($agentLidAttr);

        $agentS = new CounterpartyService($this->msC);
        
        $bodyForAgentUpdate = $updateValuesS->dictionary($customEntityS, $agentAttr, $valueName);
        $agentS->update($agentId, $bodyForAgentUpdate);
    }
}