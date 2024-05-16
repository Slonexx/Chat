<?php

namespace App\Http\Controllers\Setting;

use App\Clients\oldMoySklad;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CounterpartyController;
use App\Models\Lid;
use App\Models\Notes;
use App\Services\MoySklad\LidAttributesCreateService;
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

        return to_route('counterparty', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,

            'message' => $message,
        ]);

    }

}
