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

class CheckCounterparty implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public mixed $params;
    public string $url;
    public string $method;

    // Установка количества попыток и тайм-аута
    public int $tries = 5;
    public int $timeout = 600;

    public function __construct($params, $url, $method = 'GET')
    {
        $this->params = $params;
        $this->url = $url;
        $this->method = $method;
    }

    public function handle()
    {
        $client = new Client([
            'verify' => false,
            'timeout' => 1200
        ]);

        $attempts = 3;
        $delay = 20000;

        for ($i = 0; $i < $attempts; $i++) {
            try {
                $method = $this->method;
                $client->$method($this->url, $this->params);
                return; // Успешное выполнение, выходим из функции
            } catch (ClientException $e) {
                $this->handleClientException($e);
                return;
            } catch (RequestException $e) {
                if ($i == $attempts - 1) {
                    throw $e; // Повторные попытки исчерпаны, выбрасываем исключение
                }
                usleep($delay);
                $delay *= 2; // Увеличиваем задержку экспоненциально
            }
        }
    }

    private function handleClientException(ClientException $e)
    {
        $msError = "Превышено ограничение на количество запросов в единицу времени";
        $statusCode = $e->getResponse()->getStatusCode();
        $body_encoded = $e->getResponse()->getBody()->getContents();
        $body = json_decode($body_encoded);
        $data = $body->data ?? false;
        $inputMessage = $data->errors[0]->error ?? false;

        if ($statusCode == 429 && $inputMessage == $msError) {
            CheckCounterparty::dispatch($this->params, $this->url)->onConnection('database')->onQueue("low");
        }
    }
}

