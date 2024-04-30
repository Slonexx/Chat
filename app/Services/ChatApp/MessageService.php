<?php
namespace App\Services\ChatApp;

use App\Services\ChatappRequest;
use App\Services\Response;
use DateTime;
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

    function prepareMessages(string $lineName, string $lineId, string $messenger, string $usernameOrPhone, bool $isAddMessengerInfo, array $messages){
        $arrayMessages = [];
        $messengerString = $isAddMessengerInfo == true ? ", $messenger $usernameOrPhone" : "";
        foreach($messages as $message){
            $directionSending = $message->fromMe == true ? "Мы:" : "Клиент:";
            $arrayMessages[] =  "$lineName#$lineId{$messengerString}" . PHP_EOL . "$directionSending $message->text";
        }
        return $arrayMessages;
    }
}