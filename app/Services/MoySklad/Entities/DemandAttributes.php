<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\MoySklad;
use App\Clients\MsClient;
use App\Services\HandlerService;
use App\Services\Response;
use Exception;
use Illuminate\Support\Facades\Config;
use stdClass;

class DemandAttributes {

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    private const ATTRIBUTES_URL_IDENTIFIER = "demandMetadataAttributes";

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
        $this->res = new Response();
        $this->accountId = $accountId;
    }
    public function getAllAttributes(){
        $res = $this->msC->getAll(self::ATTRIBUTES_URL_IDENTIFIER);
        if(!$res->status)
            return $res->errorWith200($res->data, "Ошибка при получении аттрибутов отгрузки");
        else
            return $res;
    }
}