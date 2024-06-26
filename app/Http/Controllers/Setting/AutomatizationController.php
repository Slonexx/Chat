<?php

namespace App\Http\Controllers\Setting;

use App\Clients\oldMoySklad;
use App\Http\Controllers\Controller;
use App\Models\employeeModel;
use App\Models\Scenario;
use App\Models\Templates;
use App\Services\HandlerService;
use App\Services\ChatApp\AutomatizationService;
use App\Services\MoySklad\Entities\EmployeeService;
use Error;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class AutomatizationController extends Controller{

    function getScenario(Request $request, $accountId){
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";
        $main = employeeModel::getAllEmpl($accountId);


        if ($main->toArray == null) {
            return to_route('creatEmployee', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'message' => 'Сначала пройдите настройки подключение'
            ]);
        }
        $client = new oldMoySklad($accountId);

        $endpoints = [
            'customerorder' => '/customerorder/metadata',
            'demand' => '/demand/metadata',
            'salesreturn' => '/salesreturn/metadata',
            'invoiceout' => '/invoiceout/metadata',
            'project' => '/project',
            'saleschannel' => '/saleschannel',
        ];

        $response = [];

        foreach ($endpoints as $key => $endpoint) {
            $result = $client->getByUrl("https://api.moysklad.ru/api/remap/1.2/entity$endpoint");
            //$defaultOption = [['id' => '0', 'name' => 'Не выбирать']];

            if (in_array($key, ['customerorder', 'demand', 'salesreturn', 'invoiceout'])) {
                if ($result->status) {
                    $result = $result->data;
                    $data = property_exists($result, 'states') ? $result->states : [];
                } else  return view('setting.error', [
                    'accountId' => $accountId,
                    'fullName' => $fullName,
                    'uid' => $uid,
                    'isAdmin' => $isAdmin,
                    'message' => $result->data
                ]);
            } else {
                if ($result->status) {
                    $result = $result->data;
                    $data = $result->meta->size > 0 ? $result->rows : [];
                } else return view('setting.error', [
                    'accountId' => $accountId,
                    'fullName' => $fullName,
                    'uid' => $uid,
                    'isAdmin' => $isAdmin,
                    'message' => $result->data
                ]);
            }

            //array_unshift($data, ...$defaultOption);
            $response[$key] = $data;
        }

        $template = Templates::getAllMainsTemplates($accountId);
        if ($template->toArray == null) return to_route('template', [
            'accountId' => $accountId,
            'isAdmin' => $request->isAdmin,
            'fullName' => $request->fullName ?? "Имя аккаунта",
            'uid' => $request->uid ?? "логин аккаунта",

            'message' => 'Сначала создайте шаблоны'
        ]);


        $Saved = Scenario::getInformationALLAcc($accountId);
        //dd($Saved);

        return view('setting.Scenario.main', [
            'arr_meta' => [
                'customerorder' => (array)$response['customerorder'],
                'demand' => (array)$response['demand'],
                'salesreturn' => (array)$response['salesreturn'],
                'invoiceout' => (array)$response['invoiceout'],
            ],
            'arr_project' => (array)$response['project'],
            'arr_saleschannel' => (array)$response['saleschannel'],
            'template' => $template->toArray,


            'SavedCreateToArray' => $Saved->toArray,


            'accountId' => $accountId,
            'message' => $request->message ?? '',
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ]);

    }

    function saveScenario(Request $request, $accountId)
    {
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";
        $newArray = [];

        foreach ($request->all() as $key => $value) {
            // Если ключ содержит один из префиксов, то извлекаем идентификатор
            if (preg_match('/^(template_|entity_|status_|saleschannel_|project_)(.*)$/', $key, $matches)) {
                // Получаем префикс и идентификатор
                $prefix = $matches[1];
                $identifier = $matches[2];
                // Создаем новую запись в новом массиве
                if (!isset($newArray[$identifier])) {
                    $newArray[$identifier] = [];
                }
                // Добавляем значение в новый массив
                $newArray[$identifier][str_replace('_', '', $prefix)] = $value;
            }
        }

        $is_set = Scenario::createOrUpdateIsArray($accountId, $newArray);

        try {
            $Client = new oldMoySklad($accountId);
            $webhookUrls = 'https://smartchatapp.kz/api/webhook';

            $webhooksResponse = $Client->getByUrl('https://api.moysklad.ru/api/remap/1.2/entity/webhook/');
            if (!$webhooksResponse->status) {
                return to_route('scenario', [
                    'accountId' => $accountId,
                    'isAdmin' => $request->isAdmin,
                    'fullName' => $request->fullName ?? "Имя аккаунта",
                    'uid' => $request->uid ?? "логин аккаунта",

                    'message' => $webhooksResponse->data
                ]);
            }
            $webhooks = $webhooksResponse->data->rows;


            $webhookExists = false;
            foreach ($webhooks as $webhook) {
                if ($webhook->url == $webhookUrls) {
                    $webhookExists = true;
                    break;
                }
            }
            if (!$webhookExists) {
                foreach ($webhooks as $webhook) {
                    if (str_contains($webhook->url, 'https://smartchatapp.kz/')) {
                        $Client->deleteByUrl($webhook->meta->href);
                    }
                }

                $entityTypes = ['demand', 'customerorder', 'invoiceout', 'salesreturn'];
                foreach ($entityTypes as $entityType) {
                    $Client->postByUrl('https://api.moysklad.ru/api/remap/1.2/entity/webhook/', [
                        'url' => $webhookUrls,
                        'action' => 'UPDATE',
                        'entityType' => $entityType,
                        'diffType' => 'FIELDS',
                    ]);
                }
            }
        } catch (BadResponseException $e) {

            return to_route('scenario', [
                'accountId' => $accountId,
                'isAdmin' => $request->isAdmin,
                'fullName' => $request->fullName ?? "Имя аккаунта",
                'uid' => $request->uid ?? "логин аккаунта",

                'message' => $e->getMessage()
            ]);
        }


        if ($is_set['status']) {
            return to_route('automation', [
                'accountId' => $accountId,
                'isAdmin' => $request->isAdmin,
                'fullName' => $request->fullName ?? "Имя аккаунта",
                'uid' => $request->uid ?? "логин аккаунта",
            ]);
        } else {
            return to_route('scenario', [
                'accountId' => $accountId,
                'isAdmin' => $request->isAdmin,
                'fullName' => $request->fullName ?? "Имя аккаунта",
                'uid' => $request->uid ?? "логин аккаунта",

                'message' => $is_set['message']
            ]);
        }
    }

    function deleteScenario(Request $request, $accountId)
    {

        $id = $request->id ?? null;
        if ($id == null) return response()->json(['status' => false, 'message' => 'Ошибка Удаления, неизвестный идентификатор']);
        try {
            $model = Scenario::find($id);
            $model->delete();
        } catch (BadResponseException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
        return response()->json(['status' => true]);
    }


}
