<?php
namespace App\Services\MoySklad;

use App\Clients\oldMoySklad;
use App\Services\HandlerService;
use App\Services\MoySklad\Attributes\CustomorderS;
use App\Services\MoySklad\Entities\CustomEntityService;
use App\Services\MoySklad\RequestBody\Attributes\UpdateValuesService;
use App\Services\Response;
use Illuminate\Support\Facades\Config;

class AgentControllerLogicService{

    private oldMoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, oldMoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new oldMoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function createOrderAndAttributes($organId, $agent, CustomerorderCreateLogicService $customOrderS, $responsible, $responsibleUuid, $isCreateOrder){
        $handlerS = new HandlerService();
        //agentId
        $agentHref = $agent->meta->href;
        $agentId = explode("/", $agentHref);
        $agentId = array_pop($agentId);
        //agentId
        $agentAttr = $agent->attributes ?? null;
        //findOrCreateAttribute
        $attributesS = new LidAttributesCreateService($this->accountId, $this->msC);
        $serviceFieldsNames = [
            "lid",
        ];
        //вынести выше
        $config = Config::get("lidAttributes");
        $serviceFields = array_filter($config, fn($key)=> in_array($key, $serviceFieldsNames), ARRAY_FILTER_USE_KEY);
        //вынести выше
        $findOrCreateRes = $attributesS->findOrCreate($serviceFields, $isCreateOrder);
        if(isset($findOrCreateRes)){
            if(!$findOrCreateRes->status)
                return $findOrCreateRes;

        }
        //вынести выше
        //getCreatedAttribute
        $lidName = $serviceFields["lid"]->name;
        //ожидает ответа
        $valueName = $serviceFields["lid"]->values[0]->name;
        //вынести выше
        $updateValuesS = new UpdateValuesService($this->accountId, $this->msC);
        $customEntityS = new CustomEntityService($this->accountId, $this->msC);
        $agentUpdateS = new AgentUpdateLogicService($this->accountId, $this->msC);

        //updateAgentAttribute
        if($agentAttr == null){
            $agentUpdateRes = $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $valueName, $updateValuesS, $customEntityS);
            if(!$agentUpdateRes->status)
                return $agentUpdateRes;
            
        } else {
            $settedAttribute = array_filter($agentAttr, fn($value)=> $value->name == $lidName);
            $settedAttribute = array_shift($settedAttribute);
            $settedAttributeValueName = $settedAttribute->value->name;
            if($settedAttributeValueName == $valueName){
                if(!$isCreateOrder)
                    return $handlerS->createResponse(true, $isCreateOrder);
            } else{
                $agentUpdateRes = $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $valueName, $updateValuesS, $customEntityS);
                if(!$agentUpdateRes->status)
                    return $agentUpdateRes;
            }
        }

        //createOrder
        if($isCreateOrder){
            $orderAttrS = new CustomorderS($this->accountId, $this->msC);
            $orderAttrRes = $orderAttrS->getAllAttributes(true);
            if(!$orderAttrRes->status)
                return $orderAttrRes;
            $orderLidAttr = array_filter($orderAttrRes->data, fn($value)=> $value->name == $lidName);
            $orderAttr = array_shift($orderLidAttr);
            $bodyRes = $updateValuesS->dictionary($customEntityS, $orderAttr, $valueName);
            if(!$bodyRes->status)
                return $bodyRes;
            $organMeta = $handlerS->FormationMetaById("organization", "organization", $organId);
            $preparedOrganMeta = $handlerS->FormationMeta($organMeta);
            $preparedAgentMeta = $handlerS->FormationMeta($agent->meta);
            
            $customOrderRes = $customOrderS->createByAgentAndOrg($agentId, $preparedAgentMeta, $preparedOrganMeta, $responsible, $responsibleUuid, $bodyRes->data);
            return $customOrderRes;
        }

        return $handlerS->createResponse(true, $isCreateOrder);
        
    } 

    function updateAttributesIfNecessary($customerOrders){
        $agentUpdateS = new AgentUpdateLogicService($this->accountId, $this->msC);
        $orderUpdateS = new CustomerorderUpdateLogicService($this->accountId, $this->msC);
        $updateValuesS = new UpdateValuesService($this->accountId, $this->msC);
        $customEntityS = new CustomEntityService($this->accountId, $this->msC);
        $handlerS = new HandlerService();
        $serviceFieldsNames = [
            "lid",
        ];

        //вынести выше
        $config = Config::get("lidAttributes");
        $serviceFields = array_filter($config, fn($key)=> in_array($key, $serviceFieldsNames), ARRAY_FILTER_USE_KEY);
        $lidName = $serviceFields["lid"]->name;
        //ожидает ответа
        $waitAnswerValueName = $serviceFields["lid"]->values[0]->name;
        //отвеченный
        $answeredValueName = $serviceFields["lid"]->values[1]->name;
        
        //вынести выше
        foreach($customerOrders as $order){
            $agentId = $order->agent->id;
            $orderId = $order->id;
            $stateType = $order->state->stateType;
            $agentAttr = $order->agent->attributes;
            $orderAttr = $order->attributes;

            $nameAttr = "";
            if($stateType == "Successful")
                $nameAttr = $answeredValueName;
            else
                $nameAttr = $waitAnswerValueName;

            $settedAttribute = array_filter($agentAttr, fn($value)=> $value->name == $lidName);
            $settedAttribute = array_shift($settedAttribute);
            $settedAttributeValueName = $settedAttribute->value->name;
            if($settedAttributeValueName != $nameAttr){
                $agentUpdateRes = $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $nameAttr, $updateValuesS, $customEntityS);
                if(!$agentUpdateRes->status)
                    return $agentUpdateRes;
            }

            $settedAttribute = array_filter($orderAttr, fn($value)=> $value->name == $lidName);
            $settedAttribute = array_shift($settedAttribute);
            $settedAttributeValueName = $settedAttribute->value->name;
            

            if($settedAttributeValueName != $nameAttr){
                $agentUpdateRes = $orderUpdateS->orderUpdateLidAttribute($orderId, $lidName, $nameAttr, $updateValuesS, $customEntityS);
                if(!$agentUpdateRes->status)
                    return $agentUpdateRes;
            }
        }

        return $handlerS->createResponse(true, "+");
        
    }


}