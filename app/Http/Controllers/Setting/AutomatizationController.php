<?php

namespace App\Http\Controllers\Setting;
use App\Clients\oldMoySklad;
use App\Clients\MsClientAdd;
use App\Http\Controllers\Controller;
use App\Models\AutomationSettings;
use App\Models\CompanySettings;
use App\Models\employeeModel;
use App\Models\MainSettings;
use App\Models\organizationModel;
use App\Models\Scenario;
use App\Models\Templates;
use App\Services\HandlerService;
use App\Services\ChatApp\AutomatizationService;
use App\Services\MoySklad\Entities\oldCustomOrderService;
use App\Services\MoySklad\Entities\DemandService;
use App\Services\MoySklad\Entities\EmployeeService;
use App\Services\MoySklad\Entities\InvoiceoutService;
use App\Services\MoySklad\Entities\ProjectService;
use App\Services\MoySklad\Entities\SalesChannelService;
use App\Services\MoySklad\Entities\SalesReturnService;
use App\Services\MoySklad\FrontendLogicService;
use Error;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use stdClass;

class AutomatizationController extends Controller{
    function getPage(Request $request, $accountId){
        try{
            $handlerS = new HandlerService();
            $isAdmin = $request->isAdmin;
            $fullName = $request->fullName ?? "Имя аккаунта";
            $uid = $request->uid ?? "логин аккаунта";

            $savedAuto = MainSettings::join('template_auto_settings as auto_s', 'auto_s.main_settings_id', "=", "main_settings.id")
            ->join('templates as t', 't.id', "=", "auto_s.template_id")
            ->where("account_id", $accountId)
            ->select(
                "auto_s.uuid",
                "channel",
                "project",
                "status",
                "entity",
                "t.uuid as template"
                )
            ->get()
            ->toArray();

            $automation = [];
            $statuses = [];
            $entityServices = [
                "demand" => new DemandService($accountId),
                "customerorder" => new oldCustomOrderService($accountId),
                "invoiceout" => new InvoiceoutService($accountId),
                "salesreturn" => new SalesReturnService($accountId),
            ];

            foreach($entityServices as $entityType => $service){
                $statusesRes = $service->getStatuses();
                if(!$statusesRes->status){
                    return $handlerS->responseHandler($statusesRes, true, false);
                }
                $statuses[$entityType] = $statusesRes->data;
            }

            ////////////STATUSES////////////
            //$prepStatuses = [];
            foreach($statuses as $entityType => $states){
                $autos = array_filter($savedAuto, fn($value) => $value["entity"] == $entityType);
                foreach($states as $state){
                    if(count($autos) == 0)
                        break;
                    $fA = array_filter($autos, fn($value) => $value['status'] == $state->id);
                    if(count($fA) == 0)
                        continue;
                    $shftedAuto = array_shift($fA);

                    $fSEqueal = array_filter($states, fn($value) => $value->id == $shftedAuto['status']);
                    $fSNotEqueal = array_filter($states, fn($value) => $value->id != $shftedAuto['status']);

                    $stateEqual = array_shift($fSEqueal);

                    $obj = new stdClass();
                    $obj->id = $stateEqual->id;
                    $obj->name = $stateEqual->name;
                    $obj->selected = true;

                    $shftedAuto['status'] = [];
                    $shftedAuto['status'][] = $obj;

                    foreach($fSNotEqueal as $state){
                        $obj = new stdClass();
                        $obj->id = $state->id;
                        $obj->name = $state->name;
                        $obj->selected = false;
                        $shftedAuto['status'][] = $obj;
                    }
                    $automation[] = $shftedAuto;
                }

            }
            ////////////STATUSES////////////

            $availableEntities = array_keys($entityServices);


            $projectS = new ProjectService($accountId);
            $allProjRes = $projectS->getAll();
            if(!$allProjRes->status){
                return $handlerS->responseHandler($allProjRes, true, false);
            }

            $salesChannelS = new SalesChannelService($accountId);
            $allSalesChanRes = $salesChannelS->getAll();
            if(!$allSalesChanRes->status){
                return $handlerS->responseHandler($allSalesChanRes, true, false);
            }

            $saleschannel = $allSalesChanRes->data;
            $project = $allProjRes->data;

            $templates = MainSettings::join('templates as t', 'main_settings.id', "=", "t.main_settings_id")
            ->where("account_id", $accountId)
            ->select(
                "t.title",
                "t.uuid")
            ->get()
            ->pluck("title", "uuid")
            ->all();

            for($i = 0; $i< count($automation); $i++){
                ////////////ENTITIES////////////
                $currentEntity = $automation[$i]['entity'];
                $obj = new stdClass();
                $obj->name = $currentEntity;
                $obj->selected = true;
                $automation[$i]['entity'] = [];
                $automation[$i]['entity'][] = $obj;

                $fENotEqueal = array_filter($availableEntities, fn($value) => $value != $currentEntity);
                foreach($fENotEqueal as $item){
                    $obj = new stdClass();
                    $obj->name = $item;
                    $obj->selected = false;
                    $automation[$i]['entity'][] = $obj;
                }
                ////////////ENTITIES////////////

                $frontendLogicS = new FrontendLogicService();

                ////////////CHANNEL////////////
                $preparedChannel = $frontendLogicS->prepareSelect($automation[$i], 'channel', $saleschannel);
                $automation[$i]['channel'] = [];
                $automation[$i]['channel'] = $preparedChannel;
                ////////////CHANNEL////////////

                ////////////PROJECT////////////
                $preparedProject = $frontendLogicS->prepareSelect($automation[$i], 'project', $project);
                $automation[$i]['project'] = [];
                $automation[$i]['project'] = $preparedProject;
                ////////////PROJECT////////////

                ////////////TEMPLATE////////////
                $preparedTemplate = $frontendLogicS->prepareSelect($automation[$i], 'template', $templates);
                $automation[$i]['template'] = [];
                $automation[$i]['template'] = $preparedTemplate;
                ////////////TEMPLATE////////////

            }

            return view('setting.Scenario.main', [
                'savedAuto' => $automation,
                // 'template' => $templates,
                // 'message' => $request->message ?? '',

                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'fullName' => $fullName,
                'uid' => $uid,
            ]);


        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    function sendTemplate(Request $request){
        try{
            $req = $request->all();
            $handlerS = new HandlerService();

            $msUid = $req["auditContext"]["uid"];
            $spldUid = explode("@", $msUid);
            $startUid = $spldUid[0];

            foreach($req['events'] as $event){
                $type = $event['meta']['type'] ?? null;
                $href = $event['meta']['href'] ?? null;
                $accountId = $event['accountId'] ?? null;
                $employeeS = new EmployeeService($accountId);
                $employeeId = null;
                if($startUid != ""){
                    $employeeIdRes = $employeeS->getByUid($startUid);
                    if(!$employeeIdRes->status){
                        return $handlerS->responseHandler($employeeIdRes, true, false);
                    }

                }

                $stateHasChanged = in_array('state', $event['updatedFields']);
                if($stateHasChanged){
                    $autoS = new AutomatizationService($accountId);
                    $res = $autoS->sendTemplate($type, $href, $employeeIdRes->data);
                    return $handlerS->responseHandler($res, true, false);
                } else {
                    return response()->json("Статус не был изменён");
                }
            }
        } catch(Exception | Error $e){
            return response()->json($e->getMessage(), 500);
        }
    }



    function getScenario(Request $request, $accountId){
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";
        $main = employeeModel::getAllEmpl($accountId);


        if ($main->toArray == null) {
            return to_route('creatEmployee', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'message' => 'Сначала пройдите настройки подключение'
            ]);
        }
        $client = new oldMoySklad($accountId);

        $endpoints = [
            'customerorder' => '/customerorder/metadata',
            'demand' => '/demand/metadata',
            'salesreturn' => '/salesreturn/metadata',
            'invoiceout' => '/invoiceout/metadata',
            'project' => '/project',
            'saleschannel' => '/saleschannel',
        ];

        $response = [];

        foreach ($endpoints as $key => $endpoint) {
            $result = $client->getByUrl("https://api.moysklad.ru/api/remap/1.2/entity$endpoint");
            //$defaultOption = [['id' => '0', 'name' => 'Не выбирать']];

            if (in_array($key, ['customerorder', 'demand', 'salesreturn', 'invoiceout']))  {
                if ($result->status) {
                    $result = $result->data;
                    $data = property_exists($result, 'states') ? $result->states : [];
                }
                else  return view('setting.error', [ 'accountId' => $accountId, 'isAdmin' => $isAdmin, 'message' => $result->data->message ]);
            }
            else {
                if ($result->status) {
                    $result = $result->data;
                    $data = $result->meta->size > 0 ? $result->rows : [];
                }
                else return view('setting.error', [ 'accountId' => $accountId, 'isAdmin' => $isAdmin, 'message' => $result->data->message ]);
            }

            //array_unshift($data, ...$defaultOption);
            $response[$key] = $data;
        }

        $template = Templates::getAllMainsTemplates($accountId);
        if ($template->toArray == null) return to_route('template', [
            'accountId' => $accountId,
            'isAdmin' => $request->isAdmin,
            'fullName' => $request->fullName ?? "Имя аккаунта",
            'uid' => $request->uid ?? "логин аккаунта",

            'message' => 'Сначала создайте шаблоны'
        ]);


        $Saved = Scenario::getInformationALLAcc($accountId);
        //dd($Saved);

        return view('setting.Scenario.main', [
            'arr_meta' => [
                'customerorder' => (array)$response['customerorder'],
                'demand' => (array)$response['demand'],
                'salesreturn' => (array)$response['salesreturn'],
                'invoiceout' => (array)$response['invoiceout'],
            ],
            'arr_project' => (array)$response['project'],
            'arr_saleschannel' => (array)$response['saleschannel'],
            'template' => $template->toArray,


            'SavedCreateToArray' => $Saved->toArray,



            'accountId' => $accountId,
            'message' => $request->message ?? '',
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ]);

    }

    function saveScenario(Request $request, $accountId){
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";
        $newArray = [];

        foreach ($request->all() as $key => $value) {
            // Если ключ содержит один из префиксов, то извлекаем идентификатор
            if (preg_match('/^(template_|entity_|status_|saleschannel_|project_)(.*)$/', $key, $matches)) {
                // Получаем префикс и идентификатор
                $prefix = $matches[1];
                $identifier = $matches[2];
                // Создаем новую запись в новом массиве
                if (!isset($newArray[$identifier])) {
                    $newArray[$identifier] = [];
                }
                // Добавляем значение в новый массив
                $newArray[$identifier][str_replace('_', '', $prefix)] = $value;
            }
        }

        $is_set = Scenario::createOrUpdateIsArray($accountId, $newArray);
        if ($is_set['status']){
            return to_route('automation', [
                'accountId' => $accountId,
                'isAdmin' => $request->isAdmin,
                'fullName' => $request->fullName ?? "Имя аккаунта",
                'uid' => $request->uid ?? "логин аккаунта",
            ]);
        } else {
            return to_route('scenario', [
                'accountId' => $accountId,
                'isAdmin' => $request->isAdmin,
                'fullName' => $request->fullName ?? "Имя аккаунта",
                'uid' => $request->uid ?? "логин аккаунта",

                'message' => $is_set['message']
            ]);
        }
    }

    function deleteScenario(Request $request, $accountId){

        $id = $request->id ?? null;
        if ($id == null) return response()->json(['status'=> false, 'message' => 'Ошибка Удаления, неизвестный идентификатор']);
        try {
            $model = Scenario::find($id);
            $model->delete();
        } catch (BadResponseException $e){
            return response()->json(['status'=> false, 'message' => $e->getMessage()]);
        }
        return response()->json(['status'=> true]);
    }
}
