<?php

namespace App\Console\Commands;

use App\Jobs\CheckCounterparty;
use App\Models\MainSettings;
use App\Models\Notes;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class createMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда создаёт сообщения, которых нет в заметках контрагнета';

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
                $notesCollection = Notes::where("accountId", $accountId)
                    ->where("is_activity_agent", true)
                    ->get();
                if($notesCollection->isNotEmpty()){
                    $url = Config::get('Global.url') /*''*/ . "api/counterparty/import_dialogs/${$accountId}";
                    CheckCounterparty::dispatch($params, $url)->onConnection('database')->onQueue("high");
                    $this->info('Продолжение выполнения команды.');
                }
                
            } catch (Exception $e) {
                Log::info('Непредвиденная ошибка' . $e->getMessage());
                continue;
            }
        }
    }
}
