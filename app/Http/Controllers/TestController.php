<?php

namespace App\Http\Controllers;

use App\Clients\MoySklad;
use App\Clients\MsClient;
use App\Services\Response;
use Illuminate\Http\Request;

class TestController extends Controller
{
    function check(){
        $res = new Response();
        $msC = new MoySklad("1dd5bd55-d141-11ec-0a80-055600047495");
        $res = $msC->getById("organizationMetadataAttributes", "954efa46-d3b3-11ee-0a80-17a00098792d");
        //$goodRes = $res->success("Good");
        return response()->json($res);
    }

    function testReturnMainFields(){

    }
    function yes(){
        return response()->json([],200);
    }
}
