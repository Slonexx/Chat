<?php

namespace App\Http\Controllers\Entity;

use App\Clients\MsClient;
use App\Clients\newClient;
use App\Http\Controllers\Controller;
use App\Models\polesModel;
use App\Models\templateModel;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PopapController extends Controller
{
    public function Popup($object): Factory|View|Application
    {
        return view('popup.ViewPopap', ['Entity' => $object,]);
    }

    public function template(): Factory|View|Application
    {
        return view('popup.template');
    }

    public function searchTemplate(Request $request): JsonResponse
    {
        $data = json_decode(json_encode([
            'accountId' => $request->accountId ?? "",
            'object_Id' => $request->object_Id ?? "",
            'entity_type' => $request->entity_type ?? "",

            'name' => $request->name ?? "",
        ]));

        $client = new MsClient($data->accountId);
        try {
            $entity = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $data->entity_type . '/' . $data->object_Id);
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => true,
                'data' => $e->getMessage(),
            ]);
        }

        $res = [];
        try {
            $model = templateModel::where('accountId', $data->accountId)->where('name', 'LIKE', '%' . $data->name . '%')->get();
            if (!$model->isEmpty()) {
                foreach ($model as $item) {
                    $array = $item->toArray();

                    $polesModel = polesModel::where('accountId', $data->accountId)->where('name_uid', ($item->toArray())['name_uid'])->get();
                    if (!$polesModel->isEmpty()) {
                        foreach ($polesModel as $polesModelItem) {
                            $polesModelItemToArray = $polesModelItem->toArray();
                            $array['message'] = $this->messageUpdate($client, $entity, $array['message'], $polesModelItemToArray['i'], $polesModelItemToArray['pole'], $polesModelItemToArray['add_pole']);
                        }
                    }
                    $res[] = $array;
                }
            }
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => true,
                'data' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $res,
        ]);

    }

    public function messenger(Request $request): JsonResponse
    {
        $data = json_decode(json_encode([
            'accountId' => $request->accountId ?? '',
            'object_Id' => $request->object_Id ?? '',
            'entity_type' => $request->entity_type ?? '',

            'license_id' => $request->license_id ?? '',
            'license_full' => $request->license_full ?? '',
            'employee' => $request->employee ?? '',
            'agent' => $request->agent ?? '',
            'phone' => $request->phone ?? '',
        ]));


        $newClient = new newClient($data->employee);
        $messenger = [];


        try {
            $linesChatApp = json_decode($newClient->licenses()->getBody()->getContents());
        } catch (BadResponseException $e) {
            if ($e->getCode() == 403) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ошибка токена сотрудника, просьба зайти в приложение в раздел " Сотрудники и доступы ", напротив сотрудника нажмите на кнопку "изменить", после в всплывающем окне нажмите на кнопку "изменить"',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Ошибка получение линий в chatApp в шаблоне сообщений, просьба сообщить разработчиком',
                    'data' => $e->getResponse()->getBody()->getContents(),
                ]);
            }
        }


        foreach ($linesChatApp->data as $item) {
            if ($item->licenseId == $data->license_id) {
                foreach ($item->messenger as $messengerItem) {
                    $messenger[] = [
                        'name' => $messengerItem->name,
                        'value' => $messengerItem->type
                    ];
                }
            }
        }

        return response()->json([
            'status' => true,
            'data' => $messenger
        ]);

    }

    public function information(Request $request): JsonResponse
    {
        $data = json_decode(json_encode([
            'accountId' => $request->accountId ?? '',
            'object_Id' => $request->object_Id ?? '',
            'entity_type' => $request->entity_type ?? '',

            'license_id' => $request->license_id ?? '',
            'license_full' => $request->license_full ?? '',
            'employee' => $request->employee ?? '',

            'phoneOrName' => $request->phoneOrName ?? '',
            'messenger' => $request->messenger ?? '',
            'linesId' => $request->linesId ?? '',
            'agent' => $request->agent ?? '',
            'phone' => $request->phone ?? '',
        ]));


        $msClient = new MsClient($data->accountId);
        $newClient = new newClient($data->employee);


        try {
            $msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $data->entity_type . '/' . $data->object_Id);
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Ошибка получение объекта в МоемСкладе, в шаблоне сообщений, просьба сообщить разработчиком',
                'data' => $e->getResponse()->getBody()->getContents(),
            ]);
        }

        if ($data->messenger == 'telegram') {
            if ($this->containsLetters($data->phoneOrName)) {
                try {
                    $dataChatApp = json_decode(($newClient->usersCheckTelegram($data->linesId, $data->messenger, $data->phoneOrName)->getBody()->getContents()));
                    if ($dataChatApp->data->chatId == null) {
                        return response()->json([
                            'status' => false,
                            'data' => $data,
                            'message' => 'Не смогли по имени пользователя в телеграмм, отправка не возможна',
                        ]);
                    } else $chatId = $dataChatApp->data->chatId;
                } catch (BadResponseException $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Ошибка проверки пользователя по имени в телеграмма, просьба сообщить разработчиком',
                        'data' => $e->getResponse()->getBody()->getContents(),
                    ]);
                }
            } else {
                try {
                    $dataChatApp = json_decode(($newClient->phonesCheck($data->linesId, $data->messenger, $data->phoneOrName)->getBody()->getContents()));
                    if ($dataChatApp->data->chatId == null) {
                        return response()->json([
                            'status' => false,
                            'data' => $data,
                            'message' => 'Не удается проверить по номер телефона в телеграмм, отправка не возможна',
                        ]);
                    } else $chatId = $dataChatApp->data->chatId;
                } catch (BadResponseException $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Ошибка проверки пользователя по имени в телеграмма, просьба сообщить разработчиком',
                        'data' => $e->getResponse()->getBody()->getContents(),
                    ]);
                }
            }
        }
        elseif ($data->messenger == 'grWhatsApp') {
            if (str_ends_with($data->phoneOrName, "@c.us")) $chatId = $data->phoneOrName;
            else {
                $chatId = '7' . substr($data->phoneOrName, -10);
                if (strlen($data->phoneOrName) > 16) return response()->json([
                    'status' => true,
                    'message' => 'Некорректный номер для данного мессенджера. Вы можете отправить сообщение, если только вы уверены, что в данный номер существует',
                    'data' => $data,
                ]);
            }
           /* else
            try {
                $dataChatApp = json_decode(($newClient->phonesCheck($data->linesId, $data->messenger, $data->phoneOrName)->getBody()->getContents()))->data->chatId;
                if ($dataChatApp == null) {
                    return response()->json([
                        'status' => false,
                        'data' => $data,
                        'message' => 'Ошибка проверки пользователя по номеру телефона. Вы можете отправить сообщение, если только вы уверены, что в данный мессенджер существует',
                    ]);
                } else $chatId = $dataChatApp;
            }
            catch (BadResponseException) {
                $data->chatId = $data->phoneOrName;
                return response()->json([
                    'status' => true,
                    'message' => 'Ошибка проверки пользователя по номеру телефона. Вы можете отправить сообщение, если только вы уверены, что в данный мессенджер существует',
                    'data' => $data,
                ]);
            }*/
        } else  return response()->json([
            'status' => true,
            'message' => 'Невозможно проверить по данному мессенджеру. Вы можете отправить сообщение, если только вы уверены, что в данный чат существует',
            'data' => $data,
        ]);

        $data->chatId = $chatId;

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $data = (object) [
            'accountId' => $request->accountId ?? '',
            'object_Id' => $request->object_Id ?? '',
            'entity_type' => $request->entity_type ?? '',

            'license_id' => $request->license_id ?? '',
            'license_full' => $request->license_full ?? '',
            'employee' => $request->employee ?? '',

            'doubleName' => $request->doubleName ?? '',
            'phoneOrName' => $request->phoneOrName ?? '',
            'messenger' => $request->messenger ?? '',
            'linesId' => $request->linesId ?? '',
            'agent' => $request->agent ?? '',
            'phone' => $request->phone ?? '',
            'chatId' => $request->chatId ?? '',
            'text' => $request->text ?? '',
        ];

        if ($data->chatId == '') $data->chatId = $data->phoneOrName;

        $newClient = new newClient($data->employee);

        try {
            $res = ($newClient->sendMessage($data->linesId, $data->messenger, $data->chatId, $data->text))->getBody()->getContents();
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => false,
                'data' => json_decode($e->getResponse()->getBody()->getContents())
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'data' => $data,
                'response' => $res,
            ]
        ]);
    }


    private function messageUpdate(MsClient $client, mixed $entity, string $message, $index, $pole, $add_pole): string
    {
        $word_pole_index = 'поле_' . $index;
        $word_add_pole_index = 'доп_поле_' . $index;
        $text_pole = '';
        $text_add_pole = '';

        if ($pole != null) {
            switch ($pole) {
                case '0':
                {
                    $text_pole = $entity->name;
                    break;
                }
                case '1':
                {
                    try {
                        $metaGet = $client->get($entity->organization->meta->href);
                        $text_pole = $metaGet->name;
                    } catch (BadResponseException) {
                    }
                    break;
                }

                case '2':
                {
                    if (property_exists($entity, 'deliveryPlannedMoment')) {
                        $text_pole = substr($entity->deliveryPlannedMoment, 0, -5);
                    }
                    break;
                }

                case '3':
                {
                    if (property_exists($entity, 'salesChannel')) {
                        try {
                            $metaGet = $client->get($entity->salesChannel->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException) {
                        }
                    }
                    break;
                }

                case '4':
                {
                    if (property_exists($entity, 'rate')) {
                        try {
                            $metaGet = $client->get($entity->rate->currency->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException) {
                        }
                    }
                    break;
                }

                case '5':
                {
                    if (property_exists($entity, 'store')) {
                        try {
                            $metaGet = $client->get($entity->store->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException) {
                        }
                    }
                    break;
                }

                case '6':
                {
                    if (property_exists($entity, 'contract')) {
                        try {
                            $metaGet = $client->get($entity->contract->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException) {
                        }
                    }
                    break;
                }

                case '7':
                {
                    if (property_exists($entity, 'project')) {
                        try {
                            $metaGet = $client->get($entity->project->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException) {
                        }
                    }
                    break;
                }

                case '8':
                {
                    if (property_exists($entity, 'shipmentAddress')) {
                        $text_pole = $entity->shipmentAddress;
                    }
                    break;
                }

                case '9':
                {
                    if (property_exists($entity, 'description')) {
                        $text_pole = $entity->description;
                    }
                    break;
                }

                case '10':
                {
                    if (property_exists($entity, 'state')) {
                        try {
                            $metaGet = $client->get($entity->state->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException) {
                        }
                    }
                    break;
                }

                case '11':
                {
                    if (property_exists($entity, 'sum')) {
                        $text_pole = $entity->sum / 100;
                    }
                    break;
                }

                case '12':
                {
                    if (property_exists($entity, 'agent')) {
                        try {
                            $metaGet = $client->get($entity->agent->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException) {
                        }
                    }
                    break;
                }

                default:
                    break;
            }
        }

        if ($add_pole != null) {
            if (property_exists($entity, 'attributes')) {
                foreach ($entity->attributes as $item) {
                    if ($item->id == $add_pole) {

                        if (is_bool($item->value)) {
                            if ($item) {
                                $text_add_pole = "активно";
                            } else {
                                $text_add_pole = "не активно";
                            }
                        } else {
                            $text_add_pole = $item->value;
                        }
                    }
                }

            }
        }

        // Разбиваем строку по пробелам, запятам и другим знакам препинания
        $words = preg_split('/([\s,.\{\}\(\)]+)/', $message, -1, PREG_SPLIT_DELIM_CAPTURE);

        //dd($words);

        foreach ($words as &$word) {
            if ($word == $word_pole_index) {
                $word = $text_pole;
            } elseif ($word == $word_add_pole_index) {
                $word = $text_add_pole;
            }
        }


        return implode('', $words);
    }

    private function containsLetters($inputString)
    {
        return preg_match("/[a-zA-Z]/", $inputString);
    }
}
