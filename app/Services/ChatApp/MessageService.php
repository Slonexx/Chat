<?php
namespace App\Services\ChatApp;

use App\Services\ChatappRequest;
use stdClass;

class MessageService{

    function prepareMessages(string $lineName, string $lineId, string $messenger, string $usernameOrPhone, bool $isAddMessengerInfo, stdClass $message){
        $messengerString = $isAddMessengerInfo == true ? ", $messenger $usernameOrPhone" : "";
        $directionSending = $message->fromMe == true ? "Мы:" : "Клиент:";
        return "$lineName#$lineId{$messengerString}" . PHP_EOL . "$directionSending $message->text";
    }
}