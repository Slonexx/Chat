<?php

namespace App\Console\Commands;

use App\Jobs\CheckCounterparty;
use App\Models\employeeModel;
use App\Models\MainSettings;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class checkCounterPartyFromConversations extends Command
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
    protected $description = 'Команда проверяет последние 50 чатов по всем мессенджерам на наличие контрагентов в МС';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $allUsers = MainSettings::where("is_activate", true)->get()->all();
        $params = [
            "headers" => [
                'Content-Type' => 'application/json'
                ]
            ];
            
        foreach ($allUsers as $item) {
            try {
                $accountId = $item->accountId;
                $url = /*Config::get('Global.url')*/ '' . "api/counterparty/create/${$accountId}";
                CheckCounterparty::dispatch($params, $url)->onConnection('database')->onQueue("high");
                $this->info('Продолжение выполнения команды.');
                
            } catch (Exception $e) {
                Log::info('Непредвиденная ошибка' . $e->getMessage());
                continue;
            }
        }
    }
}
