<?php
namespace App\Services\MoySklad\Attributes;

use App\Clients\MoySklad;
use App\Exceptions\CustomerorderAttributesException;
use App\Exceptions\MsException;
use App\Services\HTTPResponseHandler;
use App\Services\Response;
use Error;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;

class CustomorderS {

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    private const ATTRIBUTES_URL_IDENTIFIER = "customerorderMetadataAttributes";

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
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
            $agentRes = $resHandler->handleOK($resAttr, "все аттрибуты заказа покупателя успешно получены");
            $attributes = $agentRes->data->rows;
            if($notEmpty && count($attributes) == 0){
                throw new CustomerorderAttributesException("Аттрибуты не найдены", 1);
            } else {
                return $this->res->success($attributes);
            }
        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при получении всех аттрибутов заказа покупателя|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при получении всех аттрибутов заказа покупателя", previous:$e);
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