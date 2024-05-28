<?php

namespace App\Http\Controllers\Setting;

use App\Clients\newClient;
use App\Clients\oldMoySklad;
use App\Http\Controllers\Controller;
use App\Models\employeeModel;
use App\Models\Lid;
use App\Models\organizationModel;
use App\Services\ChatappRequest;
use App\Services\MoySklad\LidAttributesCreateService;
use Error;
use Exception;
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

        $msClient = new oldMoySklad($accountId);
        $req = $msClient->getAll('employee');
        if ($req->status){
            $employee = $req->data->rows;
        } else return to_route('error', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $request->fullName ?? "Имя аккаунта",
            'uid' => $request->uid ?? "логин аккаунта",
            'message' => $req->data
        ]);


        $req = $msClient->getByUrl(Config::get("Global.moyskladJsonApiEndpointUrl").'/entity/organization');
        $organization = [];
        foreach ($req->data->rows as $item){
            $req = $msClient->getByUrl(Config::get("Global.moyskladJsonApiEndpointUrl").'/entity/organization/'.$item->id.'?expand=accounts');
            $organization[] = $req->data;
        }

        $states = $msClient->getByUrl(Config::get("Global.moyskladJsonApiEndpointUrl").'/entity/customerorder/metadata');
        $states = $states->data->states;


        $project = $msClient->getByUrl(Config::get("Global.moyskladJsonApiEndpointUrl").'/entity/project');
        $project = $project->data->rows;

        $saleschannel = $msClient->getByUrl(Config::get("Global.moyskladJsonApiEndpointUrl").'/entity/saleschannel');
        $saleschannel = $saleschannel->data->rows;

        $model = (Lid::getInformationALLAcc($accountId));


        return view('setting.LID.main', [
            'employee' => $employee,
            'organization' => $organization,
            'states' => $states,
            'project' => $project,
            'saleschannel' => $saleschannel,

            'model' => $model->toArray,

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



        $project_uid = $request->project_uid ?? null;
        if ($project_uid != null and $project_uid == 0) $project_uid = null;

        $sales_channel_uid = $request->sales_channel_uid ?? null;
        if ($sales_channel_uid != null and $sales_channel_uid == 0) $sales_channel_uid = null;

        //Проверка на task
        $tasks = $request->tasks ?? null;
        if ($tasks != null and $tasks == 0) $tasks = null;

        $data = [
            'accountId' => $accountId,
            'is_activity_settings' => $is_activity_settings,
            'is_activity_order' => $orderIsActive ?? '0',
            'lid' => 'lid',
            'responsible' => $request->responsible ?? '0',
            'responsible_uuid' => $request->responsible_uuid ?? null,
            'tasks' => $tasks,

            'organization' => $request->organization ?? null,
            'organization_account' => $request->organization_account ?? null,
            'states' => $request->states ?? null,
            'project_uid' => $project_uid,
            'sales_channel_uid' => $sales_channel_uid,
        ];

        $model = Lid::createOrUpdate($data);

        if ($model->status) $message = '';
        else $message = $model->message;



        $lineId = [];
        $model = employeeModel::getAllEmpl($accountId);
        if ($model->toArray!=null){
            foreach ($model->toArray as $item){
                try{
                    $chatappReq = new ChatappRequest($item['employeeId']);
                    $webhooksRes = $chatappReq->getWebhooks();
                    $webhooks = $webhooksRes->data->data;
                    if(!empty($webhooks)){
                        $licenses = array_column($webhooks, "licenseId");
                        $lineId[] = array_unique($licenses);
                    }
                } catch(Exception | Error){
                    continue;
                }
            }
            $messengers = ['telegram', 'telegramBot', 'avito', 'vkontakte', 'grWhatsApp', 'email', 'instagram'];
            foreach ($model->toArray as $item){
                $client = new newClient($item['employeeId']);
                $org_model = organizationModel::getInformationByEmployee($item['employeeId']);
                if ($org_model->toArray!=null){
                    foreach ($org_model->toArray as $item_org){
                        if (!in_array($item_org['lineId'], $lineId)){
                            $lineId[] = $item_org['lineId'];
                            $licenses = $item_org['lineId'];
                            foreach ($messengers as $messenger){
                                $urlCallBack = 'https://smartchatapp.kz/api/webhook/'.$accountId.'/licenses/'.$licenses.'/messengers/'.$messenger;
                                $res = $client->putCallbackUrls($urlCallBack, $licenses, $messenger);
                                if (!$res->status and $res->statusCode == 403) break 2;
                            }
                        }
                    }
                }
            }

        }


        return to_route('counterparty', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $request->fullName ?? "Имя аккаунта",
            'uid' => $request->uid ?? "логин аккаунта",

            'message' => $message,
        ]);

    }

}
