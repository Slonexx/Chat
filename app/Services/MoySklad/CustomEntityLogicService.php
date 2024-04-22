<?php
namespace App\Services\MoySklad;

use App\Clients\oldMoySklad;
use App\Services\MoySklad\Entities\CustomEntityService;
use App\Services\HandlerService;

class CustomEntityLogicService {
    private oldMoySklad $msC;

    private string $accountId;

    private HandlerService $handlerS;

    function __construct($accountId, oldMoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new oldMoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->handlerS = new HandlerService();
        $this->accountId = $accountId;
    }

    function findOrCreate($entityTypeAttributes, $attributes){
        $body = [];
        $customEntityS = new CustomEntityService($this->accountId, $this->msC);
        //можно сюда вынести получение всех справочников
        foreach($attributes as $addField) {
            $res = $customEntityS->tryToFind($addField);
            if(isset($res)){
                if(!$res->status)
                    return $res;

                $customentityId = $res->data;
               
            } else {
                $res = $customEntityS->createDictionary($addField);
                if(!$res->status)
                    return $res;
    
                $customentityId = $res->data->id;
                $res = $customEntityS->createValuesMoreThan1000($addField, $customentityId);
                if(!$res->status)
                    return $res;
            }

            $res = $customEntityS->setBody($addField, $customentityId);
            if(!$res->status)
                return $res;

            $body[] = $res->data;
        }
        
        return $customEntityS->createAttribute($entityTypeAttributes, $body);
    }
}