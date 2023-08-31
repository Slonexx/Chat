<?php

namespace App\Clients;

use App\Http\Controllers\getBaseTableByAccountId\getMainSettingBD;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class newClient
{
    private Client $client;
    private getMainSettingBD $Setting;
    private mixed $URL_;


    public function __construct($accountId)
    {
        $this->Setting = new getMainSettingBD($accountId);
        $this->URL_ = json_decode(json_encode(Config::get("Global")));

        $this->client = new Client([
            'base_uri' => $this->URL_->url_,
            'headers' => [
                'Authorization' => $this->Setting->accessToken,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    public function createTokenMake(string $email, string $password, string $appId): \Psr\Http\Message\ResponseInterface
    {

        $client = new Client([
            'base_uri' => $this->URL_->url_,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        return $client->post($this->URL_->url_.'v1/tokens',[
            'body' => json_encode([
                'email' => $email,
                'password' => $password,
                'appId' => $appId,
            ]),
        ]);
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



}
