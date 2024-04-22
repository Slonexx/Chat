<?php
namespace App\Services\MoySklad;

use App\Clients\oldMoySklad;
use App\Services\Response;
use stdClass;

class AttributesService{

    private oldMoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId) {
        $this->msC = new oldMoySklad($accountId);
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function getAttrForEntities($services){
        $res = new Response();
        if(empty($entityType) || empty($entityId))
            return $this->res->error(
                [
                    $entityType,
                    $entityId
                ], 
            "Один или несколько параметров пусты");


    }
}