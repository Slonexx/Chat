<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Exceptions\AgentControllerLogicException;
use App\Services\HandlerService;
use App\Services\MoySklad\Attributes\CustomorderS;
use App\Services\MoySklad\Entities\CustomEntityService;
use App\Services\MoySklad\RequestBody\Attributes\UpdateValuesService;
use App\Services\Response;
use Error;
use Exception;
use Illuminate\Support\Facades\Config;

class AgentControllerLogicService{

    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function createOrderAndAttributes($organId, $agent, CustomerorderCreateLogicService $customOrderS, $responsible, $responsibleUuid, $isCreateOrder){
        $handlerS = new HandlerService();
        //agentId
        $agentHref = $agent->meta->href;
        $agentId = basename($agentHref);
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
        $attributesS->findOrCreate($serviceFields, $isCreateOrder);
        //вынести выше
        //getCreatedAttribute
        $lidName = $serviceFields["lid"]->name;
        //ожидает ответа
        $valueName = $serviceFields["lid"]->values[0]->name;
        //вынести выше
        $updateValuesS = new UpdateValuesService($this->accountId, $this->msC);
        $customEntityS = new CustomEntityService($this->accountId, $this->msC);
        $agentUpdateS = new AgentUpdateLogicService($this->accountId, $this->msC);

        try{
            //updateAgentAttribute
            if($agentAttr == null){
                $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $valueName, $updateValuesS, $customEntityS);
            } else {
                $settedAttribute = array_filter($agentAttr, fn($value)=> $value->name == $lidName);
                if(empty($settedAttribute))
                    $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $valueName, $updateValuesS, $customEntityS);
                else {
                    $settedAttribute = array_shift($settedAttribute);
                    $settedAttributeValueName = $settedAttribute->value->name;
                    if($settedAttributeValueName != $valueName){
                        $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $valueName, $updateValuesS, $customEntityS);
                    }
                }
                
            }

        } catch(Exception | Error $e){
            throw new AgentControllerLogicException("Ошибка при обновлении контрагента во время создания заказа", 1, $e);
        }

        try{
            //createOrder
            if($isCreateOrder){
                $orderAttrS = new CustomorderS($this->accountId, $this->msC);
                $orderAttrRes = $orderAttrS->getAllAttributes(true);
                $orderLidAttr = array_filter($orderAttrRes->data, fn($value)=> $value->name == $lidName);
                $orderAttr = array_shift($orderLidAttr);
                $body = $updateValuesS->dictionary($customEntityS, $orderAttr, $valueName);
                $organMeta = $handlerS->FormationMetaById("organization", "organization", $organId);
                $preparedOrganMeta = $handlerS->FormationMeta($organMeta);
                $preparedAgentMeta = $handlerS->FormationMeta($agent->meta);
                
                $customOrderS->createByAgentAndOrg($agentId, $preparedAgentMeta, $preparedOrganMeta, $responsible, $responsibleUuid, $body);
            }

        } catch(Exception | Error $e){
            throw new AgentControllerLogicException("Ошибка во время создания заказа", 2, $e);
        }
        
    } 

    function updateAttributesIfNecessary($customerOrders){
        $agentUpdateS = new AgentUpdateLogicService($this->accountId, $this->msC);
        $orderUpdateS = new CustomerorderUpdateLogicService($this->accountId, $this->msC);
        $updateValuesS = new UpdateValuesService($this->accountId, $this->msC);
        $customEntityS = new CustomEntityService($this->accountId, $this->msC);
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

            try{
                $settedAttribute = array_filter($agentAttr, fn($value)=> $value->name == $lidName);
                $settedAttribute = array_shift($settedAttribute);
                $settedAttributeValueName = $settedAttribute->value->name;
                if($settedAttributeValueName != $nameAttr){
                    $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $nameAttr, $updateValuesS, $customEntityS);
                }
            } catch(Exception | Error $e){
                throw new AgentControllerLogicException("Ошибка при обновлении контрагента во время создания заказа(find RegularStateType)", 1, $e);
            }

            $settedAttribute = array_filter($orderAttr, fn($value)=> $value->name == $lidName);
            $settedAttribute = array_shift($settedAttribute);
            $settedAttributeValueName = $settedAttribute->value->name;
            
            try{
                if($settedAttributeValueName != $nameAttr){
                    $orderUpdateS->orderUpdateLidAttribute($orderId, $lidName, $nameAttr, $updateValuesS, $customEntityS);
                }
            } catch(Exception | Error $e){
                throw new AgentControllerLogicException("Ошибка во время обновления доп.поля lid в заказе", 2, $e);
            }
        }
        
    }


}