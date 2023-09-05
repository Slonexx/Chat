<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class VendorApiController extends Controller
{
    public function context(string $contextKey)
    {
        return $this->request('POST', "/context/{$contextKey}");
    }

    public function updateAppStatus(string $appId, string $accountId, string $status)
    {
        return $this->request('PUT', "/apps/{$appId}/{$accountId}/status", ["status" => $status]);
    }

    private function request(string $method, string $path, $body = null)
    {
        $url = Config::get("Global.moyskladVendorApiEndpointUrl") . $path;
        $bearerToken = $this->buildJWT();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$bearerToken}",
            'Content-type' => 'application/json',
        ])->send($method, $url, $body);

        return $response->json();
    }

    private function buildJWT(): string
    {
        $appUid = Config::get("Global.appUid");
        $secretKey = Config::get("Global.secretKey");

        $token = [
            "sub" => $appUid,
            "iat" => time(),
            "exp" => time() + 300,
            "jti" => bin2hex(random_bytes(32)),
        ];

        return JWT::encode($token, $secretKey, 'HS256'); // Provide the algorithm 'HS256'
    }
}
