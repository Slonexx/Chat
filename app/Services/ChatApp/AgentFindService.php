<?php
namespace App\Services\ChatApp;

use App\Clients\MoySklad;
use App\Services\MoySklad\Attributes\CounterpartyS;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\Response;

class AgentFindService{

    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    private string $nameError = "Ошибка при поиске контрагента по имени";

    private string $phoneError = "Ошибка при поиске контрагента по номеру";

    private string $addFieldError = "Ошибка при поиске контрагента по доп полю";

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function telegram($phone, $name, $addF, $attribute_id){
        $counterpartyS = new CounterpartyService($this->accountId, $this->msC);
        preg_match_all('/([a-zA-Z]+)/', $name, $matches);
        if(count($matches[1]) > 0)
            $nameForFinding = "{$name} {$phone}";
        else{
            $nameForFinding = $phone;

        }
        $agentByNameRes = $counterpartyS->getByParam("name", $nameForFinding, $this->nameError);
        if(count($agentByNameRes->data->rows) == 0){
            $agentByPhoneRes = $counterpartyS->getByParam("phone", $phone, $this->phoneError);
            if(count($agentByPhoneRes->data->rows) == 0){
                $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
                $agentByTgRes = $counterpartyAttributeS->getByAttribute($attribute_id, "@{$addF}", $this->addFieldError);
                return $agentByTgRes;
            } else
                return $agentByPhoneRes;
        } else
            return $agentByNameRes;
    }

    function whatsapp($phone, $name, $addF, $attribute_id){
        $counterpartyS = new CounterpartyService($this->accountId, $this->msC);
        $pos = strpos($name, '@');
        if($pos == false)
            $nameForFinding = "{$name} $phone";
        else{
            $nameForFinding = $phone;

        }
        $agentByNameRes = $counterpartyS->getByParam("name", $nameForFinding, $this->nameError);
        if(count($agentByNameRes->data->rows) == 0){
            $agentByPhoneRes = $counterpartyS->getByParam("phone", $phone, $this->phoneError);
            if(count($agentByPhoneRes->data->rows) == 0){
                $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
                $agentByWpRes = $counterpartyAttributeS->getByAttribute($attribute_id, $addF, $this->addFieldError);
                return $agentByWpRes;
            } else
                return $agentByPhoneRes;
        } else
            return $agentByNameRes;
    }

    function email($email, $attribute_id){
        $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
        $agentByEmailRes = $counterpartyAttributeS->getByAttribute($attribute_id, $email, $this->addFieldError);
        return $agentByEmailRes;
        
    }

    function vk($chatId, $attribute_id){
        if(ctype_digit($chatId))
            $chatId = "id{$chatId}";
        $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
        $agentByEmailRes = $counterpartyAttributeS->getByAttribute($attribute_id, $chatId, $this->addFieldError);
        return $agentByEmailRes;
    }

    function inst($username, $attribute_id){
        $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
        $agentByEmailRes = $counterpartyAttributeS->getByAttribute($attribute_id, "@{$username}", $this->addFieldError);
        return $agentByEmailRes;
    }

    function tg_bot($username, $attribute_id){
        $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
        $agentByTgRes = $counterpartyAttributeS->getByAttribute($attribute_id, "@{$username}", $this->addFieldError);
        return $agentByTgRes;
    }

    function avito($chatId, $attribute_id){
        $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
        $agentByTgRes = $counterpartyAttributeS->getByAttribute($attribute_id, $chatId, $this->addFieldError);
        return $agentByTgRes;
    }

}