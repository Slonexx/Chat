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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mutex = Cache::lock('customer_order_create', 1500);
        if ($mutex->get()) {
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
                        $accountId = $key;
                        $url = Config::get('Global.url') . "api/customerorder/create/{$key}";
                        CheckCounterparty::dispatch($params, $url, 'get')->onConnection('database')->onQueue("high");
                        $this->info('Продолжение выполнения команды.');

                    }

                } catch (Exception $e) {
                    Log::info('Непредвиденная ошибка' . $e->getMessage());
                    continue;
                }
            }
        }
    }
}
