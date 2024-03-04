<?php

namespace App\Http\Controllers\Setting;


use App\Clients\MsClient;
use App\Clients\newClient;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getBaseTableByAccountId\getMainSettingBD;
use App\Models\employeeModel;
use App\Models\organizationModel;
use App\Models\polesModel;
use App\Models\templateModel;
use App\Services\MoySklad\TemplateService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;

class templateController extends Controller
{
    public function getCreate(Request $request, $accountId): Factory|View|Application
    {
        $isAdmin = $request->isAdmin;
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

        $existingRecords = organizationModel::where('accountId', $accountId)->get();
        $saveOrgan = [];
        if (!$existingRecords->isEmpty()) {
            foreach ($existingRecords as $record) {
                $saveOrgan[] = $record->getAttributes();
            }
        }
        if ($saveOrgan == []) {
            return view('setting.error', [
                'message' => 'Отсутствует настройки в "Организации и линии"',

                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'fullName' => $fullName,
                'uid' => $uid,
            ]);
        }

        //dd($saveOrgan);

        $model = templateModel::where('accountId', $accountId)->get();
        $Template = [];
        if (!$existingRecords->isEmpty()) {
            foreach ($model as $record) {
                $Template[] = $record->getAttributes();
            }
        }

        // dd($Template);

        return view('setting.template.template', [
            'saveOrgan' => $saveOrgan,
            'template' => $Template,
            'message' => $request->message ?? '',

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ]);
    }

