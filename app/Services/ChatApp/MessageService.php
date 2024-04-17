<?php
namespace App\Services\ChatApp;

use App\Clients\newClient;
use App\Services\Response;
use Exception;
use GuzzleHttp\Exception\ClientException;

class MessageService{

    private string $accountId;

    private string $employeeId;

    private newClient $chatappC;

    function __construct($accountId, $employeeId, newClient $chatappC = null) {
        if ($chatappC == null) $this->chatappC = new newClient($accountId);
        else  $this->chatappC = $chatappC;
        $this->accountId = $accountId;
        $this->employeeId = $employeeId;
    }

    function getAllMessagesFromChat($lineId, $messenger, $chatId, $errorMessage){
        
        $res = new Response();
        try{
            $messagesRes = $this->chatappC->messages($lineId, $messenger, $chatId);
            if(!$messagesRes->status)
                return $messagesRes->addMessage($errorMessage);
            else
                return $res->success($messagesRes->data);
        } 
        catch(ClientException $e){
            $res = new Response();
            $body = $e->getResponse()->getBody()->getContents();
            return $res->customResponse(json_decode($body), 400, false);
        }catch(Exception $e){
            $res = new Response();
            return $res->customResponse($e->getMessage(), 500, false);
        }
        
    }
}