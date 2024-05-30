<?php
namespace App\Services\Intgr;

use App\Services\ChatappRequest;
use stdClass;

class MessageService{

    function prepareMessage(string $line, string $messenger, string $usernameOrPhone, bool $isAddMessengerInfo, stdClass $message){
        $messengerString = $isAddMessengerInfo == true ? ", $messenger $usernameOrPhone" : "";
        $directionSending = $message->fromMe == true ? "Мы:" : "Клиент:";
        return "$line{$messengerString}" . PHP_EOL . "$directionSending $message->text";
    }
}