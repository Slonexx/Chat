<?php
namespace App\Services\MoySklad\Attributes;

use App\Clients\MoySklad;
use App\Services\Response;

class SalesreturnS {

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    private const ATTRIBUTES_URL_IDENTIFIER = "salesreturnMetadataAttributes";

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
        $this->res = new Response();
        $this->accountId = $accountId;
    }
    
    public function getAllAttributes($notEmpty = true){
        $resAttr = $this->msC->getAll(self::ATTRIBUTES_URL_IDENTIFIER);
        if(!$resAttr->status)
            return $resAttr->addMessage("Ошибка при получении аттрибутов возврата покупателя");
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
}