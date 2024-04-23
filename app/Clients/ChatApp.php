<?php

namespace App\Clients;

use App\Models\employeeModel;
use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;

class ClientException extends Exception {}

class ChatApp {
    private Client $client;

    public function __construct($employeeId)
    {
        $model = employeeModel::where( 'employeeId', $employeeId )->first();
        $authToken = $model->accessToken ?? null;
        if($authToken == null)
            throw new InvalidArgumentException("Токен ChatApp не найден");

        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authToken
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
