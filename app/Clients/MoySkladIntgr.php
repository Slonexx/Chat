<?php
namespace App\Clients;

use GuzzleHttp\Client;

class MoySkladIntgr {

    private string $token;

    public Client $client;

    public array $headers;

    public mixed $url;

    public function __construct(string $ms_token) {
        $this->token = $ms_token;
        $auth = 'Bearer ' . $this->token;

        $this->headers = [
            'Authorization' => $auth,
            'Content-Type' => 'application/json',
            'Accept-Encoding' => 'gzip'
        ];
        $this->client = new Client([
            'headers' => $this->headers
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
