<?php
namespace App\Services\MoySklad\Attributes;

use App\Clients\MoySklad;
use App\Exceptions\MsException;
use App\Services\Entities\CustomEntityService;
use App\Services\HandlerService;
use App\Services\HTTPResponseHandler;
use App\Services\MsFilterService;
use App\Services\Response;
use Error;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;

class CounterpartyS {

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    private const ATTRIBUTES_URL_IDENTIFIER = "agentMetadataAttributes";

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->res = new Response();
        $this->accountId = $accountId;
    }
    /**
     * Метод возращает все аттрибуты в виде массива
     * @param bool $notEmpty true - выдаст ошибку если массив пустой
     */
    public function getAllAttributes(bool $notEmpty = true){
        $resAttr = $this->msC->getAll(self::ATTRIBUTES_URL_IDENTIFIER);
        if(!$resAttr->status)
            return $resAttr->addMessage("Ошибка при получении аттрибутов контрагента");
        else {
            $attributes = $resAttr->data->rows;
            if($notEmpty && count($attributes) == 0){
                return $this->res->error($attributes, "Аттрибуты не найдены");
            } else {
                return $this->res->success($attributes);
            }
        }
            
    }

    public function getAttributesById($id){
        $res = $this->msC->getById(self::ATTRIBUTES_URL_IDENTIFIER, $id);
        if(!$res->status)
            return $res->addMessage("Ошибка при получении organizationMetadataAttributes по id");
        else
            return $res;
    }

    public function createAttribute($body){
        $res = $this->msC->post(self::ATTRIBUTES_URL_IDENTIFIER, $body);
        if(!$res->status)
            return $res->addMessage("Ошибка при создании organizationMetadataAttributes");
        else
            return $res;
    }

    public function getByAttribute(string $attribute_id, string $value){
        $filterS = new MsFilterService();
        try{
            $filterUrl = $filterS->prepareUrlForFilter("agent", self::ATTRIBUTES_URL_IDENTIFIER, $attribute_id, $value);
        }catch(Error $e){
            throw new Error("Ошибка при получении контрагента по доп полю", previous:$e);
        }
        $resHandler = new HTTPResponseHandler();
        try{
            $response = $this->msC->get($filterUrl);
            return $resHandler->handleOK($response, "поиск контрагент успешно завершён");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при поиске контрагента|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при поиске контрагента", previous:$e);
            }
        }
        
        
    }
    /**
     * возращает аттрибуты, которых нет в моём складе
     */
    function checkCreateArrayAttributes($attributes){
        $handlerS = new HandlerService();
        $attributesRes = $this->getAllAttributes(false);
        if(!$attributesRes->status)
            return $attributesRes;
        $attrubutesForCreating = [];

        foreach($attributes as $addFieldMs){
            $findedAttribute = array_filter($attributesRes->data, fn($attribute)=> $attribute->name == $addFieldMs->name);
            if(count($findedAttribute) == 0)
                $attrubutesForCreating[] = $addFieldMs;
        }
        if(empty($attrubutesForCreating))
            return null;
        else{
            return $handlerS->createResponse(true, $attrubutesForCreating);
        }
    }


}