<?php
namespace App\Services\MoySklad\RequestBody\Attributes;

use App\Clients\MoySklad;
use App\Services\HandlerService;
use App\Services\Response;

class UpdateValuesService{

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    private const URL_IDENTIFIER = "agent";

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->res = new Response();
        $this->accountId = $accountId;
    }
    
    function dictionary($customEntityS, $entityAttr, $valueName){
        $handlerS = new HandlerService();
        $entityAttrHref = $entityAttr->customEntityMeta->href;
        $entityAttrMeta = $entityAttr->meta;
        $attrName = $entityAttr->name;

        $customEntityDictionaryParsedUrl = explode("/", $entityAttrHref);
        $customEntityDictionaryId = array_pop($customEntityDictionaryParsedUrl);

        $res = $customEntityS->getById($customEntityDictionaryId);
        if(!$res->status)
            return $handlerS->createResponse(false, $res->data, true, "Ошибка при получении customentity");

        $valueNames = $res->data->rows;

        $findedValue = array_filter($valueNames, fn($value)=> $value->name == $valueName);

        if(count($findedValue) == 0)
            return $handlerS->createResponse(false, $findedValue, true, "Значение {$valueName} в {$attrName} в МС не найдены");

        $findedValue = array_shift($findedValue);
        $valueHref = $findedValue->meta->href;

        $valueObject = (object)[
            "href" => $valueHref,
            "type" => "customentity"
        ];

        $preparedValue = $handlerS->FormationMeta($valueObject);

        $body = $handlerS->FormationAttribute($entityAttrMeta, $preparedValue);

        return $handlerS->createResponse(true, $body);
    }

    
}