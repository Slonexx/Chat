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
                

                // $edoClient = new UdoClient($item->accountId, $item->ms_uid);
                // $res = $edoClient->getAll("usersInfo");
                // if($res->statusCode != 200){
                //     $body = (object) [
                //         "username" => $item->login,
                //         "password" => $item->password,
                //     ];
                //     $res = $edoClient->postForm("accessToken", $body);
                //     if(!$res->status)
                //         Log::info("У пользователя {$item->accountId} неправильный логин и/или пароль");
                //     else {
                //         $users = Users::where("accountId", $item->accountId)
                //             ->where("ms_uid", $item->ms_uid)
                //             ->get();
                //         if(count($users) == 0)
                //             Log::info("Пользователь {$item->accountId} не найден");
                //         if(count($users) > 1)
                //             Log::info("У пользователя {$item->accountId} две одинаковых записи в Users");


                //         $userInstance = $users->get(0);

                //         $userInstance["udo_token"] = $res->data->access_token;
            
                //         $userInstance->save();

                  //  }
                //}
            } catch (Exception $e) {
                Log::info('Непредвиденная ошибка' . $e->getMessage());
                continue;
            }
        }
    }
}
