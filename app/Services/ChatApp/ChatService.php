<?php
namespace App\Services\ChatApp;

use App\Exceptions\ChatappRequestException;
use App\Exceptions\ChatServiceException;
use App\Services\ChatAppException;
use App\Services\ChatappRequest;
use App\Services\Response;
use Error;

class ChatService{

    private string $employeeId;

    function __construct($employeeId) {
        if(!is_string($employeeId) || $employeeId == null)
            throw new Error("employeeId отсутствует или имеет некорректный формат");
        $this->employeeId = $employeeId;
    }

    function getAllChatForEmployee($countConversation, $lineId){

        $res = new Response();
        $chatReq = new ChatappRequest($this->employeeId);
        $licenseRes = $chatReq->getLicenses();
        $licenses = $licenseRes->data->data;
        $currentLine = array_filter($licenses, fn($val) => $val->licenseId == $lineId);
        if(count($currentLine) == 0)
            throw new ChatServiceException("линия не найдена");

        $line = array_shift($currentLine);
        $mesengers = $line->messenger;
        $mesengers = array_column($mesengers, "type");

        $resChat = [];
        foreach($mesengers as $messenger){
            $chatsRes = $chatReq->getChatsBy($lineId, $messenger, $countConversation);
            $conversations = $chatsRes->data->data->items;
            if($messenger == "avito"){
                array_map(function($value) use ($lineId, $messenger, $chatReq){
                    try{
                        $messages = $chatReq->getMessages($lineId, $messenger, $value->id);
                        $messages = $messages->data->data->items;
                        if(count($messages) == 0){
                            $value->id = null;
                            $value->name = null;
                        }
                        foreach($messages as $message){
                            if(!$message->fromMe){
                                $value->id = $message->fromUser->id;
                                $value->name = $message->fromUser->name;
                                break;
                            }

                        }
                        return $value;
                    } catch(ChatappRequestException $e){
                        $statusCode = $e->getCode();
                        if($statusCode >= 500)
                            throw $e;
                        else {
                            $value->id = null;
                            $value->name = null;
                            return $value;
                        }

                    }

                }, $conversations);

            }
            //chatapp/db
            $compliances = [
                "grWhatsApp" => "whatsapp",
                "telegram" => "telegram",
                "email" => "email",
                "vkontakte" => "vk",
                "instagram" => "instagram",
                "telegramBot" => "telegram_bot",
                "avito" => "avito"
            ];

            $resChat[$compliances[$messenger]] = $conversations;


        }
        return $res->success($resChat, "По сотруднику {$this->employeeId} получены все чаты");

    }
}
