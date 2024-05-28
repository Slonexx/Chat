<?php
namespace App\Services;

use App\Clients\ChatApp;
use App\Exceptions\ChatappRequestException;
use Error;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;

class ChatappRequest{

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

    function getChatsBy(string $licenseId, string $messenger, int $limit = 20){
        $chatsUrl = Config::get("chatappUrls.chats", null);
        if(!is_string($chatsUrl) || $chatsUrl == null)
            throw new Error("url чатов отсутствует или имеет некорректный формат");
        $url = sprintf($chatsUrl, $licenseId, $messenger);
        $urlWithLimit = "{$url}?limit={$limit}";
        $resHandler = new HTTPResponseHandler();
        try{
            $response = $this->chatappC->get($urlWithLimit);
            return $resHandler->handleOK($response, "чаты успешно получены");
        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new ChatappRequestException("ошибка при получении чатов|" . $encodedBody, $statusCode);
            } else {
                throw new ChatappRequestException("неизвестная ошибка при получении чатов", previous:$e);
            }
        }
        
    }

    function getMessages(string $licenseId, string $messenger, string $chatId){
        $messagesUrl = Config::get("chatappUrls.messages", null);
        if(!is_string($messagesUrl) || $messagesUrl == null)
            throw new Error("url сообщений отсутствует или имеет некорректный формат");
        $url = sprintf($messagesUrl, $licenseId, $messenger, $chatId);
        $resHandler = new HTTPResponseHandler();
        try{
            $response = $this->chatappC->get($url);
            return $resHandler->handleOK($response, "сообщения успешно получены");
        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new ChatappRequestException("ошибка при получении сообщений|" . $encodedBody, $statusCode);
            } else {
                throw new ChatappRequestException("неизвестная ошибка при получении сообщений", previous:$e);
            }
        }
    }
    /**
     * @param int $countMessages > 0 && $countMessages <= 100
     */
    function getMessagesWithLimitAndTime(string $licenseId, string $messenger, string $chatId, string $direction, int $countMessages = 20, $time = null){
        $messagesUrl = Config::get("chatappUrls.messages", null);
        if(!is_string($messagesUrl) || $messagesUrl == null)
            throw new Error("url сообщений отсутствует или имеет некорректный формат");
        $url = sprintf($messagesUrl, $licenseId, $messenger, $chatId);
        $urlWithLimit = "$url?limit=$countMessages&direction=$direction";
        $timeIsNull = $time == null;
        if(!$timeIsNull){
            $urlWithLimitAndTime = $urlWithLimit . "&lastTime=$time";
        }
            
        $resHandler = new HTTPResponseHandler();
        try{
            if(!$timeIsNull)
                $urlChatapp = $urlWithLimitAndTime;
            else
                $urlChatapp = $urlWithLimit;
            $response = $this->chatappC->get($urlChatapp);
            return $resHandler->handleOK($response, "сообщения успешно получены");
        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new ChatappRequestException("ошибка при получении сообщений|" . $encodedBody, $statusCode);
            } else {
                throw new ChatappRequestException("неизвестная ошибка при получении сообщений", previous:$e);
            }
        }
    }

    function getWebhooks(){
        $webhooksUrl = Config::get("chatappUrls.webhooks", null);
        if(!is_string($webhooksUrl) || $webhooksUrl == null)
            throw new Error("url вебхуков отсутствует или имеет некорректный формат");
        $resHandler = new HTTPResponseHandler();
        try{
            $response = $this->chatappC->get($webhooksUrl);
            return $resHandler->handleOK($response, "вебхуки успешно получены");
        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new ChatappRequestException("ошибка при получении вебхуков|" . $encodedBody, $statusCode);
            } else {
                throw new ChatappRequestException("неизвестная ошибка при получении вебхуков", previous:$e);
            }
        }
    }


}