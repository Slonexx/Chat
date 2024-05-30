<?php
namespace App\Services\Intgr\Entities;

use App\Clients\MoySkladIntgr;
use App\Exceptions\MsException;
use App\Services\HTTPResponseHandler;
use App\Services\Response;
use Error;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;

class CounterpartyNotesService{

    private MoySkladIntgr $msC;

    private Response $res;

    private const URL_IDENTIFIER = "counterpartyNotes";

    function __construct(MoySkladIntgr $MoySklad) {
        $this->msC = $MoySklad;
        $this->res = new Response();
    }

    function create($counterpartyId, $body){
        $fullKey = "msUrls." . self::URL_IDENTIFIER;
        $notesUrl = Config::get($fullKey, null);
        if(!is_string($notesUrl) || $notesUrl == null)
            throw new Error("url отсутствует или имеет некорректный формат");
        $replacedUrl = str_replace("{counterpartyId}", $counterpartyId, $notesUrl);
        $resHandler = new HTTPResponseHandler();
        try{
            $response = $this->msC->post($replacedUrl, $body);
            return $resHandler->handleOK($response, "заметка в контрагенте успешно создана");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при создании заметки в контрагенте|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при создании заметки в контрагенте", previous:$e);
            }
        }
    }

    function delete($counterpartyId, $noteId){
        $fullKey = "msUrls." . self::URL_IDENTIFIER;
        $notesUrl = Config::get($fullKey, null);
        if(!is_string($notesUrl) || $notesUrl == null)
            throw new Error("url отсутствует или имеет некорректный формат");
        $replacedUrl = str_replace("{counterpartyId}", $counterpartyId, $notesUrl);
        $preparedUrl = $replacedUrl . $noteId;
        $resHandler = new HTTPResponseHandler();
        try{
            $response = $this->msC->delete($preparedUrl);
            return $resHandler->handleOK($response, "заметка в контрагенте успешно удалена");

        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при удалении заметки в контрагенте|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при удалении заметки в контрагенте", previous:$e);
            }
        }
    }


}