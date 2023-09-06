<?php

namespace App\Http\Controllers\Entity;

use App\Clients\MsClient;
use App\Clients\newClient;
use App\Http\Controllers\Controller;
use App\Http\Controllers\vendor\VendorApiController;
use App\Models\employeeModel;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class widgetController extends Controller
{
    public function widgetObject(Request $request, $object): Factory|View|Application
    {
        //$accountId = '1dd5bd55-d141-11ec-0a80-055600047495';

        try {
           $vendorAPI = new VendorApiController();
            $employee = $vendorAPI->context($request->contextKey);
            //$client = new MsClient($accountId);
            //$employee = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/employee/e793faeb-e63a-11ec-0a80-0b4800079eb3');
        } catch (\Throwable) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => "Проблема с получением данных виджета, просьба срочно сообщить разработчиком ",
            ]);
        }


        $employeeModel = employeeModel::where('employeeId', $employee->id )->first();
        if ($employeeModel == null) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => "Данные сотрудника ".$employee->fullName." отсутствуют, просьба зайти в приложение и настроить для для него",
            ]);
        }
        $employeeModel = $employeeModel->getAttributes();
        if ($employeeModel['access'] == '1') {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => "У сотрудника ".$employee->fullName." отсутствуют доступ.",
            ]);
        }


        $accountId = $employee->accountId;
        $Client = new MsClient($accountId);

        try {
            $Client->get("https://online.moysklad.ru/api/remap/1.2/entity/employee");
        } catch (BadResponseException $e) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents()),
            ]);
        }

        return view('widget.object', [
            'accountId' => $accountId,
            'entity' => $object,
            'employee' => $employeeModel,
        ]);


    }


    public function widgetGetData(Request $request)
    {
        $accountId = $request->accountId ?? '';
        $entity_type = $request->entity_type ?? '';
        $entityId = $request->entityId ?? '';
        $employee = json_decode(json_encode($request->employee)) ?? '';


        if ($accountId != '' and $entity_type != '' and $entityId != '' and $employee != '') {
            $newClient = new newClient($employee->employeeId);
            $msClient = new MsClient($accountId);
            try {
                $license = json_decode(($newClient->licenses())->getBody()->getContents()) ;
            } catch (BadResponseException $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ошибка получение линий в ChatApp, просьба сообщить разработчиком приложения, '. $e->getMessage(),
                    'onToken' => ""
                ]);
            }

            //настройку по компании и линиям
            $license_id = 0;
            foreach ($license->data as $item) {
                if (property_exists($item, 'licenseId')){
                    $license_id = $item->licenseId;
                    break;
                }
            }
            if ($license_id == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ошибка получение линий в ChatApp',
                    'onToken' => http_build_query([
                        'api' => [
                            'access_token' => $employee->accessToken,
                        ],
                    ]),
                ]);
            }


            try {
               $documents = $msClient->get('https://online.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$entityId);
               $agent =  $msClient->get($documents->agent->meta->href);
            } catch (BadResponseException $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ошибка запроса в МС = '.$e->getMessage(),
                    'onToken' => http_build_query([
                        'api' => [
                            'access_token' => $employee->accessToken,
                        ],
                    ]),
                ]);
            }
            if (property_exists($agent, 'phone')) {
                $phone = str_replace(" ", "", $agent->phone);
                if (strpos($phone, "+7") === 0) {
                    $phone = substr($phone, 2);
                }
                if (strlen($phone) < 12) {
                    // Если меньше 12, добавьте +7
                    $phone = "+7" . $phone;
                }
                if (strlen($phone) > 12) {
                    return response()->json([
                        'status' => false,
                        'message' => "Некорректный номер контрагента",
                        'onToken' => http_build_query([
                            'api' => [
                                'access_token' => $employee->accessToken,
                            ],
                        ]),
                    ]);
                }
            }


            $all = http_build_query([
                'api' => [
                    'access_token' => $employee->accessToken,
                    'license_id' => $license_id,
                    'messenger_type' => 'grWhatsApp',
                    'crm_domain' => 'smartchatapp.kz',
                    'employee_ext_code' => $employee->employeeId,
                ],
                'crm' => [
                    'phones' => [
                        $phone,
                    ],
                    'dialogIds' => [
                        //Сделать изменения по Диалог
                    ]
                ],
            ]);

            return response()->json([
                'status' => true,
                'all' => $all,
                'onToken' => http_build_query([
                        'api' => [
                            'access_token' => $employee->accessToken,
                        ],
                    ]),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Ошибка загрузки данных'
            ]);
        }

    }


    public function LOG_widgetInfoAttributes(Request $request): View|Factory|JsonResponse|Application
    {
        $ticket_id = null;

        $accountId = $request->accountId;
        $entity_type = $request->entity_type;
        $objectId = $request->objectId;

        $url = $this->getUrlEntity($entity_type, $objectId);
        $Setting = new getSettingVendorController($accountId);
        try {
            $Client = new MsClient($Setting->TokenMoySklad);
            $body = $Client->get($url);
        } catch (BadResponseException $e) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->errors[0]->error,
            ]);
        }

        try {
            $ClientWeb = new KassClient($accountId);
            $Total = $ClientWeb->ShiftHistory(0, 50)->Data->Total;
            sleep(1);
            $json = $ClientWeb->ShiftHistory($Total - 1, 50)->Data->Shifts[0];

            if (property_exists($json, 'CloseDate')) {
                $Close = true;
            } else $Close = false;

        } catch (BadResponseException $e) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message,
            ]);
        }

        if (property_exists($body, 'attributes')) {
            foreach ($body->attributes as $item) {
                if ($item->name == 'фискальный номер (WebKassa)') {
                    if ($item->value != null) $ticket_id = $item->value;
                    break;
                }
            }
        }
        return response()->json(['ticket_id' => $ticket_id, 'Close' => $Close]);
    }


    private function getUrlEntity($enType, $enId): ?string
    {
        return match ($enType) {
            "customerorder" => "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/" . $enId,
            "demand" => "https://online.moysklad.ru/api/remap/1.2/entity/demand/" . $enId,
            "salesreturn" => "https://online.moysklad.ru/api/remap/1.2/entity/salesreturn/" . $enId,
            default => null,
        };
    }
}
