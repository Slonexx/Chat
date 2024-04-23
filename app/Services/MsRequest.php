<?php
namespace App\Services;

use App\Clients\ChatApp;
use App\Exceptions\ChatappRequestException;
use Error;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;

class MsRequest{

    private ChatApp $chatappC;

    private string $accountId;

    function __construct($employeeId) {
        $this->chatappC = new ChatApp($employeeId);
    }

    function getLicenses(){
        $licenseUrl = Config::get("chatappUrls.licenses", null);
        $resHandler = new HTTPResponseHandler();
        if(!is_string($licenseUrl) || $licenseUrl == null)
            throw new Error("url лицензии отсутствует или имеет некорректный формат");
        try{
            $response = $this->chatappC->get($licenseUrl);
            return $resHandler->handleOK($response, "лицензии успешно получены");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new ChatappRequestException("ошибка при получении линий|" . $encodedBody, $statusCode);
            } else {
                throw new ChatappRequestException("неизвестная ошибка при получении линий", previous:$e);
            }
        }
        
    }


}