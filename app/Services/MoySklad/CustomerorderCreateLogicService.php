<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Exceptions\MsException;
use App\Services\HandlerService;
use App\Services\HTTPResponseHandler;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\MoySklad\Entities\CustomOrderService;
use App\Services\MsFilterService;
use App\Services\Response;
use GuzzleHttp\Exception\RequestException;
use stdClass;

class CustomerorderCreateLogicService{

    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function findFirst($firstCount, $agentHref){
        $filterS = new MsFilterService();
        $urlWithFilter = $filterS->prepareUrlWithParam("customerorder", "agent", $agentHref);
        $prepUrl = $urlWithFilter . "&limit={$firstCount}&expand=state,agent";
        $resHandler = new HTTPResponseHandler();
        try{
            $firstOrdersRes = $this->msC->get($prepUrl);
            return $resHandler->handleOK($firstOrdersRes, "поиск $firstCount заказов завершился успешно");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при поиске $firstCount заказов по агенту|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при обновлении контрагента", previous:$e);
            }
        }
    }

    function checkStateTypeEqRegular(array $customerOrders){
        foreach($customerOrders as $item){
            $stateType = $item->state->stateType;
            if($stateType == "Regular")
                return true;
        }
        return false;
    }

    /**
     * @throws MsException
     */
    function createBySettings($agentOwnerId, $preparedMetas, $responsible, $responsibleUuid, $attributes){
        $customerOrderS = new CustomOrderService($this->accountId, $this->msC);
        $body = new stdClass();
        $body->agent = $preparedMetas->agent;
        $body->organization = $preparedMetas->organization;
        $body->attributes = $attributes->attributes;

        $organizationAccount = $preparedMetas->organizationAccount ?? false;
        if($organizationAccount)
            $body->organizationAccount = $organizationAccount;

        $project = $preparedMetas->project ?? false;
        if($project)
            $body->project = $project;

        $salesChannel = $preparedMetas->salesChannel ?? false;
        if($salesChannel)
            $body->salesChannel = $salesChannel;

        $state = $preparedMetas->state ?? false;
        if($state)
            $body->state = $state;

        $handlerS = new HandlerService();

        if ($responsibleUuid !== null) {
            switch($responsible){
                case "1":
                    $employeeMeta = $handlerS->FormationMetaById("employee", "employee", $responsibleUuid);
                    $preparedEmployeeMeta = $handlerS->FormationMeta($employeeMeta);
                    $body->owner = $preparedEmployeeMeta;
                    break;
                case "2":
                    if(isset($agentOwnerId)){
                        $employeeMeta = $handlerS->FormationMetaById("employee", "employee", $agentOwnerId);
                        $preparedEmployeeMeta = $handlerS->FormationMeta($employeeMeta);
                        $body->owner = $preparedEmployeeMeta;
                    }
                    break;
                default:
                    break;
            }
        }
        $orderRes = $customerOrderS->create($body);
        return $orderRes->data;

    }
}
