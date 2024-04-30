<?php
namespace App\Clients;

use GuzzleHttp\Client;
use App\Models\MainSettings;
use InvalidArgumentException;
use React\Http\Browser;

class MoySkladAsync {

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

    public function getAsync($url, $loop){
        $browser = new Browser($loop);
        return $browser->get($url, $this->headers["headers"]);
    }

    public function postAsync($url, $data, $loop){
        $browser = new Browser($loop);
        return $browser->post($url, $this->headers, $data);
    }

    // public function put($url, $body){
    //     return $this->client->put($url, [
    //         'json' => $body
    //     ]);
    // }

    // public function delete($url){
    //     return $this->client->delete($url);
    // }

}
