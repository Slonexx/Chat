<?php

namespace App\Http\Controllers\Setting;

use App\Clients\MoySklad;
use App\Clients\MsClient;
use App\Clients\newClient;
use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Models\employeeModel;
use App\Models\Lid;
use App\Models\Scenario;
use App\Services\MoySklad\AgentControllerLogicService;
use App\Services\MoySklad\LidAttributesCreateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class LidController extends Controller
{

    function getLid(Request $request, $accountId)
    {
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";
        //$main = employeeModel::getAllEmpl($accountId);

        $msClient = new MoySklad($accountId);
        $req = $msClient->getAll('employee');
        if ($req->status){
            $employee = $req->data->rows;
        } else return to_route('error', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $request->fullName ?? "Имя аккаунта",
            'uid' => $request->uid ?? "логин аккаунта",
            'message' => "",
        ]);
        //dd($employee);






        return view('setting.LID.main', [
            'employee' => $employee,


            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,

            'message' => $request->message ?? '',
        ]);

    }

    function saveLid(Request $request, $accountId)
    {
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

        $is_activity_settings = $request->is_activity_settings ?? '0';
        if ($is_activity_settings == 'on') $is_activity_settings = '1';

        $orderIsActive = $request->is_activity_order;

        $attributesS = new LidAttributesCreateService($accountId);
        $serviceFieldsNames = [
            "lid",
        ];
        $config = Config::get("lidAttributes");
        $serviceFields = array_filter($config, fn($key)=> in_array($key, $serviceFieldsNames), ARRAY_FILTER_USE_KEY);
        $findOrCreateRes = $attributesS->findOrCreate($serviceFields, $orderIsActive);
        if(isset($findOrCreateRes)){
            if(!$findOrCreateRes->status)
                return to_route('lid', [
                    'accountId' => $accountId,
                    'isAdmin' => $isAdmin,
                    'fullName' => $fullName ?? "Имя аккаунта",
                    'uid' => $uid ?? "логин аккаунта",
        
                    'message' => $findOrCreateRes->message,
                ]);

        }

        $data = [
            'accountId' => $accountId,
            'is_activity_settings' => $is_activity_settings,
            'is_activity_order' => $orderIsActive ?? '0',
            'lid' => 'lid',
            'responsible' => $request->responsible ?? '0',
            'responsible_uuid' => $request->responsible_uuid ?? null,
        ];


        $model = Lid::createOrUpdate($data);

        if ($model->status) $message = '';
        else $message = $model->message;


        return to_route('lid', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $request->fullName ?? "Имя аккаунта",
            'uid' => $request->uid ?? "логин аккаунта",

            'message' => $message,
        ]);

    }

}
