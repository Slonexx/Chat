<?php
namespace App\Services\Intgr;

use App\Clients\MoySkladIntgr;
use App\Services\Intgr\Attributes\CustomorderS;
use App\Services\Intgr\Entities\CustomEntityService;
use App\Services\Intgr\Entities\CustomOrderService;
use App\Services\MoySklad\RequestBody\Attributes\UpdateValuesService;

class CustomerorderUpdateLogicService{

    private MoySkladIntgr $msC;

    function __construct(MoySkladIntgr $MoySklad) {
        $this->msC = $MoySklad;
    }

    function orderUpdateLidAttribute($orderId, $lidName, $valueName, UpdateValuesService $updateValuesS, CustomEntityService $customEntityS){
        $orderAttrS = new CustomorderS($this->msC);
        $orderAttrRes = $orderAttrS->getAllAttributes(true);
        $orderAllAttributes = $orderAttrRes->data;
        $orderLidAttr = array_filter($orderAllAttributes, fn($value)=> $value->name == $lidName);
        $orderAttr = array_shift($orderLidAttr);

        $orderS = new CustomOrderService($this->msC);
        
        $bodyForOrderUpdate = $updateValuesS->dictionary($customEntityS, $orderAttr, $valueName);

        $orderS->update($orderId, $bodyForOrderUpdate);
    }


}