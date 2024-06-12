<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use stdClass;

class JobWithDelay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public mixed $params;
    public string $url;
    public string $conn;
    public int $clientTimeout;
    public int $random_min_us;
    public int $random_max_us;

    public function __construct($params, $url, $connection, object $timeObj)
    {
        $this->params = $params;
        $this->url = $url;
        $this->conn = $connection;
        $this->clientTimeout = $timeObj->timeout;
        $this->random_min_us = $timeObj->min_us;
        $this->random_max_us = $timeObj->max_us;

    }

    public function handle(): void
    {
        $client = new Client([
            'verify' => false,
            'timeout' => $this->clientTimeout
        ]);
        $delay = mt_rand($this->random_min_us, $this->random_max_us);
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
            $timeObj = new stdClass();
            $timeObj->timeout = $this->clientTimeout;
            $timeObj->min_us = $this->random_min_us;
            $timeObj->max_us = $this->random_max_us;
            JobWithDelay::dispatch($this->params, $this->url, $this->conn, $timeObj)->onConnection($this->conn)->onQueue($queue);
        }
    }
}
