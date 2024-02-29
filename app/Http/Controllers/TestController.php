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
        $res = $msC->getById("organizationMetadataAttributes", "62b037b6-d3aa-11ee-0a80-1028009410e4");
        //$goodRes = $res->success("Good");
        return response()->json($res);
    }
}
