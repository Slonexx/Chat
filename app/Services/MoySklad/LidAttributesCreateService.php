<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Services\MoySklad\Attributes\CounterpartyS;
use App\Services\MoySklad\Attributes\CustomorderS;
use App\Services\Response;
use Illuminate\Support\Facades\Config;

class LidAttributesCreateService{
    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }
    /**
     * @param object[] $attributes lidAttributes
     * @param bool $isCreateOrder logic var
     */
    function findOrCreate(array $attributes, bool $isCreateOrder){
        
        $agentAttributesS = new CounterpartyS($this->accountId, $this->msC);
        //find add f.
        $customEntityS = new CustomEntityLogicService($this->accountId, $this->msC);
        $agentArrayToCreate = $agentAttributesS->checkCreateArrayAttributes($attributes);
        if(!empty($agentArrayToCreate))
            $customEntityS->findOrCreate("agentMetadataAttributes", $agentArrayToCreate);

        if($isCreateOrder){
            $orderAttributesS = new CustomorderS($this->accountId, $this->msC);
            //find add f.
            $orderArrayToCreate = $orderAttributesS->checkCreateArrayAttributes($attributes);
            if(!empty($orderArrayToCreate))
                $customEntityS->findOrCreate("customerorderMetadataAttributes", $orderArrayToCreate);
        }

        
    }
}