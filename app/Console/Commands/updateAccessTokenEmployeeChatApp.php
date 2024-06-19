<?php

namespace App\Console\Commands;

use App\Clients\MsClient;
use App\Clients\newClient;
use App\Models\employeeModel;
use App\Models\settingModel;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class updateAccessTokenEmployeeChatApp extends Command
{
    protected $signature = 'updateAccessTokenEmployeeChatApp:update';
    protected $description = '';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {

        $mutex = Cache::lock('lock_update', 21600);
        if ($mutex->get()) {

            $allSettings = settingModel::all();

            foreach ($allSettings as $settings) {
                if (!$this->checkClient($settings->accountId)) {
                    continue;
                }

                $employees = employeeModel::where('accountId', $settings->accountId)->get();
                foreach ($employees as $employee) {
                    $this->updateEmployeeToken($employee);
                }
            }

            $this->info('Command executed successfully.');
        }
        else $this->info('Уже был запущен');
    }

    private function checkClient(string $accountId): bool
    {
        $client = new MsClient($accountId);

        try {
            $client->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');
            return true;
        } catch (BadResponseException) {
            return false;
        }
    }

    private function updateEmployeeToken($employee): void
    {
        $data = [
            'accountId' => $employee->accountId,
            'employeeId' => $employee->employeeId,
            'email' => $employee->email,
            'password' => $employee->password,
            'appId' => $employee->appId,
        ];

        $client = new newClient($data['accountId']);

        try {
            $body = json_decode($client->createTokenMake($data['email'], $data['password'], $data['appId'])->getBody()->getContents());
            $model = employeeModel::firstOrNew(['employeeId' => $data['employeeId']]);

            $model->fill([
                'cabinetUserId' => $body->data->cabinetUserId,
                'accessToken' => $body->data->accessToken,
                'refreshToken' => $body->data->refreshToken,
            ]);

            $model->save();
        } catch (BadResponseException) {
            // Log the exception if necessary
        }
    }
}
