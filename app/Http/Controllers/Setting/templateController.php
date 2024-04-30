<?php

namespace App\Http\Controllers\Setting;

use App\Clients\MsClient;
use App\Http\Controllers\Controller;
use App\Models\Automation_scenario;
use App\Models\MainSettings;
use App\Models\MsEntityFields;
use App\Models\organizationModel;
use App\Models\polesModel;
use App\Models\Scenario;
use App\Models\templateModel;
use App\Models\Templates;
use App\Models\Variables;
use App\Services\HandlerService;
use App\Services\MoySklad\TemplateLogicService;
use App\Services\MoySklad\TemplateService;
use App\Services\Response;
use Error;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class templateController extends Controller
{
    public function getCreated(Request $request, $accountId): Factory|View|Application
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

        $templates = MainSettings::join("templates", "main_settings.id", "=", "templates.main_settings_id")
        ->where("main_settings.account_id", $accountId)
        ->select("templates.uuid", "templates.title")
        ->get()
        ->toArray();

        // $model = templateModel::where('accountId', $accountId)->get();
        // $Template = [];
        // if (!$existingRecords->isEmpty()) {
        //     foreach ($model as $record) {
        //         $Template[] = $record->getAttributes();
        //     }
        // }

        // dd($Template);

        return view('setting.template.template', [
            'saveOrgan' => $saveOrgan,
            'template' => $templates,
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
            $handlerS = new HandlerService();
            $res = new Response();
            $name_uid = (string)Str::uuid();

            
            $dataArray = [
                'title' => $request->name ?? '',
                'content' => $request->message ?? '',
                //'name_uid' => $name_uid,
                //'organId' => $request->organId ?? '',
                //'idCreatePole' => $request->idCreatePole ?? [],
                //'idCreateAddPole' => $request->idCreateAddPole ?? [],
            ];
            $data = json_decode(json_encode($dataArray));
            $idCreatePole = $request->idCreatePole ?? [];
            $idCreateAddPole = $request->idCreateAddPole ?? [];
            
            $model = new templateModel();
            
            $existingRecords = MainSettings::join("templates", "main_settings.id", "=", "templates.main_settings_id")
                ->where("main_settings.account_id", $accountId)
                ->where("templates.title", $data->title)
                ->get("templates.*");
            
            if (!$existingRecords->isEmpty()) {
                throw new Exception("Шаблон с данным именем уже существует");
            }

            // $mainSet = MainSettings::where("account_id", $accountId)->get()->first();
            // $req = Templates::where("main_settings_id", $mainSet->id)
            //     ->where("title", $data->title);

            // $reqResult = $req->get();
            
            // if (!$reqResult->isEmpty()) {
            //     $templateIds = $reqResult->pluck("id")
            //     ->all();
            //     Variables::whereIn("template_id", $templateIds)->delete();
            //     $reqResult->each(function($template){
            //         $template->delete();
            //     });
            // }

            $setting = MainSettings::where("account_id", $accountId)->get();

            if($setting->isEmpty()){
                $er = $res->error($setting, 'Настройки по данному accountId не найдены');
                return response()->json($er);
            }
            $templateS = new TemplateService($accountId);

            $uniqueFields = $templateS->checkTemplate($data->content);
            if(!$uniqueFields->status){
                return $handlerS->responseHandler($uniqueFields);
            }

            $findedSetting = $setting->first();
            //из коллекции достали и сказали создай в шаблонах
            $createdTemplate = $findedSetting->templates()->create($dataArray);

            //add relation with attributes
            $templateLogicS = new TemplateLogicService($accountId);

            $attributes = $templateLogicS->findAttributesFromTemplate($data->content);
            
            $attributesId = $findedSetting->attributes()
                ->whereIn("name", $attributes)
                ->pluck('id')
                ->toArray();

            $templateId = $createdTemplate->id;

            $prepIdArray = [];
            foreach($attributesId as $value){
                $item = [
                    'template_id' => $templateId, 
                    'attribute_settings_id' => $value
                ];
                //added create Many
                Variables::create($item);
            }

            $success = $res->success($data, 'Успешно сохранено');
            return response()->json($success);

        } catch (Exception | Error $e) {
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
            $handlerS = new HandlerService();
            $res = new Response();
            $nameUID = $request->nameUID ?? "";
            //чисто теоретически клиент Б может поменять текст шаблона клиента А зная его UUID. 
            //Но так как клиенту выдаются только его шаблоны это не представляется возможным
            $template = Templates::where("uuid", $nameUID)
                ->select("title", "content", "uuid")
                ->get();

            if($template->isEmpty()){
                $er = $res->error($template->first(), "Не найден шаблон по данному uuid");
                return $handlerS->responseHandler($er);
            } else {
                $templateContent = $template->first();
                $templateRes = $res->success($templateContent);
                return response()->json($templateRes);
            }


            // $data = [
            //     'name' => "",
            //     'name_uid' => $nameUID,
            //     'organId' => "",
            //     'message' => "",

            //     'idCreatePole' => [],
            //     'idCreateAddPole' => [],
            // ];


            // $templateModel = templateModel::where('accountId', $accountId)->where('name_uid', $nameUID)->get();

            // if (!$templateModel->isEmpty()) {
            //     foreach ($templateModel as $record) {
            //         $item_record = json_decode(json_encode($record->toArray()));

            //         $data['name'] = $item_record->name;
            //         $data['organId'] = $item_record->organId;
            //         $data['message'] = $item_record->message;

            //         $polesModel = polesModel::where('accountId', $accountId)->where('name_uid', $item_record->name_uid)->get();
            //         if (!$polesModel->isEmpty()) {
            //             foreach ($polesModel as $item) {
            //                 $data['idCreatePole'][$item->i] = [
            //                     'pole' => $item->pole,
            //                 ];
            //                 $data['idCreateAddPole'][$item->i] = [
            //                     'add_pole' => $item->add_pole,
            //                 ];
            //             }
            //         }
            //     }
            // }
        } catch (Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }


        // return response()->json([
        //     'status' => true,
        //     'data' => $data
        // ]);


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
            $customerorder = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes/');
        } catch (BadResponseException) {
            $customerorder = null;
        }

        try {
            $demand = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes/');
        } catch (BadResponseException) {
            $demand = null;
        }

        try {
            $salesreturn = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata/attributes/');
        } catch (BadResponseException) {
            $salesreturn = null;
        }

        try {
            $invoiceout = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/invoiceout/metadata/attributes/');
        } catch (BadResponseException) {
            $invoiceout = null;
        }

        try {
            $counterparty = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/counterparty/metadata/attributes/');
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
            $handlerS = new HandlerService();
            $templateS = new TemplateService($accountId);

            $entityType = $request->entityType ?? false;
            $entityId = $request->entityId ?? false;
            $templateId = $request->templateId ?? false;

            $template = $templateS->getTemplate($entityType, $entityId, $templateId);
            if(!$template->status){
                return $handlerS->responseHandler($template, true, false);
            } else {
                return $handlerS->responseHandler($template);
            }
        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    function getTemplates(Request $request){

        try{

            $entityType = $request->entity_type ?? false;
            $entityId = $request->object_Id ?? false;
            $accountId = $request->accountId ?? false;
            $templateS = new TemplateService($accountId);


            $template = $templateS->getTemplates($entityType, $entityId);
            return response()->json($template);
        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    function putTemplateByUuid(Request $request, $accountId){
        try{
            $handlerS = new HandlerService();
            $res = new Response();
            $UUID = $request->uuid ?? "";
            // при желании можно сделать обновление названия
            //$title = $request->name ?? "";
            $content = $request->message ?? "";
            //чисто теоретически клиент Б может поменять текст шаблона клиента А зная его UUID. 
            //Но так как клиенту выдаются только его шаблоны это не представляется возможным
            $collectionTemplate = Templates::where("uuid", $UUID)->get();
            if($collectionTemplate->isEmpty()) {
                $er = $res->error($collectionTemplate->first(), "Не найден шаблон по данному uuid");
                return response()->json($er);
            } 

            $templateS = new TemplateService($accountId);

            $uniqueFields = $templateS->checkTemplate($content);
            if(!$uniqueFields->status){
                return $handlerS->responseHandler($uniqueFields);
            }

            
            $template = $collectionTemplate->first();
            $templateLogicS = new TemplateLogicService($accountId);

            $attributes = $templateLogicS->findAttributesFromTemplate($content);

            $setting = MainSettings::where("account_id", $accountId)->get();

            if($setting->isEmpty()){
                $er = $res->error($setting, 'Настройки по данному accountId не найдены');
                return response()->json($er);
            }
            //получаем актуальные имена
            $attributesNames = $setting->first()->attributes()
                ->whereIn("name", $attributes)
                ->pluck('name', "id")
                ->toArray();

            $attributesOfCurrentTemplate = $template->attributes()->pluck('name')->toArray();

            $newAttributes = array_filter($attributesNames, fn($name) => !in_array($name, $attributesOfCurrentTemplate));

            foreach ($newAttributes as $key => $item) {
                Variables::updateOrCreate(
                    [
                        'template_id' => $template->id, 
                        'attribute_settings_id' => $key
                    ],
                    [
                        'template_id' => $template->id, 
                        'attribute_settings_id' => $key
                    ]
                );
            }
            
            // foreach($newAttributes as $attribute){
            //     $attribute->id
            // }
            // $templateId = $createdTemplate->id;

            // $prepIdArray = [];
            // foreach($attributesId as $value){
            //     $item = [
            //         'template_id' => $templateId, 
            //         'attribute_settings_id' => $value
            //     ];
            //     //added create Many
            //     Variables::create($item);
            // }
            //$template->title = $title;
            $template->content = $content;
            $template->save();

            $templateRes = $res->success("+");
            return response()->json($templateRes);
        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    function getMainFields(Request $request){
        try{
            $res = new Response();
            $templates = MsEntityFields::pluck('keyword', 'name_RU')->unique();
            if($templates->isEmpty()){
                $er = $res->error($templates->toArray(), "Не найдены main fields");
                return response()->json($er);
            } else {
                $success = $res->success($templates->toArray());
                return response()->json($success);
            }
        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }

    }

    function deleteTemplate($accountId, $uuid){
        try{
            $res = new Response();
            $setting = MainSettings::where("account_id", $accountId)->get();

            if($setting->isEmpty()){
                $er = $res->error($setting, 'Настройки по данному accountId не найдены');
                return response()->json($er);
            }
            $templateReq = $setting->first()
                ->templates()
                ->where("uuid", $uuid);

            $templateId = $templateReq->get(["templates.id"])
                ->first()
                ->id;

            Scenario::where("template_id", $templateId)
                ->delete();

            Automation_scenario::where("template_id", $templateId)
            ->delete();

            $templateReq->delete();

            $success = $res->success("", 'Успешно удалено');
            return response()->json($success);
        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }


}
