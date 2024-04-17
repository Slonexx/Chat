<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Clients\MsClient;
use App\Clients\MsClientAdd;
use App\Models\AttributeSettings;
use App\Services\MoySklad\Entities\CustomEntityService;
use App\Services\HandlerService;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Config;
use stdClass;

class CustomEntityLogicService {
    private MoySklad $msC;

    private string $accountId;

    private HandlerService $handlerS;

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->handlerS = new HandlerService();
        $this->accountId = $accountId;
    }

    function findOrCreate($entityTypeAttributes, $attributes){
        $body = [];
        $customEntityS = new CustomEntityService($this->accountId, $this->msC);
        //можно сюда вынести получение всех справочников
        foreach($attributes as $addField) {
            $res = $customEntityS->tryToFind($addField);
            if(isset($res)){
                if(!$res->status)
                    return $res;

                $customentityId = $res->data;
               
            } else {
                $res = $customEntityS->createDictionary($addField);
                if(!$res->status)
                    return $res;
    
                $customentityId = $res->data->id;
                $res = $customEntityS->createValuesMoreThan1000($addField, $customentityId);
                if(!$res->status)
                    return $res;
            }

            $res = $customEntityS->setBody($addField, $customentityId);
            if(!$res->status)
                return $res;

            $body[] = $res->data;
        }
        
        return $customEntityS->createAttribute($entityTypeAttributes, $body);
    }
}