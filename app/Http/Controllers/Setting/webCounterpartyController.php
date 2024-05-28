<?php

namespace App\Http\Controllers\Setting;

use App\Clients\newClient;
use App\Clients\oldMoySklad;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CounterpartyController;
use App\Models\employeeModel;
use App\Models\Lid;
use App\Models\Notes;
use App\Models\organizationModel;
use App\Services\ChatappRequest;
use App\Services\MoySklad\LidAttributesCreateService;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class webCounterpartyController extends Controller
{

    function get(Request $request, $accountId)
    {
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";
        $model = Notes::getInformationALLAcc($accountId);


        return view('setting.notes.main', [
            'model' => $model->toArray,

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,

            'message' => $request->message ?? '',
        ]);

    }

    function save(Request $request, $accountId)
    {
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

        $is_activity_agent = $request->is_activity_agent ?? '0';
        if ($is_activity_agent == 'on') $is_activity_agent = '1';
        $notes = $request->notes ?? null;
        $is_messenger = $request->is_messenger ?? false;



        if ($notes == '1') {
            $newController = new CounterpartyController();
            $notes_check = $newController->checkRate($accountId);


            if ($notes_check->status() == 401){
                return view('setting.error', [
                    'accountId' => $accountId,
                    'isAdmin' => $isAdmin,
                    'fullName' => $fullName,
                    'uid' => $uid,

                    'message' => print_r($notes_check->getData()->error, true),
                ]);
            }
            if ($notes_check->status() == 200){
                $notes = true;
            } else $notes = false;

        }
        else {
            $notes = false;
            $is_messenger = false;
        }

        $data = [
            'accountId' => $accountId,
            'is_activity_agent' => $is_activity_agent,
            'notes' => $notes,
            'is_messenger' => $is_messenger,
            'last_start' => null,
        ];

        $model = Notes::createOrUpdate($data);

        if ($model->status) $message = '';
        else $message = $model->message;

        $lineId = [];
        $appUrl = Config::get("Global.url", null);
        $model = employeeModel::getAllEmpl($accountId);
        if ($model->toArray!=null){
            foreach ($model->toArray as $item){
                try{
                    $chatappReq = new ChatappRequest($item['employeeId']);
                    $webhooksRes = $chatappReq->getWebhooks();
                    $webhooks = $webhooksRes->data->data;
                    foreach($webhooks as $webhookItem){
                        $url = $webhookItem->url;
                        if(str_starts_with($appUrl, $url)){
                            $licenseId = $webhookItem->licenseId;
                            if(!in_array($licenseId, $lineId))
                                $lineId[] = $licenseId;
                        }
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
            'fullName' => $fullName,
            'uid' => $uid,

            'message' => $message,
        ]);

    }

}
