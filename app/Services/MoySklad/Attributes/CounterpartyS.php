<?php
namespace App\Services\MoySklad\Attributes;

use App\Clients\MoySklad;
use App\Exceptions\CounterpartyAttributesException;
use App\Exceptions\MsException;
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
        $fullKey = "msUrls." . self::ATTRIBUTES_URL_IDENTIFIER;
        $url = Config::get($fullKey, null);
        $resHandler = new HTTPResponseHandler();
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");
        try{
            $resAttr = $this->msC->get($url);
            $agentRes = $resHandler->handleOK($resAttr, "все аттрибуты контрагента успешно получены");
            $attributes = $agentRes->data->rows;
            if($notEmpty && count($attributes) == 0){
                throw new CounterpartyAttributesException("Аттрибуты не найдены", 1);
            } else {
                return $this->res->success($attributes);
            }
        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при получении всех аттрибутов контрагента|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при получении всех аттрибутов контрагента", previous:$e);
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
            return $resHandler->handleOK($response, "поиск по аттрибуту контрагента успешно завершён");

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
        $attributesRes = $this->getAllAttributes(false);
        $attrubutesForCreating = [];

        foreach($attributes as $addFieldMs){
            $findedAttribute = array_filter($attributesRes->data, fn($attribute)=> $attribute->name == $addFieldMs->name);
            if(count($findedAttribute) == 0)
                $attrubutesForCreating[] = $addFieldMs;
        }
        return $attrubutesForCreating;
    }


}