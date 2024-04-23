<?php
namespace App\Services\MoySklad;

class CutLogicService{
    function cutArrayWithKeys(array $array, array $keysToKeep){
        $prepAttrs = [];
        foreach($array as $attrItem){
            $prepAttrs[] = (object) array_intersect_key((array)$attrItem, array_flip($keysToKeep));
        }
        return $prepAttrs;
    }
    
    /**
     * specific f
     */
    // function cutMsObjectFromReqExpand($objectMs, $expandParams){
    //         try{
    //             array_map(function($value) use ($fields){
    //                 foreach($value as $key => $property){
    //                     if(!in_array($key, $fields))
    //                             unset($value->{$key});
    //                 }
    //             }, $objectMs);

    //             $res = new Response();

    //             return $res->success($objectMs);

    //         } catch (Exception $e){
    //             $res = new Response();
    //             $answer = $res->errorWith200($e, $e->getMessage());
    //             return $answer;
    //         }
    //     }
    // }
}