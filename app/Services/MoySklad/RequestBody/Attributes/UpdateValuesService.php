<?php
namespace App\Services\MoySklad\RequestBody\Attributes;

use App\Clients\MoySklad;
use App\Exceptions\UpdateValuesException;
use App\Services\HandlerService;
use App\Services\Response;

class UpdateValuesService{

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->res = new Response();
        $this->accountId = $accountId;
    }

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