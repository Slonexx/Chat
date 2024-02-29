<?php
namespace App\Services\Entity;

use App\client\MsClient;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class SalesReturnService {
    private MsClient $msClient;

    function __construct($accountId) {
        $this->msClient = new MsClient($accountId);
    }

    public function getById(string $id) {
        $urlIdentifier = "salesreturnURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
        $data = $obj->data;       
        $statusCode = $obj->statusCode;
        return response()->json($data, $statusCode);
    }

    function send($data) {
        $urlIdentifier = "salesreturnURL";

        $obj = $this->msClient->post($urlIdentifier, $data);
        $data = $obj->data;
        $statusCode = $obj->statusCode;
        return response()->json($data, $statusCode);
    }
    
    public function change($data, $id){
        $urlIdentifier = "salesreturnURL";
        //dd($urlIdentifier, $data, $id);
        $obj = $this->msClient->put($urlIdentifier, $data, $id);
        $data = $obj->data;       
        $statusCode = $obj->statusCode;
        return response()->json($data, $statusCode);
    }

}