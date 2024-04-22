<?php
namespace App\Services\ChatApp;

use App\Clients\oldMoySklad;
use App\Services\MoySklad\Attributes\CounterpartyS;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\Response;

class AgentFindService{

    private oldMoySklad $msC;

    private string $accountId;

    private Response $res;

    private string $nameError = "Ошибка при поиске контрагента по имени";

    private string $phoneError = "Ошибка при поиске контрагента по номеру";

    private string $addFieldError = "Ошибка при поиске контрагента по доп полю";

    function __construct($accountId, oldMoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new oldMoySklad($accountId);
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
        if(!$agentByNameRes->status)
            return $agentByNameRes;
        if(count($agentByNameRes->data) == 0){
            $agentByPhoneRes = $counterpartyS->getByParam("phone", $phone, $this->phoneError);
            if(!$agentByPhoneRes->status)
                return $agentByPhoneRes;
            if(count($agentByPhoneRes->data) == 0){
                $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
                $agentByTgRes = $counterpartyAttributeS->getByAttribute($attribute_id, "@{$addF}", $this->addFieldError);
                if(!$agentByTgRes->status)
                    return $agentByTgRes;
                else
                    return $this->res->success($agentByTgRes->data);
            } else
                return $this->res->success($agentByPhoneRes->data);
        } else
            return $this->res->success($agentByNameRes->data);
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
        if(!$agentByNameRes->status)
            return $agentByNameRes;
        if(count($agentByNameRes->data) == 0){
            $agentByPhoneRes = $counterpartyS->getByParam("phone", $phone, $this->phoneError);
            if(!$agentByPhoneRes->status)
                return $agentByPhoneRes;
            if(count($agentByPhoneRes->data) == 0){
                $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
                $agentByWpRes = $counterpartyAttributeS->getByAttribute($attribute_id, $addF, $this->addFieldError);
                if(!$agentByWpRes->status)
                    return $agentByWpRes;
                else
                    return $this->res->success($agentByWpRes->data);
            } else
                return $this->res->success($agentByPhoneRes->data);
        } else
            return $this->res->success($agentByNameRes->data);
    }

    function email($email, $attribute_id){
        $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
        $agentByEmailRes = $counterpartyAttributeS->getByAttribute($attribute_id, $email, $this->addFieldError);
        if(!$agentByEmailRes->status)
            return $agentByEmailRes;
        else
            return $this->res->success($agentByEmailRes->data);
        
    }

    function vk($chatId, $attribute_id){
        if(ctype_digit($chatId))
            $chatId = "id{$chatId}";
        $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
        $agentByEmailRes = $counterpartyAttributeS->getByAttribute($attribute_id, $chatId, $this->addFieldError);
        if(!$agentByEmailRes->status)
            return $agentByEmailRes;
        else
            return $this->res->success($agentByEmailRes->data);
    }

    function inst($username, $attribute_id){
        $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
        $agentByEmailRes = $counterpartyAttributeS->getByAttribute($attribute_id, "@{$username}", $this->addFieldError);
        if(!$agentByEmailRes->status)
            return $agentByEmailRes;
        else
            return $this->res->success($agentByEmailRes->data);
    }

    function tg_bot($username, $attribute_id){
        $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
        $agentByTgRes = $counterpartyAttributeS->getByAttribute($attribute_id, "@{$username}", $this->addFieldError);
        if(!$agentByTgRes->status)
            return $agentByTgRes;
        else
            return $this->res->success($agentByTgRes->data);
    }

    function avito($chatId, $attribute_id){
        $counterpartyAttributeS = new CounterpartyS($this->accountId, $this->msC);
        $agentByTgRes = $counterpartyAttributeS->getByAttribute($attribute_id, $chatId, $this->addFieldError);
        if(!$agentByTgRes->status)
            return $agentByTgRes;
        else
            return $this->res->success($agentByTgRes->data);
    }

}