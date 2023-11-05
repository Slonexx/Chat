<?php

namespace App\Clients;

use App\Http\Controllers\getBaseTableByAccountId\getMainSettingBD;
use App\Models\employeeModel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

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

    public function createTokenMake(string $email, string $password, string $appId): \Psr\Http\Message\ResponseInterface
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

        return $client->post($this->URL_->url_.'v1/tokens',$options);
    }

    public function checkToken(): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->post($this->URL_->url_.'v1/tokens',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken
            ]
        ]);
    }

    public function licenses(): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->get($this->URL_->url_.'v1/licenses',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken
            ]
        ]);
    }

    public function messagesSearch($licenseId, $messengerType , $chatId, $data): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->get($this->URL_->url_.'v1/licenses/'.$licenseId.'/messengers/'.$messengerType.'/chats/'.$chatId.'/messages/search',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken
            ]
        ]);
    }
    public function usersCheckTelegram($licenseId, $messengerType , $userName): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->get($this->URL_->url_.'v1/licenses/'.$licenseId.'/messengers/'.$messengerType.'/users/'.$userName.'/check',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken
            ]
        ]);
    }

    public function phonesCheck($licenseId, $messengerType , $phone): \Psr\Http\Message\ResponseInterface
    {
        $newPhone = '+'.substr($phone, -11);
        return $this->client->get($this->URL_->url_.'v1/licenses/'.$licenseId.'/messengers/'.$messengerType.'/phones/'.$newPhone.'/check',[
            'headers' => [
                'Authorization' => $this->Setting->accessToken
            ]
        ]);
    }

    public function sendMessage($licenseId, $messengerType , $chats, $text): \Psr\Http\Message\ResponseInterface
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


}
