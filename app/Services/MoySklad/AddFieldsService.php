<?php
namespace App\Services\MoySklad;

use App\Clients\oldMoySklad;
use App\Services\Response;
use stdClass;

class AddFieldsService{

    private oldMoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId) {
        $this->msC = new oldMoySklad($accountId);
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

    // function getAttrForCurrentEntityId($services){
    //     $templateAttributeValues = [];
    //     foreach($templatesWithAtttributes as $item){
    //         $user_var = $item["name"];
    //         $templateAttributeValues["!{{$user_var}}"] = $item["attribute_id"];
    //     }

    //     $resAttributes = $expandedInfo->attributes;

    //     foreach($templateAttributeValues as $key => $_){
    //         $findedAttribute = array_filter(
    //             $resAttributes,
    //             function ($value) use ($templateAttributeValues) {
    //                 return in_array($value->id, $templateAttributeValues);
    //             }
    //         );
    //         if(count($findedAttribute) > 0){
    //             $firstAttributeById = array_shift($findedAttribute);
    //             $templateAttributeValues[$key] = $firstAttributeById->value;
    //         }

    //     }
    // }

}