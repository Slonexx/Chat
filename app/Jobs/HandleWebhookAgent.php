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

    // Установка количества попыток и тайм-аута
    public int $timeout = 600;

    public function __construct($params, $url)
    {
        $this->params = $params;
        $this->url = $url;
    }

    public function handle(): void
    {
        $connection = "webhook_agent";
        $queue = "high";
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
            $this->handleClientException($e, $connection, $queue);
            return;
        } catch (RequestException) {
            return;
        }
    }

    private function handleClientException(ClientException $e, $connection, $queue): void
    {
        $msError = "Превышено ограничение на количество запросов в единицу времени";
        $statusCode = $e->getResponse()->getStatusCode();
        $body_encoded = $e->getResponse()->getBody()->getContents();
        $body = json_decode($body_encoded);
        $data = $body->data ?? false;
        $inputMessage = $data->errors[0]->error ?? false;

        if ($statusCode == 429 && $inputMessage == $msError) {
            HandleWebhookAgent::dispatch($this->params, $this->url)->onConnection($connection)->onQueue($queue);
        }
    }
}
