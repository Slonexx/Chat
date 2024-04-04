<?php
namespace App\Services\ChatApp;

use App\Clients\MoySklad;
use App\Clients\newClient;
use App\Models\ChatappEmployee;
use App\Models\employeeModel;
use App\Models\MainSettings;
use App\Models\Templates;
use App\Services\HandlerService;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\MoySklad\Entities\CustomOrderService;
use App\Services\MoySklad\Entities\DemandService;
use App\Services\MoySklad\Entities\InvoiceoutService;
use App\Services\MoySklad\Entities\SalesReturnService;
use App\Services\MoySklad\TemplateService;
use App\Services\Response;
use GuzzleHttp\Exception\BadResponseException;
use stdClass;

class AgentFindService{

    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function telegram($phone, $name, $addF, $attribute_id){
        $counterpartyS = new CounterpartyService($this->accountId);
        preg_match_all('/([a-zA-Z]+)/', $name, $matches);
        if(count($matches[1]) > 0)
            $nameForFinding = "{$name} {$phone}";
        else{
            $nameForFinding = $phone;

        }
        $agentByPhoneRes = $counterpartyS->getByPhone($nameForFinding, $phone, $addF, $attribute_id);
        return $agentByPhoneRes;
    }

    function whatsapp($phone, $name, $addF, $attribute_id){
        $counterpartyS = new CounterpartyService($this->accountId);
        $pos = strpos($name, '@');
        if($pos == false)
            $nameForFinding = "{$name} $phone";
        else{
            $nameForFinding = $phone;

        }
        $agentByPhoneRes = $counterpartyS->getByPhone($nameForFinding, $phone, $addF, $attribute_id);
        return $agentByPhoneRes;
    }

}