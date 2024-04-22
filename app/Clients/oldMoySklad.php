<?php
namespace App\Clients;

use GuzzleHttp\Client;
use App\Models\MainSettings;
use App\Services\Response;
use Exception;
use Illuminate\Support\Facades\Config;

class oldMoySklad {

    public string $token;

    public Client $client;

    public array $headers;

    public mixed $url;

    public function __construct($accountId) {
        $setting = MainSettings::where("account_id", $accountId)->first();
        if ($setting) {
            $this->token = $setting->ms_token;
            $auth = 'Bearer ' . $this->token;

            $this->headers = [
                'Authorization' => $auth,
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip'
            ];
            $this->client = new Client([
                'headers' => $this->headers
            ]);
            $this->url = Config::get('Global');
        } else {
            $this->client = new Client();
            $this->url = Config::get('Global');
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


    public function getAll($urlIdentifier){
        $url = $this->url[$urlIdentifier];
        try {
            $answer = $this->client->get($url, ['http_errors' => false]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);
        }
    }

    public function getById($urlIdentifier, $id){
        $joinedUrl = $this->url[$urlIdentifier] . $id;
        try {
            $answer = $this->client->get($joinedUrl, ['http_errors' => false]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);
        }
    }

    public function getByUrl($url){
        try {
            $answer = $this->client->get($url, ['http_errors' => false]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);
        }
    }

    public function post($urlIdentifier, $data){
        $joinedUrl = $this->url[$urlIdentifier];
        try {
            $answer = $this->client->post($joinedUrl, [
                'json' => $data,
                'http_errors' => false
            ]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);

        }
    }

    public function postById($urlIdentifier, $id, $data){
        $joinedUrl = $this->url[$urlIdentifier] . $id;
        try {
            $answer = $this->client->post($joinedUrl, [
                'json' => $data,
                'http_errors' => false
            ]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);

        }
    }

    public function postByUrl($url, $data){
        try {
            $answer = $this->client->post($url, [
                'json' => $data,
                'http_errors' => false
            ]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);

        }
    }

    public function getPrintByUrl($url, $data){
        try {
            $answer = $this->client->post($url, [
                'json' => $data,
                'http_errors' => false
            ]);
            $res = new Response();

            $responseData = $answer->getBody()->getContents();
            $statusCode = $answer->getStatusCode();

            return $res->customResponse($responseData, $statusCode, true);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);

        }
    }

    public function put($urlIdentifier, $body, $id){
        $joinedUrl = $this->url[$urlIdentifier] . $id;
        try {
            $answer = $this->client->put($joinedUrl, [
                'json' => $body,
                'http_errors' => false
            ]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);

        }
    }

    public function putWithoutId($urlIdentifier, $body){
        $url = $this->url[$urlIdentifier];
        try {
            $answer = $this->client->put($url, [
                'json' => $body,
                'http_errors' => false
            ]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);

        }
    }

    public function deleteByUrl($url){
        try {
            $answer = $this->client->delete($url, ['http_errors' => false]);
            return $this->ResponseHandler($answer);

        } catch(Exception $e) {
            return $this->ResponseExceptionHandler($e);

        }
    }

}
