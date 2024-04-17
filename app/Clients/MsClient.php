<?php

namespace App\Clients;
use App\Http\Controllers\getBaseTableByAccountId\getMainSettingBD;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class MsClient{

    private Client $client;
    private getMainSettingBD $Setting;

    private mixed $URL_;

    public function __construct($accountId) {
        $this->Setting = new getMainSettingBD($accountId);
        $this->URL_ = Config::get("Global");

        $this->client = new Client([
            'headers' => [
                'Authorization' =>  $this->Setting->tokenMs,
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip',
            ]
        ]);
    }

    public function get($url){
        $res = $this->client->get($url,[
            'Accept' => 'application/json',
        ]);
        return json_decode($res->getBody());
    }

    public function post($url, $body){
        $res = $this->client->post($url,[
            'body' => json_encode($body),
        ]);

        return json_decode($res->getBody());
    }

    public function put($url, $body){
        $res = $this->client->put($url,[
            'Accept' => 'application/json',
            'body' => json_encode($body),
         ]);
         return json_decode($res->getBody());
    }

    public function delete($url, $body){
        $res = $this->client->delete($url,[
            'Accept' => 'application/json',
            'body' => json_encode($body),
        ]);
        return json_decode($res->getBody());
    }

}
