<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Services\Response;
use stdClass;

class AddFieldsService{

    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
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