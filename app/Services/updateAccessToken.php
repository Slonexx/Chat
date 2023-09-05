<?php

namespace App\Services;


use App\Clients\newClient;
use App\Models\employeeModel;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class updateAccessToken
{
    public function initialization($data): void
    {

        $Client = new newClient($data['accountId']);
        try {
            $body = json_decode(($Client->createTokenMake($data['email'],$data['password'] ,$data['appId'] ))->getBody()->getContents());
            $model = new employeeModel();
            $existingRecords = employeeModel::where('employeeId', $data['employeeId'] )->get();

            if (!$existingRecords->isEmpty()) {
                foreach ($existingRecords as $record) {
                    $record->delete();
                }
            }

            $model->accountId = $data['accountId'];

            $model->employeeId = $data['employeeId'];
            $model->employeeName = $data['employeeName'];

            $model->email = $data['email'];
            $model->password = $data['password'];
            $model->appId = $data['appId'];

            $model->access = $data['access'];

            $model->cabinetUserId = $body->data->cabinetUserId;
            $model->accessToken = $body->data->accessToken;
            $model->refreshToken = $body->data->refreshToken;

            $model->save();

        } catch (BadResponseException){

        }
    }
}
