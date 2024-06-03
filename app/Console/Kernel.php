<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        \App\Console\Commands\checkCounterPartyFromConversations::class,
        \App\Console\Commands\createCustomerOrder::class,
        \App\Console\Commands\createMessages::class,
        \App\Console\Commands\updateAccessTokenEmployeeChatApp::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Обновление токена доступа для чата каждые 8 часов
        $schedule->exec("php artisan updateAccessTokenEmployeeChatApp:update")->cron('* */8 * * *')->runInBackground();

        // Команда проверяет последние 50 чатов по всем мессенджерам на наличие контрагентов в МС
        $schedule->exec("php artisan counterparty:check")->cron('*/45 */1 * * *')->runInBackground();

        //Команда отправляет запрос на создание заказа покупателя
        $schedule->exec("php artisan customer_order:create")->cron('*/20 * * * *')->runInBackground();


        //Запуск джобов каждые 30 мин
        $schedule->exec("php artisan queue:work database --queue=high,low --max-time=1800")->cron('*/30 * * * *')->runInBackground();
        $schedule->exec("php artisan queue:work database --queue=high,low --max-time=2700")->cron('*/45 * * * *')->runInBackground();
        //webhook
        $schedule->exec("php artisan queue:work webhook_agent --queue=high,low --max-time=1800")->cron('*/30 * * * *')->runInBackground();
        $schedule->exec("php artisan queue:work webhook_agent_intgr --queue=high,low --max-time=1800")->cron('*/30 * * * *')->runInBackground();
        $schedule->exec("php artisan queue:work customerorder_intgr --queue=high,low --max-time=1800")->cron('*/30 * * * *')->runInBackground();

        // Прочие задачи
        $schedule->exec("php artisan telescope:prune --hours=48")->daily()->runInBackground();
        $schedule->exec("php artisan route:cache")->hourly()->runInBackground();

        // Удаление falid джобов
        $schedule->exec("php artisan  queue:flush")->daily()->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
