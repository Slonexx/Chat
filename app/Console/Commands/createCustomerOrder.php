<?php

namespace App\Console\Commands;

use App\Jobs\CheckCounterparty;
use App\Models\employeeModel;
use App\Models\Lid;
use App\Models\MainSettings;
use App\Models\organizationModel;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class createCustomerOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'counterparty:check';

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
        
        $org = organizationModel::all()->all();
        $empl = employeeModel::all()->all();
        $lid = Lid::all()->all();
        foreach($org as $orgItem){
            $lidSetting = array_filter($lid, fn($val)=> $val->account_id == $orgItem->accountId
                && $val->is_activity_settings == true);
            if(count($lidSetting) == 0)
                continue;

            $employeesCurrentOrg = array_filter($empl, fn($val)=> $val->employeeId == $orgItem->employeeId);
            $employees = [];
            $employees[] = (array)$employeesCurrentOrg;
        }
        $params = [
            "headers" => [
                'Content-Type' => 'application/json'
                ]
            ];
            
        foreach ($employees as $item) {
            try {
                $accountId = $item->accountId;
                $employeeId = $item->employeeId;
                $url = /*Config::get('Global.url')*/ '' . "api/customerorder/create/${$accountId}/${$employeeId}";
                //CheckCounterparty::dispatch($params, $url)->onConnection('database')->onQueue("high");
                $this->info('Продолжение выполнения команды.');
                
            } catch (Exception $e) {
                Log::info('Непредвиденная ошибка' . $e->getMessage());
                continue;
            }
        }
    }
}
