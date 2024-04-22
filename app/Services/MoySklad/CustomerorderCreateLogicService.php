<?php
namespace App\Services\MoySklad;

use App\Clients\oldMoySklad;
use App\Services\HandlerService;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\MoySklad\Entities\CustomOrderService;
use App\Services\MsFilterService;
use App\Services\Response;
use stdClass;

class CustomerorderCreateLogicService{

    private oldMoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, oldMoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new oldMoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function findFirst($firstCount, $agentHref){
        $filterS = new MsFilterService();
        $urlWithFilter = $filterS->prepareUrlWithParam("customerorder", "agent", $agentHref);
        $prepUrl = $urlWithFilter . "&limit={$firstCount}&expand=state,agent";
        $firstOrdersRes = $this->msC->getByUrl($prepUrl);
        if(!$firstOrdersRes->status)
            return $firstOrdersRes->addMessage("Не удалось найти первые {$firstCount} заказов");
        else
            return $this->res->success($firstOrdersRes->data->rows);
    }

    function checkStateTypeEqRegular(array $customerOrders){
        foreach($customerOrders as $item){
            $stateType = $item->state->stateType;
            if($stateType == "Regular")
                return true;
        }
        return false;
    }

    function createByAgentAndOrg($agentId, $agentMeta, $orgMeta, $responsible, $responsibleUuid, $attributes){
        $customerOrderS = new CustomOrderService($this->accountId, $this->msC);
        $body = new stdClass();
        $body->agent = $agentMeta;
        $body->organization = $orgMeta;
        $body->attributes = $attributes->attributes;

        $handlerS = new HandlerService();
        $employeeMeta = $handlerS->FormationMetaById("employee", "employee", $responsibleUuid);
        $preparedEmployeeMeta = $handlerS->FormationMeta($employeeMeta);
        switch($responsible){
            case "1":
                $body->owner = $preparedEmployeeMeta;
                break;
            case "2":

                $bodyForChangeAgent = new stdClass();
                $bodyForChangeAgent->owner = $preparedEmployeeMeta;
                $agentS = new CounterpartyService($this->accountId, $this->msC);
                $agentUpdateRes = $agentS->update($agentId, $bodyForChangeAgent, "Ошибка при обновлении контрагента во время создания заказа");
                if(!$agentUpdateRes->status)
                    return $agentUpdateRes;
                
                $body->owner = $preparedEmployeeMeta;
                
                break;
            default:
                break;
        }
        $customerOrderCreateRes = $customerOrderS->create($body);
        if(!$customerOrderCreateRes->status)
            return $this->res->error($customerOrderCreateRes->data, "Ошибка при создании заказа покупателя");
        else
            return $customerOrderCreateRes;
        
    }
}