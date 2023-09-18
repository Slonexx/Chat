<?php

namespace App\Http\Controllers\Setting;


use App\Clients\MsClient;
use App\Clients\newClient;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getBaseTableByAccountId\getMainSettingBD;
use App\Models\employeeModel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class CreateAuthTokenController extends Controller
{
    public function getCreateAuthToken(Request $request, $accountId): Factory|View|Application
    {
        $isAdmin = $request->isAdmin;
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

        $existingRecords = employeeModel::where('accountId', $accountId)->get();
        $Employee = [];
        if (!$existingRecords->isEmpty()) {
            foreach ($existingRecords as $record) {
                $Employee[] = [
                    'accountId' => $record->accountId,
                    'employeeId' => $record->employeeId,
                    'employeeName' => $record->employeeName,

                    'email' => $record->email,
                    'password' => $record->password,
                    'appId' => $record->appId,

                    'access' => $record->access,

                    'cabinetUserId' => $record->cabinetUserId,
                    'accessToken' => $record->accessToken,
                    'refreshToken' => $record->refreshToken,
                ];
            }
        }

        $ms = new MsClient($accountId);
        try {
            $E = $ms->get('https://api.moysklad.ru/api/remap/1.2/entity/employee')->rows;
        } catch (BadResponseException $e) {
            return view('setting.error', [
                'message' => json_decode($e->getResponse()->getBody()->getContents()),

                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'fullName' => $fullName,
                'uid' => $uid,
            ]);
        }

        return view('setting.mainSetting.authToken', [
            'MsEmployee' => $E,
            'MyEmployee' => $Employee,

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ]);
    }

    public function postCreateAuthToken(Request $request, $accountId): View|Factory|RedirectResponse|Application
    {
        $isAdmin = $request->isAdmin;
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

            return to_route('creatOrganization', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'fullName' => $fullName,
                'uid' => $uid,
            ]);
    }


    public function getEmployee(Request $request, $accountId): JsonResponse
    {
        $employeeId = $request->employee ?? "";

        $existingRecords = employeeModel::where('employeeId', $employeeId)->first();

        if ($existingRecords != null) {
                return response()->json([
                    'accountId' => $existingRecords->accountId,
                    'employeeId' => $existingRecords->employeeId,
                    'employeeName' => $existingRecords->employeeName,

                    'email' => $existingRecords->email,
                    'password' => $existingRecords->password,
                    'appId' => $existingRecords->appId,

                    'access' => $existingRecords->access,

                    'cabinetUserId' => $existingRecords->cabinetUserId,
                    'accessToken' => $existingRecords->accessToken,
                    'refreshToken' => $existingRecords->refreshToken,
                ]);
        }


        return response()->json([
        'status' => 500,
        'message' => 'Ошибка с индикатором сотрудника, проблема поиска',
    ]);

    }
    public function createEmployee(Request $request, $accountId): JsonResponse
    {
        $employeeId = $request->employee ?? "";
        $employeeName = $request->employeeName ?? "";
        $email = $request->email ?? "";
        $password = $request->password ?? "";
        $appId = $request->appId ?? "";
        $access = $request->access ?? "";

        $Client = new newClient($accountId);

        if ($email != "" and $password != "" and $appId != null and $access != "" and $employeeName != "") {
            try {
                $body = json_decode(($Client->createTokenMake($email, $password, $appId))->getBody()->getContents());
                $model = new employeeModel();
                $existingRecords = employeeModel::where('employeeId', $employeeId)->get();

                if (!$existingRecords->isEmpty()) {
                    foreach ($existingRecords as $record) {
                        $record->delete();
                    }
                }

                $model->accountId = $accountId;

                $model->employeeId = $employeeId;
                $model->employeeName = $employeeName;

                $model->email = $email;
                $model->password = $password;
                $model->appId = $appId;

                $model->access = $access;

                $model->cabinetUserId = $body->data->cabinetUserId;
                $model->accessToken = $body->data->accessToken;
                $model->refreshToken = $body->data->refreshToken;

                $model->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Данный аккаунт есть в ChatApp, вы можете нажимать на кнопку "Добавить"',
                ]);

            } catch (BadResponseException $e) {
                $getContents = json_decode($e->getResponse()->getBody()->getContents());

                if ($getContents->error->message == 'The email must be a valid email address.') {
                    return response()->json([
                        'status' => 500,
                        'message' => 'Электронная почта должна быть действительным адресом электронной почты.',
                    ]);
                }
                elseif ($getContents->error->message == 'User does not exist') {
                    return  response()->json([
                        'status' => 500,
                        'message' => 'Пользователь не существует',
                    ]);
                }
                elseif ($getContents->error->message == 'User password is incorrect') {
                    return response()->json([
                        'status' => 500,
                        'message' => 'Пароль пользователя неверен',
                    ]);
                }
                elseif ($getContents->error->message == 'APP ID is incorrect') {
                    return response()->json([
                        'status' => 500,
                        'message' => 'Идентификатор приложения неверен (APP ID)',
                    ]);
                }
                else return response()->json([
                    'status' => 500,
                    'message' => $getContents->error->message,
                ]);
            }

        } else return response()->json([
            'status' => 500,
            'message' => 'Ошибка ввода данных, пожалуйста введите данные',
        ]);


    }
    public function deleteEmployee(Request $request, $accountId): JsonResponse
    {
        $employeeId = $request->employee ?? "";

        $existingRecords = employeeModel::where('employeeId', $employeeId)->get();

        if (!$existingRecords->isEmpty()) {
            foreach ($existingRecords as $record) {
                $record->delete();
            }
            return response()->json([
                'status' => 200,
                'message' => 'данные удалены',
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Данных нет',
            ]);
        }


    }

}
