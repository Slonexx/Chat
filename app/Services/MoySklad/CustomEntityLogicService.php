<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Exceptions\CustomEntityException;
use App\Exceptions\CustomEntityLogicException;
use App\Services\MoySklad\Entities\CustomEntityService;
use App\Services\HandlerService;

class CustomEntityLogicService {
    private MoySklad $msC;

    private string $accountId;

    private HandlerService $handlerS;

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->handlerS = new HandlerService();
        $this->accountId = $accountId;
    }

    function findOrCreate($entityTypeAttributes, $attributes){
        $body = [];
        $customEntityS = new CustomEntityService($this->accountId, $this->msC);
        $dictionaries = $customEntityS->getAll();
        foreach($attributes as $addField) {
            $dictionaryName = $addField->name ?? false;
            $dictionaryValues = $addField->values ?? [];
            try{
                $findRes = $customEntityS->tryToFind($dictionaryName, $dictionaries);
                if($findRes != null){
                    $customentityId = $findRes->data;
                } else {
                    $createDicRes = $customEntityS->createDictionary($dictionaryName);
        
                    $customentityId = $createDicRes->data->id;
                    $customEntityS->createValuesMoreThan1000($dictionaryValues, $customentityId);
                }
    
                $res = $customEntityS->setBody($addField, $customentityId);
                $body[] = (object) $res;
    
            } catch(CustomEntityException $e){
                throw new CustomEntityLogicException("Не удалось найти или создать аттрибут:$dictionaryName", previous:$e);
            }
            
        }
        
        return $customEntityS->createAttribute($entityTypeAttributes, $body);
    }
}