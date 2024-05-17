<?php

namespace App\Console\Commands;

use App\Jobs\CheckCounterparty;
use App\Models\MainSettings;
use App\Models\Notes;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class checkCounterPartyFromConversations extends Command
{
    protected $signature = 'counterparty:check';
    protected $description = 'Команда проверяет последние 50 чатов по всем мессенджерам на наличие контрагентов в МС';

    public function handle(): void
    {
        // Время блокировки в секундах (например, 1 час)
        $lockTime = 6300;

        // Ключ кеша для хранения времени последнего выполнения
        $cacheKey = 'counterparty_run';

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
            $allUsers = MainSettings::where("is_activate", true)->get()->all();
            $params = [
                "headers" => [
                    'Content-Type' => 'application/json'
                ]
            ];

            foreach ($allUsers as $item) {
                try {
                    $accountId = $item->account_id;
                    $notesCollection = Notes::where('accountId', $accountId)->where("is_activity_agent", true)->get();
                    if ($notesCollection->isNotEmpty()) {
                        $url = Config::get('Global.url') . "api/counterparty/create/$accountId";
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
