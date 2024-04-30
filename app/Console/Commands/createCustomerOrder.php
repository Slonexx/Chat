<?php

namespace App\Console\Commands;

use App\Jobs\CheckCounterparty;
use App\Models\employeeModel;
use App\Models\Lid;
use App\Models\MainSettings;
use App\Models\organizationModel;
use Exception;
use Illuminate\Console\Command;
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
        $mainSet = MainSettings::where("is_activate", true)->get()->pluck("account_id")->all();

        $params = [
            "headers" => [
                'Content-Type' => 'application/json'
                ]
            ];
        $lids = Lid::whereIn("accountId", $mainSet)->pluck("is_activity_settings", "account_id")->all();
        foreach($lids as $key => $item){
            try {
                if($item){
                    $accountId = $item->account_id;
                    $url = Config::get('Global.url') /*''*/ . "api/customerorder/create/${$key}";
                    //CheckCounterparty::dispatch($params, $url)->onConnection('database')->onQueue("high");
                    
                    $this->info('Продолжение выполнения команды.');

                }
                
            } catch (Exception $e) {
                Log::info('Непредвиденная ошибка' . $e->getMessage());
                continue;
            }
        }
    }
}
