<?php
namespace App\Services\Intgr;

use App\Clients\MoySkladIntgr;
use App\Exceptions\CustomEntityException;
use App\Exceptions\CustomEntityLogicException;
use App\Services\Intgr\Entities\CustomEntityService;
use App\Services\HandlerService;

class CustomEntityLogicService {
    private MoySkladIntgr $msC;

    private HandlerService $handlerS;

    function __construct(MoySkladIntgr $MoySklad) {
        $this->msC = $MoySklad;
        $this->handlerS = new HandlerService();
    }

    function findOrCreate($entityTypeAttributes, $attributes){
        $body = [];
        $customEntityS = new CustomEntityService($this->msC);
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