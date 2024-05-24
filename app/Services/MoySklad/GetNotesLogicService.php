<?php
namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Services\ChatappRequest;
use App\Services\Response;
use stdClass;

class GetNotesLogicService{

    private MoySklad $msC;

    private string $accountId;

    private ChatappRequest $chatappReq;

    private Response $res;

    function __construct(string $accountId, MoySklad $MoySklad = null, 
        string $employeeId, ChatappRequest $chatappReq = null) {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        if ($employeeId == null) $this->chatappReq = new ChatappRequest($employeeId);
        else  $this->chatappReq = $chatappReq;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function getAllFromOldToNew($lineId, $messenger, $chatId, $limitMessages, $timeFromDb = null, $MAX_UPLOAD_MESSAGE){
        $messages = [];
        $olderTimeInArray = null;
        $compliances = [
            "whatsapp" => "grWhatsApp",
            "telegram" => "telegram",
            "email" => "email",
            "vk" => "vkontakte",
            "instagram" => "instagram",
            "telegram_bot" => "telegramBot",
            "avito" => "avito"
        ];
        if($timeFromDb == null){
            do{
                $direction = "prev";
                //db/chatapp
                
                $iterMessages = $this->chatappReq->getMessagesWithLimitAndTime($lineId, $compliances[$messenger], $chatId, $direction, $limitMessages, $timeFromDb);
                $messagesObj = $iterMessages->data->data->items;
                
                $countMessages = count($messagesObj);

                if($countMessages == $limitMessages){
                    $lastMessage = $messagesObj[$limitMessages - 1];
                    $timeFromDb = $lastMessage->time;
                }

                
                foreach($messagesObj as $message){
                    $messageItem = new stdClass();
                    $messageItem->text = $message->message->text;
                    $messageItem->fromMe = $message->fromMe;
                    $messages[] = $messageItem;
                }

            } while($countMessages == $limitMessages && count($messages) < $MAX_UPLOAD_MESSAGE);
            $messages = array_reverse($messages);
        } else {
            $direction = "next";
            if($olderTimeInArray == null)
                $olderTimeInArray = $timeFromDb;

            do{
                $iterMessages = $this->chatappReq->getMessagesWithLimitAndTime($lineId, $compliances[$messenger], $chatId, $direction, $limitMessages);
                $messagesObj = $iterMessages->data->data->items;

                $countMessages = count($messagesObj);
                
                if($countMessages == $limitMessages){
                    $lastMessage = $messagesObj[$limitMessages - 1];
                    $olderTimeInArray = $lastMessage->time;
                }

                foreach($messagesObj as $message){
                    $messageItem = new stdClass();
                    $messageItem->text = $message->message->text;
                    $messageItem->fromMe = $message->fromMe;
                    $messages[] = $messageItem;
                }
            } while($countMessages == $limitMessages && count($messages) < $MAX_UPLOAD_MESSAGE);
        }
        return $messages;
        
    }

}