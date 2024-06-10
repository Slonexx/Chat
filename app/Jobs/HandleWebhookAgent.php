<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleWebhookAgent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public mixed $params;
    public string $url;
    public string $conn;

    public function __construct($params, $url, $connection)
    {
        $this->params = $params;
        $this->url = $url;
        $this->conn = $connection;
    }

    public function handle(): void
    {
        $client = new Client([
            'verify' => false,
            'timeout' => 1200
        ]);
        $delay = mt_rand(20000, 500000);
        usleep($delay);
        try {
            $client->post($this->url, $this->params);
            return; // Успешное выполнение, выходим из функции
        } catch (ClientException $e) {
            $queue = "low";
            $this->handleClientException($e, $queue);
            return;
        } catch (RequestException) {
            return;
        }
    }

    private function handleClientException(ClientException $e, $queue): void
    {
        $msError = "Превышено ограничение на количество запросов в единицу времени";
        $statusCode = $e->getResponse()->getStatusCode();
        $body_encoded = $e->getResponse()->getBody()->getContents();
        $body = json_decode($body_encoded);
        $data = $body->data ?? false;
        $inputMessage = $data->errors[0]->error ?? false;

        if ($statusCode == 429 && $inputMessage == $msError) {
            HandleWebhookAgent::dispatch($this->params, $this->url, $this->conn)->onConnection($this->connection)->onQueue($queue);
        }
    }
}
