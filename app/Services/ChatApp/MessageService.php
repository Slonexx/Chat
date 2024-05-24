<?php
namespace App\Services\ChatApp;

use App\Services\ChatappRequest;
use stdClass;

class MessageService{

    function prepareMessage(string $lineName, string $lineId, string $messenger, string $usernameOrPhone, bool $isAddMessengerInfo, stdClass $message){
        $messengerString = $isAddMessengerInfo == true ? ", $messenger $usernameOrPhone" : "";
        $directionSending = $message->fromMe == true ? "Мы:" : "Клиент:";
        return "$lineName#$lineId{$messengerString}" . PHP_EOL . "$directionSending $message->text";
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