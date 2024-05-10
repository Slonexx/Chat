<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\MoySklad;
use App\Exceptions\MsException;
use App\Services\HTTPResponseHandler;
use App\Services\MoySklad\CutLogicService;
use App\Services\Response;
use Error;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
use stdClass;

class TaskService {

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    private const URL_IDENTIFIER = "task";

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
        $this->res = new Response();
        $this->accountId = $accountId;
    }

    public function create($body){
        $fullKey = "msUrls." . self::URL_IDENTIFIER;
        $url = Config::get($fullKey, null);
        $resHandler = new HTTPResponseHandler();
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");
        try{
            $response = $this->msC->post($url, $body);
            return $resHandler->handleOK($response, "задача успешно создана");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при создании задачи|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при создании задачи", previous:$e);
            }
        }
    }
}