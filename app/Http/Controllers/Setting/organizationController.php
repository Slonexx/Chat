<?php

namespace App\Http\Controllers\Setting;


use App\Clients\MsClient;
use App\Clients\newClient;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getBaseTableByAccountId\getMainSettingBD;
use App\Models\employeeModel;
use App\Models\organizationModel;
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
use JetBrains\PhpStorm\NoReturn;

class organizationController extends Controller
{
    public function getCreate(Request $request, $accountId): Factory|View|Application
    {
        $isAdmin = $request->isAdmin;
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

        $existingRecords = employeeModel::where('accountId', $accountId)->get();
        $Employee = [];
        if (!$existingRecords->isEmpty()) {
            foreach ($existingRecords as $record) {
                $Employee[] = $record->getAttributes();
            }
        }

        $existingRecords = organizationModel::where('accountId', $accountId)->get();
        $saveOrgan = [];
        if (!$existingRecords->isEmpty()) {
            foreach ($existingRecords as $record) {
                $saveOrgan[] = $record->getAttributes();
            }
        }

        $ms = new MsClient($accountId);
        try {
            $All = json_decode(json_encode(['id' => '0', 'name' => 'Все организации']));
            $E = $ms->get('https://api.moysklad.ru/api/remap/1.2/entity/organization')->rows;
            array_unshift($E, $All);
        } catch (BadResponseException $e) {
            return view('setting.error', [
                'message' => json_decode($e->getResponse()->getBody()->getContents()),

                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'fullName' => $fullName,
                'uid' => $uid,
            ]);
        }

        return view('setting.organization.organization', [
            'MyEmployee' => $Employee,
            'MsOrgan' => $E,
            'saveOrgan' => $saveOrgan,
            'message' => $request->message ?? '',

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ]);
    }

    public function postCreate(Request $request, $accountId): View|Factory|RedirectResponse|Application
    {
        $isAdmin = $request->isAdmin;
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

        return to_route('creatOrganization', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
            'message' => 'Настройки сохранились',
        ]);
    }


    #[NoReturn] public function getLicenses(Request $request, $accountId): JsonResponse
    {
        $employeeId = $request->employeeId ?? "";


        try {
            $client = json_decode((new newClient($employeeId))->licenses()->getBody()->getContents());
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => false,
                'data' => "Проблема с получение, токена сотрудника в ChatApp, просьба сообщить разработчиком",
            ]);
        }


        if ($client->success) {
            return response()->json([
                'status' => true,
                'data' => $client->data,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => "Проблема с получение, токена сотрудника в ChatApp, просьба сообщить разработчиком",
            ]);
        }


        return response()->json([
            'status' => false,
            'data' => 'Ошибка с индикатором сотрудника, проблема поиска',
        ]);

    }

    public function createLicenses(Request $request, $accountId): JsonResponse
    {
        $data = json_decode(json_encode($request->all()));

        if (count($data) > 0) {
            $length = count($data) - 1;
            $employeeName = '';
            $line = '';

            try {
                if ($data[0]->organId == '0') $existingRecords = organizationModel::where('accountId', $accountId)->get();
                else $existingRecords = organizationModel::where('accountId', $accountId)->where('organId', $data[0]->organId)->get();


                if (!$existingRecords->isEmpty()) {
                    foreach ($existingRecords as $record) {
                        $record->delete();
                    }
                }


                foreach ($data as $id => $item) {

                    if ($line != '') $line = $line . ', ' . $item->lineName;
                    else $line = $item->lineName;


                    if ($id == $length) {
                        $add = '';
                    } else {
                        $add = ', ';
                    }
                    $employeeName = $employeeName . $item->employeeName . $add;

                    $model = new organizationModel();

                    $model->accountId = $accountId;
                    $model->organId = $item->organId;
                    $model->organName = $item->organName;

                    $model->employeeId = $item->employeeId;
                    $model->employeeName = $item->employeeName;

                    $model->lineId = $item->lineId;
                    $model->lineName = $item->lineName;

                    $model->save();
                }
                return response()->json([
                    'status' => true,
                    'data' => [
                        'id' => $data[0]->organId,
                        'name' => $data[0]->organName,
                        'line' => $line,
                    ],
                    'message' => 'Все данные сохранились, а именно организация: ' . $data[0]->organName . ' и сотрудники: ' . $employeeName,
                ]);
            } catch (BadResponseException $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getResponse()->getBody()->getContents(),
                ]);
            }
        } else  return response()->json([
            'status' => false,
            'message' => "Отсутствуют данные",
        ]);


    }

    public function deleteLicenses(Request $request, $accountId): JsonResponse
    {
        $organId = $request->organId ?? '';

        if ($organId == '') {
            return response()->json([
                'status' => false,
                'message' => 'Данных в базе нет',
            ]);
        }

        try {
            if ($organId == '0') {
                $existingRecords = organizationModel::where('accountId', $accountId)->get();
            } else {
                $existingRecords = organizationModel::where('accountId', $accountId)->where('organId', $organId)->get();
            }


            if (!$existingRecords->isEmpty()) {
                foreach ($existingRecords as $record) {
                    $record->delete();
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Данные удалены с базы',
            ]);
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getResponse()->getBody()->getContents(),
            ]);
        }
    }


}
