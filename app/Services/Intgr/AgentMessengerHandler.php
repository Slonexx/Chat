<?php
namespace App\Services\Intgr;

use App\Clients\MoySkladIntgr;
use App\Services\HandlerService;
use App\Services\Intgr\Attributes\CounterpartyS;
use App\Services\Intgr\Entities\CounterpartyService;
use App\Services\Intgr\Entities\CustomEntityService;
use App\Services\Intgr\LidAttributesCreateService;
use App\Services\MoySklad\RequestBody\Attributes\UpdateValuesService;
use App\Services\Response;
use Illuminate\Support\Facades\Config;

class AgentMessengerHandler{

    private MoySkladIntgr $msC;

    private Response $res;

    function __construct(MoySkladIntgr $MoySklad) {
        $this->msC = $MoySklad;
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
        
        $agentS = new CounterpartyService($this->msC);
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
        
        $agentS = new CounterpartyService($this->msC);
        return $agentS->create($body);
    }

    function email($email, $attrMeta){
        $handlerS = new HandlerService();
        $body = $handlerS->FormationAttribute($attrMeta, $email);

        $body->name = $email;
        $body->tags = ['chatapp', 'email'];

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
        
        $agentS = new CounterpartyService($this->msC);
        return $agentS->create($body);
    }

    function vk($name, $chatId, $attrMeta){
        $handlerS = new HandlerService();
        if(ctype_digit($chatId))
            $chatId = "id{$chatId}";
        $body = $handlerS->FormationAttribute($attrMeta, $chatId);

        $body->name = $name;
        $body->tags = ['chatapp', 'vk'];

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
        
        $agentS = new CounterpartyService($this->msC);
        return $agentS->create($body);
    }

    function inst($name, $username, $attrMeta){
        $handlerS = new HandlerService();
        $body = $handlerS->FormationAttribute($attrMeta, "@{$username}");

        $body->name = $name;
        $body->tags = ['chatapp', 'instagram'];

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
        
        $agentS = new CounterpartyService($this->msC);
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
        
        $agentS = new CounterpartyService($this->msC);
        return $agentS->create($body);
    }

    function avito($name, $chatId, $attrMeta){
        if(empty($name) || empty($chatId))
            return $this->res->success("skip");
        $handlerS = new HandlerService();

        $body = $handlerS->FormationAttribute($attrMeta, $chatId);
        $body->name = $name;
        $body->tags = ['chatapp', 'avito'];

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
        
        $agentS = new CounterpartyService($this->msC);
        return $agentS->create($body);
    }
}