<?php
namespace App\Services\Intgr\Entities;

use App\Clients\MoySkladIntgr;
use App\Exceptions\MsException;
use App\Services\HTTPResponseHandler;
use Error;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;

class TaskService {

    private MoySkladIntgr $msC;

    private const URL_IDENTIFIER = "task";

    function __construct(MoySkladIntgr $MoySklad) {
        $this->msC = $MoySklad;
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
