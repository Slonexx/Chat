<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\NCANodeClient;
use App\Clients\UdoClient;
use App\Models\Users;
use App\Services\Response;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Exception, Error;
use Illuminate\Support\Facades\Config;

class CutService{
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