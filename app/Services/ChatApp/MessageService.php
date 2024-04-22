<?php
namespace App\Services\ChatApp;

use App\Clients\newClient;
use App\Services\ChatappRequest;
use App\Services\Response;
use Exception;
use GuzzleHttp\Exception\ClientException;

class MessageService{

    private string $accountId;

    private string $employeeId;

    private ChatappRequest $chatReq;

    function __construct($employeeId, ChatappRequest $chatappC = null) {
        if ($chatappC == null) $this->chatReq = new ChatappRequest($employeeId);
        else  $this->chatReq = $chatappC;
        $this->employeeId = $employeeId;
    }

    function getAllMessagesFromChat($lineId, $messenger, $chatId, $errorMessage){
        
        $res = new Response();
        try{
            $messagesRes = $this->chatReq->getMessages($lineId, $messenger, $chatId);
            if(!$messagesRes->status)
                return $messagesRes->addMessage($errorMessage);
            else
                return $res->success($messagesRes->data);
        } 
        catch(ClientException $e){
            $res = new Response();
            $body = $e->getResponse()->getBody()->getContents();
            return $res->customResponse(json_decode($body), 400, false);
        }
        
    }
}