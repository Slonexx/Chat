<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Services\MoySklad\Attributes\CounterpartyS;
use App\Services\MoySklad\Attributes\CustomorderS;
use App\Services\Response;

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
        $createCustomEntityRes = $agentAttributesS->checkCreateArrayAttributes($attributes);
        if(isset($createCustomEntityRes)){
            if(!$createCustomEntityRes->status) {
                return $createCustomEntityRes;
            }
            $customEntityS->findOrCreate("agentMetadataAttributes", $createCustomEntityRes->data);

        }

        if($isCreateOrder){
            $orderAttributesS = new CustomorderS($this->accountId, $this->msC);
            //find add f.
            $createCustomEntityRes = $orderAttributesS->checkCreateArrayAttributes($attributes);
            if(isset($createCustomEntityRes)){
                if(!$createCustomEntityRes->status) {
                    return $createCustomEntityRes;
                }
                $customEntityS->findOrCreate("customerorderMetadataAttributes", $createCustomEntityRes->data);

            }
        }

        
    }
}