<?php
namespace App\Services\MoySklad;

use App\Clients\oldMoySklad;
use App\Services\MoySklad\Attributes\CustomorderS;
use App\Services\MoySklad\Entities\CustomEntityService;
use App\Services\MoySklad\Entities\CustomOrderService;
use App\Services\MoySklad\RequestBody\Attributes\UpdateValuesService;
use App\Services\Response;

class CustomerorderUpdateLogicService{

    private oldMoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, oldMoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new oldMoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function orderUpdateLidAttribute($orderId, $lidName, $valueName, UpdateValuesService $updateValuesS, CustomEntityService $customEntityS){
        $orderAttrS = new CustomorderS($this->accountId, $this->msC);
        $orderAttrRes = $orderAttrS->getAllAttributes(true);
        if(!$orderAttrRes->status)
            return $orderAttrRes;
        $orderAllAttributes = $orderAttrRes->data;
        $orderLidAttr = array_filter($orderAllAttributes, fn($value)=> $value->name == $lidName);
        $orderAttr = array_shift($orderLidAttr);

        $orderS = new CustomOrderService($this->accountId, $this->msC);
        
        $bodyRes = $updateValuesS->dictionary($customEntityS, $orderAttr, $valueName);
        if(!$bodyRes->status)
            return $bodyRes;

        $bodyForOrderUpdate = $bodyRes->data;
        return $orderS->update($orderId, $bodyForOrderUpdate, "Ошибка при обновлении заказа во время создания заказа");
    }


}