<?php
namespace App\Services\MoySklad\RequestBody\Attributes;

use App\Exceptions\UpdateValuesException;
use App\Services\HandlerService;

class UpdateValuesService{
    /**
     * подготавливает тело для обновления значения аттрибута типа справочник
     */
    function dictionary($customEntityS, $entityAttr, $valueName){
        $handlerS = new HandlerService();
        $entityAttrHref = $entityAttr->customEntityMeta->href;
        $entityAttrMeta = $entityAttr->meta;
        $attrName = $entityAttr->name;

        $customEntityDictionaryId = basename($entityAttrHref);
        
        $res = $customEntityS->getById($customEntityDictionaryId);

        $valueNames = $res->data->rows;

        $findedValue = array_filter($valueNames, fn($value)=> $value->name == $valueName);

        if(count($findedValue) == 0)
            throw new UpdateValuesException("Значение {$valueName} в справочнике {$attrName} в МС не найдено");

        $findedValue = array_shift($findedValue);
        $valueHref = $findedValue->meta->href;

        $valueObject = (object)[
            "href" => $valueHref,
            "type" => "customentity"
        ];

        $preparedValue = $handlerS->FormationMeta($valueObject);

        $body = $handlerS->FormationAttribute($entityAttrMeta, $preparedValue);

        return $body;
    }

    
}