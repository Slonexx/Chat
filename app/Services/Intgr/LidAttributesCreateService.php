<?php
namespace App\Services\Intgr;

use App\Clients\MoySkladIntgr;
use App\Services\Intgr\Attributes\CounterpartyS;
use App\Services\Intgr\Attributes\CustomorderS;
use App\Services\Response;

class LidAttributesCreateService{
    private MoySkladIntgr $msC;

    private Response $res;

    function __construct(MoySkladIntgr $MoySklad) {
        $this->msC = $MoySklad;
        $this->res = new Response();
    }
    /**
     * @param object[] $attributes lidAttributes
     * @param bool $isCreateOrder logic var
     */
    function findOrCreate(array $attributes, bool $isCreateOrder){
        
        $agentAttributesS = new CounterpartyS($this->msC);
        //find add f.
        $customEntityS = new CustomEntityLogicService($this->msC);
        $agentArrayToCreate = $agentAttributesS->checkCreateArrayAttributes($attributes);
        if(!empty($agentArrayToCreate))
            $customEntityS->findOrCreate("agentMetadataAttributes", $agentArrayToCreate);

        if($isCreateOrder){
            $orderAttributesS = new CustomorderS($this->msC);
            //find add f.
            $orderArrayToCreate = $orderAttributesS->checkCreateArrayAttributes($attributes);
            if(!empty($orderArrayToCreate))
                $customEntityS->findOrCreate("customerorderMetadataAttributes", $orderArrayToCreate);
        }

        
    }
}