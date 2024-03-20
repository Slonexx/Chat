<?php

namespace App\Http\Controllers\initialization;

use App\Clients\MsClient;
use App\Http\Controllers\Controller;
use App\Http\Controllers\vendor\getSettingVendorController;
use App\Http\Controllers\vendor\Lib;
use App\Http\Controllers\vendor\VendorApiController;
use App\Models\MainSettings;
use App\Models\settingModel;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class indexController extends Controller
{
    public function initialization(Request $request): View|Factory|Application|RedirectResponse
    {
        $contextKey = $request->contextKey;
        if ($contextKey == null) {
            return view("main.dump");
        }
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        if (!$employee->status) {
            return to_route('error', [
                'accountId' => "errorMS",
                'message' => "Ошибка получение contextKey, просьба сообщить разработчикам приложения",
            ]);
        } else {
            $employee = $employee->data;
        }

        $accountId = $employee->accountId;
        $fullName = $employee->fullName;
        $uid = $employee->uid;

        $isAdmin = $employee->permissions->admin->view;

        return to_route('main', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ]);
    }

    public function index(Request $request, $accountId): Factory|View|Application
    {

        $isAdmin = $request->isAdmin ?? "NO";
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

        $setting = new getSettingVendorController($accountId);
        MainSettings::updateOrInsert(['accountId' => $accountId], ['account_id' => $accountId, 'ms_token' => $setting->TokenMoySklad]);
        $existingRecord = settingModel::find($accountId);

        if ($existingRecord == null) {
            $model = new settingModel();
            $model->accountId = $accountId;
            $model->tokenMs = $setting->TokenMoySklad;
            $model->save();
        } else {
            $Attributes = $existingRecord->getAttributes();
            if ($Attributes['tokenMs'] != $setting->TokenMoySklad) {
                settingModel::updateOrInsert(['accountId' => $accountId], ['accountId' => $accountId, 'tokenMs' => $setting->TokenMoySklad,]);
            }
        }

        if ($setting->status != 100) {
            $app = Lib::loadApp($accountId);
            $app->status = Lib::ACTIVATED;
            $vendorAPI = new VendorApiController();
            $vendorAPI->updateAppStatus($accountId, $app->getStatusName());
            $app->persist();
        }



        if ($setting->TokenMoySklad != null) {
            $ms = new MsClient($accountId);
            try {
                $ms->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');
            } catch (BadResponseException) {
                return view('setting.error', [
                    'message' => "Токен приложение умер, просьба сообщить разработчикам приложения",

                    'accountId' => $accountId,
                    'isAdmin' => $isAdmin,
                    'fullName' => $fullName,
                    'uid' => $uid,
                ]);
            }

        } else  return view('setting.error', [
            'message' => "Отсутствуют данные, просьба сообщить разработчикам приложения",

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ]);

        return view("main.index" , [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ] );

    }
    public function error(Request $request, $accountId): Factory|View|Application
    {

        $isAdmin = $request->isAdmin ?? "NO";
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";
        $message = $request->message ?? "Отсутствуют данные, просьба сообщить разработчикам приложения";

        return view('setting.error', [
            'message' => $message,

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ]);
    }

}
