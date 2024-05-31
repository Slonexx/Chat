<?php

namespace App\Clients;

use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;

class ClientException extends Exception {}

class ChatAppIntgr {
    private Client $client;

    public function __construct(string $accessToken)
    {
        if($accessToken == null)
            throw new InvalidArgumentException("Токен ChatApp пустой");

        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $accessToken
            ]
        ]);
    }

    public function get($url){
        return $this->client->get($url);
    }

    public function post($url, $data){
        return $this->client->post($url, [
            'json' => $data
        ]);
    }

    public function put($url, $body){
        return $this->client->put($url, [
            'json' => $body
        ]);
    }

    public function delete($url){
        return $this->client->delete($url);
    }

}
