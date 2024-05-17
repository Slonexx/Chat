<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\MoySklad;
use App\Exceptions\CounterpartyException;
use App\Exceptions\MsException;
use App\Services\HTTPResponseHandler;
use App\Services\MsFilterService;
use App\Services\Response;
use Error;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;

class CounterpartyService{

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    private const URL_IDENTIFIER = "agent";

    function __construct($accountId, MoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->res = new Response();
        $this->accountId = $accountId;
    }

    public function getById(string $id) {
        // $res = $this->msC->getById(self::URL_IDENTIFIER, $id);
        // if(!$res->status)
        //     return $res->addMessage("Ошибка при получении контрагента");
        // else
        //     return $res;
        // $fullKey = "msUrls." . self::URL_IDENTIFIER;
        // $url = Config::get($fullKey, null);
        // $resHandler = new HTTPResponseHandler();
        // if(!is_string($url) || $url == null)
        //     throw new Error("url отсутствует или имеет некорректный формат");
        // try{
        //     $urlWithId =  $url . $id;
        //     $response = $this->msC->get($urlWithId);
        //     return $resHandler->handleOK($response, "контрагент c id=$id найден");

        // } catch(RequestException $e){
        //     if($e->hasResponse()){
        //         $response = $e->getResponse();
        //         $statusCode = $response->getStatusCode();
        //         $encodedBody = $response->getBody()->getContents();
        //         throw new MsException("ошибка при получении контрагента |" . $encodedBody, $statusCode);
        //     } else {
        //         throw new MsException("неизвестная ошибка при поиске с limit=$limit", previous:$e);
        //     }
        // }
    }

    public function getWithLimit(int $limit) {
        $fullKey = "msUrls." . self::URL_IDENTIFIER;
        $url = Config::get($fullKey, null);
        $resHandler = new HTTPResponseHandler();
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");
        try{
            $urlWithLimit =  $url . "?limit=$limit";
            $response = $this->msC->get($urlWithLimit);
            return $resHandler->handleOK($response, "контрагенты c limit=$limit найдены");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при поиске с limit=$limit |" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при поиске с limit=$limit", previous:$e);
            }
        }
    }

    public function getByParam(string $name, mixed $value){
        $filterS = new MsFilterService();
        try{
            $url = $filterS->prepareUrlWithParam(self::URL_IDENTIFIER, $name, $value);
        } catch(Error $e){
            throw new Error("Ошибка при получении url с параметрами", previous:$e);
        }
        $resHandler = new HTTPResponseHandler();
        try{
            $response = $this->msC->get($url);
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

    public function getByIdWithExpand(string $id, array $expandParams) {
        $agentUrl = Config::get("Global")[self::URL_IDENTIFIER];
        $preppedUrl = $agentUrl . $id . "?expand=";
        if(count($expandParams) == 0){
            return $this->res->error($expandParams, "Нет параметров для expand");
        }
        foreach($expandParams as $param){
            if($param != null)
            $preppedUrl = "{$preppedUrl}{$param},";
        }
        $expandedRes = $this->msC->getByUrl($preppedUrl);
        if(!$expandedRes->status)
            return $expandedRes->addMessage("Ошибка при получении расширенного контрагента");
        else
            return $expandedRes;

    }

    public function create($body){
        $fullKey = "msUrls." . self::URL_IDENTIFIER;
        $url = Config::get($fullKey, null);
        $resHandler = new HTTPResponseHandler();
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");
        try{
            $response = $this->msC->post($url, $body);
            return $resHandler->handleOK($response, "контрагент успешно создан");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при создании контрагента|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при создании контрагента", previous:$e);
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
            return $resHandler->handleOK($response, "контрагент успешно обновлён");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при обновлении контрагента|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при обновлении контрагента", previous:$e);
            }
        }
    }
}
