<?php
namespace App\Services\ChatApp;

use App\Clients\MoySklad;
use App\Services\HandlerService;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\Response;

class AgentMessengerHandler{

    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function telegram($phone, $username, $name, $attrMeta){
        $handlerS = new HandlerService();

        $addFValue = null;
        if($username)
            $addFValue = "@{$username}";
        else
            $addFValue = $name;
        $body = $handlerS->FormationAttribute($attrMeta, $addFValue);
        preg_match_all('/([a-zA-Z]+)/', $name, $matches);
        if(count($matches[1]) > 0)
            $body->name = "{$name} {$phone}";
        else
            $body->name = $name;
        $body->phone = $phone;
        $body->tags = ['chatapp', 'telegram'];
        
        $agentS = new CounterpartyService($this->accountId, $this->msC);
        return $agentS->create($body);
    }

    function whatsapp($phone, $id, $name, $attrMeta){
        $handlerS = new HandlerService();

        $body = $handlerS->FormationAttribute($attrMeta, $id);

        $pos = strpos($name, '@');
        if($pos == false)
            $body->name = "{$name} {$phone}";
        else{
            $body->name = $phone;

        }
        $body->phone = $phone;
        $body->tags = ['chatapp', 'whatsapp'];
        
        $agentS = new CounterpartyService($this->accountId, $this->msC);
        return $agentS->create($body);
    }

    function email($email, $attrMeta){
        $handlerS = new HandlerService();
        $body = $handlerS->FormationAttribute($attrMeta, $email);

        $body->name = $email;
        $body->tags = ['chatapp', 'email'];
        
        $agentS = new CounterpartyService($this->accountId, $this->msC);
        return $agentS->create($body);
    }

    function vk($name, $chatId, $attrMeta){
        $handlerS = new HandlerService();
        if(ctype_digit($chatId))
            $chatId = "id{$chatId}";
        $body = $handlerS->FormationAttribute($attrMeta, $chatId);

        $body->name = $name;
        $body->tags = ['chatapp', 'vk'];
        
        $agentS = new CounterpartyService($this->accountId, $this->msC);
        return $agentS->create($body);
    }

    function inst($name, $username, $attrMeta){
        $handlerS = new HandlerService();
        $body = $handlerS->FormationAttribute($attrMeta, "@{$username}");

        $body->name = $name;
        $body->tags = ['chatapp', 'instagram'];
        
        $agentS = new CounterpartyService($this->accountId, $this->msC);
        return $agentS->create($body);
    }

    function tg_bot($name, $username, $attrMeta){
        $handlerS = new HandlerService();

        $addFValue = null;
        if($username)
            $addFValue = "@{$username}";
        else
            $addFValue = $name;
        $body = $handlerS->FormationAttribute($attrMeta, $addFValue);
        $body->name = $name;
        $body->tags = ['chatapp', 'telegram_bot'];
        
        $agentS = new CounterpartyService($this->accountId, $this->msC);
        return $agentS->create($body);
    }

    function avito($name, $chatId, $attrMeta){
        if(empty($name) || empty($chatId))
            return $this->res->success("skip");
        $handlerS = new HandlerService();

        $body = $handlerS->FormationAttribute($attrMeta, $chatId);
        $body->name = $name;
        $body->tags = ['chatapp', 'avito'];
        
        $agentS = new CounterpartyService($this->accountId, $this->msC);
        return $agentS->create($body);
    }
}