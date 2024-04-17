<?php

namespace App\Http\Controllers\Setting;

use App\Clients\newClient;
use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Models\employeeModel;
use App\Models\Scenario;
use Illuminate\Http\Request;

class AutomationController extends Controller
{

    function getAutomation(Request $request, $accountId)
    {
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";
        $main = employeeModel::getAllEmpl($accountId);

        if ($main->toArray == null)
            return to_route('creatEmployee', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'fullName' => $fullName,
                'uid' => $uid,
                'message' => 'Сначала пройдите настройки подключение'
            ]);


        $list_template = Scenario::getInformationALLAcc($accountId);
        if ($list_template->toArray == null)
            return to_route('scenario', [
                'isAdmin' => $isAdmin,
                'fullName' => $fullName,
                'uid' => $uid,
                'message' => 'Сначала создайте сценарии'
            ]);


        $automation = Automation::getInformationALLAcc($accountId);
        $lines = [];


        foreach ($main->toArray as $item) {
            $chatAppClient = new newClient($item['employeeId']);
            $req = $chatAppClient->licenses();
            if ($req->getStatusCode() == 200) {
                $is_line = (json_decode($req->getBody()->getContents()))->data;
                foreach ($is_line as $line) {
                    if ($line->licenseTo > time()) {
                        $lines[$item['id']][] = [
                            'licenseId' => $line->licenseId,
                            'licenseName' => $line->licenseName,
                            'name' => $line->licenseName . '#' . $line->licenseId,
                            'messenger' => $line->messenger,
                        ];
                    }
                }
            } else {
                $lines[$item['id']] = [
                    'licenseId' => 1,
                    'licenseName' => 'ошибка',
                    'name' => 'ошибка получение токена',
                    'messenger' => [
                        'type' => 0,
                        'name' => 'ошибка получение токена',
                    ],
                ];
            }
        }


        return view('setting.automation.main', [
            'employeeModel' => $main->toArray,
            'list_template' => $list_template->toArray,
            'automation' => $automation->toArray,
            'lines' => $lines,

            'accountId' => $accountId,
            'message' => $request->message ?? '',
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ]);

    }

    function postAutomation(Request $request, $accountId)
    {
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

        //dd($request->all());
        if ($request->employee_id == null) to_route('automation', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,

            'message' => 'Отсутствует информация о сотруднике, сообщите разработчикам приложения'
        ]);


        $isAutomation = Automation::getInformationALLAcc($accountId);
        $is_default = false;

        if ($request->is_default == '1' and $isAutomation->toArray != null) {
            foreach ($isAutomation->toArray as $item) {
                if ($item['is_default'] == '1' or $item['is_default'] == 1) $is_default = true;
            }

        }

        if ($is_default) to_route('automation', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,

            'message' => 'Сотрудник по умолчанию может быть только один'
        ]);

        $id = [];
        if ($isAutomation->toArray != null) {
            foreach ($isAutomation->toArray as $item) {
                if ($item['employee_id'] == $request->employee_id) $id = $item;
            }
        }


        $data = [
            'id' => $id,
            'accountId' => $accountId,
            'line' => $request->is_line ?? '0',
            'messenger' => $request->is_messenger ?? '0',
            'is_default' => $request->is_default ?? '0',
            'employee_id' => $request->employee_id ?? '0',
            'template' => $request->template ?? [],
        ];


        Automation::createOrUpdateIsArray($accountId, $data);


        return to_route('automation', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $request->fullName ?? "Имя аккаунта",
            'uid' => $request->uid ?? "логин аккаунта",
        ]);
    }

}
