<?php

namespace App\Console\Commands;

use App\Jobs\CheckCounterparty;
use App\Models\Lid;
use App\Models\MainSettings;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class createCustomerOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer_order:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда отправляет запрос на создание заказа покупателя';


    public function handle(): void
    {

        // Время блокировки в секундах (например, 1 час)
        $lockTime = 1200;

        // Ключ кеша для хранения времени последнего выполнения
        $cacheKey = 'customer_order_run';

        // Получаем текущее время
        $currentTime = now()->timestamp;

        // Проверяем время последнего выполнения
        $lastRunTime = Cache::get($cacheKey, 0);

        if ($currentTime - $lastRunTime < $lockTime) {
            $this->info('Команда уже недавно выполнялась. Повторный запуск не требуется.');
            return;
        }

        // Обновляем время последнего выполнения задачи
        Cache::put($cacheKey, $currentTime, $lockTime);

        try {
            $mainSet = MainSettings::where("is_activate", true)->get()->pluck("account_id")->all();

            $params = [
                "headers" => [
                    'Content-Type' => 'application/json'
                ]
            ];
            $lids = Lid::whereIn("accountId", $mainSet)->pluck("is_activity_settings", "accountId")->all();
            foreach ($lids as $key => $item) {
                try {
                    if ($item) {
                        $url = Config::get('Global.url') . "api/customerorder/create/$key";
                        CheckCounterparty::dispatch($params, $url)->onConnection('database')->onQueue("high");
                        $this->info('Продолжение выполнения команды.');

                    }

                } catch (Exception $e) {
                    Log::info('Непредвиденная ошибка' . $e->getMessage());
                    continue;
                }
            }
        } catch (Exception $e) {
            Log::error('Ошибка при выполнении команды: ' . $e->getMessage());
        }
    }

}
