<?php

namespace App\Http\Controllers\initialization;

use App\Clients\MsClient;
use App\Http\Controllers\Controller;
use App\Http\Controllers\vendor\getSettingVendorController;
use App\Http\Controllers\vendor\Lib;
use App\Http\Controllers\vendor\VendorApiController;
use App\Models\employeeModel;
use App\Models\settingModel;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class indexController extends Controller
{
    public function initialization(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $contextKey = $request->contextKey;
        if ($contextKey == null) {
            return view("main.dump");
        }
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);

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

    public function index(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        $isAdmin = $request->isAdmin;
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

        $setting = new getSettingVendorController($accountId);
        $employeeId = employeeModel::where('employeeId', 'e793faeb-e63a-11ec-0a80-0b4800079eb3')->first();;

      /*  if ($setting->TokenMoySklad != null) {
            $existingRecord = settingModel::where('accountId', $accountId)->first();

            if ($existingRecord) {
                settingModel::updateOrInsert(['accountId' => $accountId], ['accountId' => $accountId, 'tokenMs' => $setting->TokenMoySklad,]);
            } else {
                $model = new settingModel();
                $model->accountId = $accountId;
                $model->tokenMs = $setting->TokenMoySklad;
                $model->save();
            }

            $ms = new MsClient($accountId);
            try {
                $ms->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');


                $apps = json_decode(json_encode(Config::get("Global")))->appId;

                $app = Lib::loadApp($apps, $accountId);
                $app->status = Lib::ACTIVATED;
                $vendorAPI = new VendorApiController();
                $vendorAPI->updateAppStatus($apps, $accountId, $app->getStatusName());
                $app->persist();

            } catch (BadResponseException $e) {
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
        ]);*/

        $fields = [
            'api' => [
                'access_token' => $employeeId->accessToken,
                'license_id' => 36651,
                'messenger_type' => 'grWhatsApp',
                'crm_domain' => 'smartInnovation',
                'employee_ext_code' => '123',
            ],
            'crm' => [
                'phones' => [
                ],
                'dialogIds' => [
                ]
            ],
        ];

        return view("main.index" , [
            'query' => $fields,

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ] );

    }

}
