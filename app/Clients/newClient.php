<?php

namespace App\Clients;

use App\Http\Controllers\getBaseTableByAccountId\getMainSettingBD;
use App\Models\employeeModel;
use App\Services\Response;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class newClient
{
    private Client $client;
    private mixed $Setting;
    private mixed $URL_;


    public function __construct($employeeId)
    {
        $this->URL_ = json_decode(json_encode(Config::get("Global")));

        try {
            $model = employeeModel::where( 'employeeId', $employeeId )->first();

            if ($model != null) {
                $tmp = $model->getAttributes();
                $this->Setting = json_decode(json_encode($tmp));
            } else {
                $this->Setting = json_decode(json_encode([
                    'accountId' => "",
                    'employeeId' => "",
                    'employeeName' => "",
                    'email' => "",
                    'password' => "",
                    'appId' => "",
                    'access' => "",
                    'cabinetUserId' => "",
                    'accessToken' => "",
                    'refreshToken' => "",
                ]));
            }

        } catch (BadResponseException) {
            $this->Setting = json_decode(json_encode([
                'accountId' => "",
                'employeeId' => "",
                'employeeName' => "",
                'email' => "",
                'password' => "",
                'appId' => "",
                'access' => "",
                'cabinetUserId' => "",
                'accessToken' => "",
                'refreshToken' => "",
            ]));
        }




        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    public function createTokenMake(string $email, string $password, string $appId, $isRefersToken = null): ResponseInterface
    {

        $client = new Client();

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'email' => $email,
                'password' => $password,
                'appId' => $appId,
            ],
        ];

        if ($isRefersToken != null){
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Refresh' => $isRefersToken,
                ],
            ];
            return $client->post($this->URL_->url_.'v1/tokens/refresh',$options);
        }
        else return $client->post($this->URL_->url_.'v1/tokens',$options);
    }

    public function checkToken(): ResponseInterface
    {
        return $this->client->post($this->URL_->url_.'v1/tokens',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken
            ]
        ]);
    }

    public function licenses(): ResponseInterface
    {
        return $this->client->get($this->URL_->url_.'v1/licenses',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken,
            ]
        ]);
    }

    public function messagesSearch($licenseId, $messengerType , $chatId, $data): ResponseInterface
    {
        return $this->client->get($this->URL_->url_.'v1/licenses/'.$licenseId.'/messengers/'.$messengerType.'/chats/'.$chatId.'/messages/search',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken
            ]
        ]);
    }
    public function usersCheckTelegram($licenseId, $messengerType , $userName): ResponseInterface
    {
        return $this->client->get($this->URL_->url_.'v1/licenses/'.$licenseId.'/messengers/'.$messengerType.'/users/'.$userName.'/check',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken
            ]
        ]);
    }

    public function phonesCheck($licenseId, $messengerType , $phone): ResponseInterface
    {
        $newPhone = '+'.substr($phone, -11);
        return $this->client->get($this->URL_->url_.'v1/licenses/'.$licenseId.'/messengers/'.$messengerType.'/phones/'.$newPhone.'/check',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken
            ]
        ]);
    }

    public function sendMessage($licenseId, $messengerType , $chats, $text): ResponseInterface
    {
        return $this->client->post($this->URL_->url_.'v1/licenses/'.$licenseId.'/messengers/'.$messengerType.'/chats/'.$chats.'/messages/text',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken
            ],
            'json' => [
                'text' => $text,
            ],
        ]);
    }

    public function chats($licenseId, $messengerType, $limit = 20): Response
    {
        try{
            $answer = $this->client->get($this->URL_->url_.'v1/licenses/'.$licenseId.'/messengers/'.$messengerType.'/chats/'."?limit={$limit}",[
                'headers' => [
                    'Authorization' => $this->Setting->accessToken
                ],
                'http_errors' => false
            ]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);
        }
    }

    public function messages($licenseId, $messengerType, $chatId): Response
    {
        try{
            $answer = $this->client->get($this->URL_->url_.'v1/licenses/'.$licenseId.'/messengers/'.$messengerType.'/chats/'.$chatId."/messages",[
                'headers' => [
                    'Authorization' => $this->Setting->accessToken
                ],
                'http_errors' => false
            ]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);
        }
    }

    public function putCallbackUrls($urlCallback, $licenseId, $messengerType): Response
    {
        try{
            $answer = $this->client->put($this->URL_->url_.'v1/licenses/'.$licenseId.'/messengers/'.$messengerType.'/callbackUrl',[
                'headers' => [
                    'Authorization' => $this->Setting->accessToken
                ],
                'json'=>[
                    'events' => ["message"],
                    'url' => $urlCallback,
                ]
            ]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);
        }
    }

    public function ResponseHandler($response){
        $res = new Response();

        $body = $response->getBody()->getContents();
        $responseData = json_decode($body);
        $statusCode = $response->getStatusCode();
        $statusCondition = $statusCode < 400;

        return $res->customResponse($responseData, $statusCode, $statusCondition);
    }

    public function ResponseExceptionHandler($e){
        $res = new Response();

        return $res->customResponse($e, 500, false, $e->getMessage());
    }


}
