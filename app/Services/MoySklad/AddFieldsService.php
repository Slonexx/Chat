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
        $attrs = new stdClass();

        foreach($services as $key => $s){
            $attrRes = $s->getAllAttributes(false);
            if(!$attrRes->status)
                return $attrRes;
            $attrs->{$key} = $attrRes->data;
        }

        return $this->res->success($attrs);
    }

}