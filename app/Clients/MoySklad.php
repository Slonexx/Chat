<?php
namespace App\Clients;

use App\Services\Response;
use GuzzleHttp\Client;
use App\Models\MainSettings;
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

    public function getByUrl($url){
        try {
            $answer = $this->client->get($url, ['http_errors' => false]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);
        }
    }

    public function ResponseExceptionHandler($e){
        $res = new Response();

        return $res->customResponse($e, 500, false, $e->getMessage());
    }

    public function ResponseHandler($response){
        $res = new Response();

        $body = $response->getBody()->getContents();
        $responseData = json_decode($body);
        $statusCode = $response->getStatusCode();
        $statusCondition = $statusCode < 400;

        return $res->customResponse($responseData, $statusCode, $statusCondition);
    }

}
