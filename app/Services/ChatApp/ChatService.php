<?php
namespace App\Services\ChatApp;

use App\Clients\newClient;
use App\Services\Response;
use Exception;
use GuzzleHttp\Exception\ClientException;

class ChatService{

    private string $accountId;

    private string $employeeId;

    private newClient $chatappC;

    function __construct($accountId, $employeeId) {
        $this->chatappC = new newClient($employeeId);
        $this->accountId = $accountId;
        $this->employeeId = $employeeId;
    }

    function getAllChatForEmployee($countConversation, $lineId){
        
        $res = new Response();
        try{
            $licenseReq = $this->chatappC->licenses();
            $encoded_body = $licenseReq->getBody()->getContents();
            $body = json_decode($encoded_body);
            $lines = $body->data;
            $currentLine = array_filter($lines, fn($val) => $val->licenseId == $lineId);
            if(count($currentLine) == 0)
                return $res->error($currentLine, "линия не найдена");

            $line = array_shift($currentLine);
            $mesengers = $line->messenger;
            $mesengers = array_column($mesengers, "type");

            $resChat = [];
            foreach($mesengers as $item){
                $chatsReq = $this->chatappC->chats($lineId, $item, $countConversation);
                if(!$chatsReq->status){
                    return $chatsReq;
                }
                if($item == "avito"){
                    $arrayChats = $chatsReq->data->data->items;
                    array_map(function($value) use ($lineId, $item){
                        $messageS = new MessageService($this->accountId, $this->employeeId, $this->chatappC);
                        $errorMessage = "Ошибка при получении сообщений";
                        $messages = $messageS->getAllMessagesFromChat($lineId, $item, $value->id, $errorMessage);
                        if(!$messages->status){
                            $value->fromUser = [];
                            return $value;
                        }
                        $messages = $messages->data->data->items;
                        if(count($messages) == 0)
                            $value->fromUser = [];
                        foreach($messages as $message){
                            if(!$message->fromMe){
                                $value->fromUser = $message->fromUser;
                                break;
                            }

                        }
                        return $value;

                    }, $arrayChats);
                    
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
                $conversations = $chatsReq->data->data->items;

                $resChat[$compliances[$item]] = $conversations;

                
            }
            return $res->success($resChat);
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