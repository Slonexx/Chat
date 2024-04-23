<?php
namespace App\Services;

use App\Services\Response;
use Psr\Http\Message\ResponseInterface;

class HTTPResponseHandler{

    public function ResponseExceptionHandler($e){
        $res = new Response();

        return $res->customResponse($e, 500, false, $e->getMessage());
    }

    public function handleOK(ResponseInterface $response, string $message = null){
        $res = new Response();

        $body = $response->getBody()->getContents();
        $responseData = json_decode($body);
        $statusCode = $response->getStatusCode();
        $status = true;

        return $res->customResponse($responseData, $statusCode, $status, $message);
    }
}