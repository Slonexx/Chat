<?php

namespace App\Services;


use App\Clients\newClient;
use App\Models\employeeModel;
use GuzzleHttp\Exception\BadResponseException;

class updateAccessToken
{
    public function initialization($data): void
    {

        $Client = new newClient($data['accountId']);
        try {



            $body = json_decode(($Client->createTokenMake($data['email'],$data['password'] ,$data['appId'] ))->getBody()->getContents());
            $model = employeeModel::firstOrNew(['employeeId' => $data['employeeId']]);

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
