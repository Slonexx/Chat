<?php
namespace App\Clients;

use GuzzleHttp\Client;
use App\Models\MainSettings;
use App\Services\Response;
use Exception;
use InvalidArgumentException;

class MoySklad {

    private string $token;

    public Client $client;

    public array $headers;

    public mixed $url;

    public function __construct($accountId) {
        $setting = MainSettings::where("account_id", $accountId)->first();
        $authToken = $setting->ms_token ?? null;
        if($authToken == null)
            throw new InvalidArgumentException("Токен MoySklad не найден");

        $this->token = $authToken;
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
