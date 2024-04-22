<?php
namespace App\Services\MoySklad\Attributes;

use App\Clients\oldMoySklad;
use App\Services\HandlerService;
use App\Services\Response;

class CustomorderS {

    private oldMoySklad $msC;

    public string $accountId;

    private Response $res;

    private const ATTRIBUTES_URL_IDENTIFIER = "customerorderMetadataAttributes";

    function __construct($accountId) {
        $this->msC = new oldMoySklad($accountId);
        $this->res = new Response();
        $this->accountId = $accountId;
    }
    
    public function getAllAttributes($notEmpty = true){
        $resAttr = $this->msC->getAll(self::ATTRIBUTES_URL_IDENTIFIER);
        if(!$resAttr->status)
            return $resAttr->addMessage("Ошибка при получении аттрибутов заказа покупателя");
        else {
            $attributes = $resAttr->data->rows;
            $res = new Response();
            if($notEmpty && count($attributes) == 0){
                return $res->error($attributes, "Аттрибуты не найдены");
            } else {
                return $res->success($attributes);
            }
        }
            
    }

    /**
     * возращает аттрибуты, которых нет в моём складе
     */
    function checkCreateArrayAttributes($attributes){
        $handlerS = new HandlerService();
        $attributesRes = $this->getAllAttributes(false);
        if(!$attributesRes->status)
            return $attributesRes;
        $attrubutesForCreating = [];

        foreach($attributes as $addFieldMs){
            $findedAttribute = array_filter($attributesRes->data, fn($attribute)=> $attribute->name == $addFieldMs->name);
            if(count($findedAttribute) == 0)
                $attrubutesForCreating[] = $addFieldMs;
        }
        if(empty($attrubutesForCreating))
            return null;
        else{
            return $handlerS->createResponse(true, $attrubutesForCreating);
        }
    }
}