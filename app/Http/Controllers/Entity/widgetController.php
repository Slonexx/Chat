<?php

namespace App\Http\Controllers\Entity;

use App\Clients\MsClient;
use App\Clients\newClient;
use App\Http\Controllers\Controller;
use App\Http\Controllers\vendor\VendorApiController;
use App\Models\employeeModel;
use App\Models\organizationModel;
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
            if (!$employee->status) {

                return view('widget.Error', [
                    'status' => false,
                    'code' => 400,
                    'message' => "Проблема с получением данных виджета, просьба срочно сообщить разработчиком ",
                ]);
            } else {
                $employee = $employee->data;
            }
            //$client = new MsClient($accountId);
            //$employee = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/employee/e793faeb-e63a-11ec-0a80-0b4800079eb3');
        } catch (BadResponseException) {
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
            $Client->get("https://api.moysklad.ru/api/remap/1.2/entity/employee");
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


    public function widgetGetData(Request $request): JsonResponse
    {
        $accountId = $request->accountId ?? '';
        $entity_type = $request->entity_type ?? '';
        $entityId = $request->entityId ?? '';
        $employee = json_decode(json_encode($request->employee ?? null)) ?? '';


        if ($accountId != '' and $entity_type != '' and $entityId != '' and $employee != '') {
            $newClient = new newClient($employee->employeeId);
            $msClient = new MsClient($accountId);
            try {
                $license = json_decode(($newClient->licenses())->getBody()->getContents()) ;
            } catch (BadResponseException $e) {
                $error = json_decode($e->getResponse()->getBody()->getContents());

                if ($error->error->code == 'ApiInvalidTokenError') {
                    return response()->json([
                        'status' => false,
                        'message' => 'Ошибка токена сотрудника, просьба зайти в приложение в раздел " Сотрудники и доступы ", напротив сотрудника '.
                            $employee->employeeName.' нажмите на кнопку "изменить", после в всплывающем окне нажмите на кнопку "изменить"',
                        'onToken' => ""
                    ]);
                } else
                return response()->json([
                    'status' => false,
                    'message' => 'Ошибка получение линий в ChatApp, просьба сообщить разработчиком приложения, '. $e->getMessage(),
                    'onToken' => ""
                ]);
            }
            if ($license->data == []) {
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
               $documents = $msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$entityId);
               if ($entity_type == 'counterparty') {
                   $agent = $documents;
               } else {
                   $agent =  $msClient->get($documents->agent->meta->href);
               }

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
            if ($entity_type == 'counterparty') {
                $organId = 0;
            } else {
                $organId = basename($documents->organization->meta->href);
            }

            $license_id = 0;

            $existingRecords = organizationModel::where('accountId', $accountId)->where('employeeId', $employee->employeeId)->get();
            if (!$existingRecords->isEmpty()) {
                foreach ($existingRecords as $record) {
                    if ($record->organId == 0) {
                        $license_id = $record->lineId;
                        break;
                    }
                    if ($record->organId == $organId){
                        $license_id = $record->lineId;
                        break;
                    }
                }
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
                        'message' => "Некорректный номер телефона контрагента",
                        'onToken' => http_build_query([
                            'api' => [
                                'access_token' => $employee->accessToken,
                            ],
                        ]),
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Отсутствует номер телефона контрагента",
                    'onToken' => http_build_query([
                        'api' => [
                            'access_token' => $employee->accessToken,
                        ],
                    ]),
                ]);
            }


            $all = [
                'api' => [
                    'access_token' => $employee->accessToken,
                    'license_id' => $license_id,
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
            ];
            if ($all['api']['license_id'] == 0) {
                unset($all['api']['license_id'] );
            }

            return response()->json([
                'status' => true,
                'all' => http_build_query($all),
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


}
