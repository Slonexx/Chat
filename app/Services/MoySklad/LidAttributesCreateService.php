<?php
namespace App\Services\MoySklad;

use App\Clients\oldMoySklad;
use App\Services\MoySklad\Attributes\oldCounterpartyS;
use App\Services\MoySklad\Attributes\CustomorderS;
use App\Services\Response;
use Illuminate\Support\Facades\Config;

class LidAttributesCreateService{
    private oldMoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, oldMoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new oldMoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }
    /**
     * @param object[] $attributes lidAttributes
     * @param bool $isCreateOrder logic var
     */
    function findOrCreate(array $attributes, bool $isCreateOrder){
        
        $agentAttributesS = new oldCounterpartyS($this->accountId, $this->msC);
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