    public function postCreate(Request $request, $accountId): View|Factory|RedirectResponse|Application
    {
        $isAdmin = $request->isAdmin;
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";

        return to_route('template', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
            'message' => 'Настройки сохранились',
        ]);
    }


    function getCreatePoles(Request $request, $accountId): JsonResponse
    {

        try {
            $name_uid = (string)Str::uuid();

            $data = json_decode(json_encode([
                'name' => $request->name ?? '',
                'name_uid' => $name_uid,
                'organId' => $request->organId ?? '',
                'message' => $request->message ?? '',

                'idCreatePole' => $request->idCreatePole ?? [],
                'idCreateAddPole' => $request->idCreateAddPole ?? [],
            ]));
            $idCreatePole = $request->idCreatePole ?? [];
            $idCreateAddPole = $request->idCreateAddPole ?? [];


            $model = new templateModel();
            $existingRecords = templateModel::where('accountId', $accountId)->where('name', $data->name)->get();

            if (!$existingRecords->isEmpty()) {
                foreach ($existingRecords as $record) {
                    $polesModel = polesModel::where('accountId', $accountId)->where('name_uid', ($record->toArray())['name_uid'])->get();
                    if (!$polesModel->isEmpty()) {
                        foreach ($polesModel as $item) {
                            $item->delete();
                        }
                    }
                    $record->delete();
                }
            }

            $model->accountId = $accountId;
            $model->organId = $data->organId;

            $model->name = $data->name;
            $model->name_uid = $name_uid;

            $model->message = $data->message;
            $model->save();


            if (count($idCreatePole) + count($idCreateAddPole) > 0)
                if ((count($idCreatePole) >= count($idCreateAddPole))) {
                    foreach ($data->idCreatePole as $id => $item) {
                        $model = new polesModel();
                        $model->accountId = $accountId;
                        $model->name = $data->name;
                        $model->name_uid = $name_uid;

                        $model->i = $id;
                        if (isset($data->idCreatePole->$id)) $model->pole = $data->idCreatePole->$id;
                        else $model->pole = null;
                        if (isset($data->idCreateAddPole->$id)) $model->add_pole = $data->idCreateAddPole->$id;
                        else $model->add_pole = null;
                        $model->entity = null;

                        $model->save();
                    }
                } else {
                    foreach ($data->idCreateAddPole as $id => $item) {
                        $model = new polesModel();
                        $model->accountId = $accountId;
                        $model->name = $data->name;
                        $model->name_uid = $name_uid;

                        $model->i = $id;
                        if (isset($data->idCreatePole->$id)) $model->pole = $data->idCreatePole->$id;
                        else $model->pole = null;
                        if (isset($data->idCreateAddPole->$id)) $model->add_pole = $data->idCreateAddPole->$id;
                        else $model->add_pole = null;
                        $model->entity = null;

                        $model->save();
                    }
                }
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);


    }

    function getNameUIDPoles(Request $request, $accountId): JsonResponse
    {
        try {
            $nameUID = $request->nameUID ?? "";

            $data = [
                'name' => "",
                'name_uid' => $nameUID,
                'organId' => "",
                'message' => "",

                'idCreatePole' => [],
                'idCreateAddPole' => [],
            ];


            $templateModel = templateModel::where('accountId', $accountId)->where('name_uid', $nameUID)->get();

            if (!$templateModel->isEmpty()) {
                foreach ($templateModel as $record) {
                    $item_record = json_decode(json_encode($record->toArray()));

                    $data['name'] = $item_record->name;
                    $data['organId'] = $item_record->organId;
                    $data['message'] = $item_record->message;

                    $polesModel = polesModel::where('accountId', $accountId)->where('name_uid', $item_record->name_uid)->get();
                    if (!$polesModel->isEmpty()) {
                        foreach ($polesModel as $item) {
                            $data['idCreatePole'][$item->i] = [
                                'pole' => $item->pole,
                            ];
                            $data['idCreateAddPole'][$item->i] = [
                                'add_pole' => $item->add_pole,
                            ];
                        }
                    }
                }
            }
        } catch (BadResponseException $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }


        return response()->json([
            'status' => true,
            'data' => $data
        ]);


    }


    function deletePoles(Request $request, $accountId): JsonResponse
    {

        $data = json_decode(json_encode([
            'name' => $request->name ?? '',
            'name_uid' => $request->name_uid ?? '',
        ]));

        try {
            $existingRecords = templateModel::where('accountId', $accountId)->where('name', $data->name)->get();

            if (!$existingRecords->isEmpty()) {
                foreach ($existingRecords as $record) {
                    $polesModel = polesModel::where('accountId', $accountId)->where('name_uid', ($record->toArray())['name_uid'])->get();
                    if (!$polesModel->isEmpty()) {
                        foreach ($polesModel as $item) {
                            $item->delete();
                        }
                    }
                    $record->delete();
                }
            }
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getAttributes(Request $request, $accountId): JsonResponse
    {

        $client = new MsClient($accountId);

        try {
            $customerorder = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes/');
        } catch (BadResponseException) {
            $customerorder = null;
        }

        try {
            $demand = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes/');
        } catch (BadResponseException) {
            $demand = null;
        }

        try {
            $salesreturn = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata/attributes/');
        } catch (BadResponseException) {
            $salesreturn = null;
        }

        try {
            $invoiceout = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/invoiceout/metadata/attributes/');
        } catch (BadResponseException) {
            $invoiceout = null;
        }

        try {
            $counterparty = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/counterparty/metadata/attributes/');
        } catch (BadResponseException) {
            $counterparty = null;
        }


        return response()->json([
            'customerorder' => $customerorder,
            'demand' => $demand,
            'salesreturn' => $salesreturn,
            'invoiceout' => $invoiceout,
            'counterparty' => $counterparty,
        ]);

    }
    /**
     * testSet1{
     * entityType : "demand",
     * entityId : "277ca4f4-d6d6-11ee-0a80-0cc500080c42",
     * templateId : "2"
     * }
     */
    function getTemplate(Request $request, $accountId){

        try{

            $templateS = new TemplateService($accountId);

            $entityType = $request->entityType ?? false;
            $entityId = $request->entityId ?? false;
            $templateId = $request->templateId ?? false;

            $template = $templateS->getTemplate($entityType, $entityId, $templateId);
            return $template->data;
        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }
}
