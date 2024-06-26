<?php
namespace App\Services\MoySklad\Attributes;

use App\Clients\oldMoySklad;
use App\Services\Response;

class DemandS {

    private oldMoySklad $msC;

    public string $accountId;

    private Response $res;

    private const ATTRIBUTES_URL_IDENTIFIER = "demandMetadataAttributes";

    function __construct($accountId) {
        $this->msC = new oldMoySklad($accountId);
        $this->res = new Response();
        $this->accountId = $accountId;
    }
    
    public function getAllAttributes($notEmpty = true){
        $resAttr = $this->msC->getAll(self::ATTRIBUTES_URL_IDENTIFIER);
        if(!$resAttr->status)
            return $resAttr->addMessage("Ошибка при получении аттрибутов отгрузки");
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