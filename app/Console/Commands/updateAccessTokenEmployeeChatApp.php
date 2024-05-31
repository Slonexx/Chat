<?php

namespace App\Console\Commands;

use App\Clients\MsClient;
use App\Models\employeeModel;
use App\Models\settingModel;
use App\Services\updateAccessToken;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Console\Command;

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
        $allSettings = settingModel::all();
        foreach ($allSettings as $settings) {

            try {
                $ClientCheckMC = new MsClient($settings->accountId);
                $ClientCheckMC->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');
            } catch (BadResponseException) {continue;}

            $employeeModelsWhereAccountId = employeeModel::where('accountId', $settings->accountId )->get();

            foreach ($employeeModelsWhereAccountId as $employee) {
                $data = [
                    'accountId' => $employee->accountId,

                    'employeeId' => $employee->employeeId,
                    'employeeName' => $employee->employeeName,

                    'email' => $employee->email,
                    'password' => $employee->password,
                    'appId' => $employee->appId,

                    'access' => $employee->access,

                    'cabinetUserId' => $employee->cabinetUserId,
                    'accessToken' => $employee->accessToken,
                    'refreshToken' => $employee->refreshToken,

                ];

                (new updateAccessToken())->initialization($data);
            }



            $this->info('Command executed successfully.');
        }


    }

}
