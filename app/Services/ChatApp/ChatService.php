<?php
namespace App\Services\ChatApp;

use App\Clients\newClient;
use App\Services\Response;
use Exception;
use GuzzleHttp\Exception\ClientException;

class ChatService{

    private string $accountId;

    function __construct($accountId) {
        $this->accountId = $accountId;
    }

    function getAllChatForEmployee($countConversation, $employeeId, $lineId){
        
        $chatappC = new newClient($employeeId);
        $res = new Response();
        try{
            $licenseReq = $chatappC->licenses();
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
                $chatsReq = $chatappC->chats($lineId, $item);
                if(!$chatsReq->status){
                    return $chatsReq;
                }
                //chatapp/db
                $compliances = [
                    "grWhatsApp" => "whatsapp",
                    "telegram" => "telegram",
                    "email" => "email",
                    "vkontakte" => "vk",
                    "instagram" => "instagram",
                    "telegramBot" => "telegram_bot"
                ];
                $conversations = $chatsReq->data->data->items;
                $chunks = array_chunk($conversations, $countConversation);

                $resChat[$compliances[$item]] = $chunks[0];

                
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