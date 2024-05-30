<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\MoySklad;
use App\Exceptions\MsException;
use App\Services\HTTPResponseHandler;
use App\Services\Response;
use Error;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
use stdClass;

class CustomOrderService {

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    private const URL_IDENTIFIER = "customerorder";

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->res = new Response();
        $this->accountId = $accountId;
    }

    public function getById(string $id) {
        // $res = $this->msC->getById(self::URL_IDENTIFIER, $id);
        // if(!$res->status)
        //     return $res->addMessage("Ошибка при получении заказа покупателя");
        // else
        //     return $res;
    }

    /**
     * @throws MsException
     */
    public function create($body){
        $fullKey = "msUrls." . self::URL_IDENTIFIER;
        $url = Config::get($fullKey, null);
        $resHandler = new HTTPResponseHandler();
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");
        try{
            $response = $this->msC->post($url, $body);
            return $resHandler->handleOK($response, "заказ успешно создан");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при создании заказа покупателя|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при создании заказа покупателя", previous:$e);
            }
        }
    }

    function update($id, $body){
        $fullKey = "msUrls." . self::URL_IDENTIFIER;
        $url = Config::get($fullKey, null);
        $resHandler = new HTTPResponseHandler();
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");
        try{
            $preparedUrl = $url . $id;
            $response = $this->msC->put($preparedUrl, $body);
            return $resHandler->handleOK($response, "заказ успешно обновлён");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при обновлении заказа|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при обновлении заказа", previous:$e);
            }
        }
    }


}
