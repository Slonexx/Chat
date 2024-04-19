<?php
namespace App\Services\ChatApp;

use App\Clients\MoySklad;
use App\Clients\newClient;
use App\Models\ChatappEmployee;
use App\Models\employeeModel;
use App\Models\MainSettings;
use App\Models\settingModel;
use App\Models\Templates;
use App\Services\MoySklad\Entities\CustomOrderService;
use App\Services\MoySklad\Entities\DemandService;
use App\Services\MoySklad\Entities\InvoiceoutService;
use App\Services\MoySklad\Entities\SalesReturnService;
use App\Services\MoySklad\TemplateService;
use App\Services\Response;
use GuzzleHttp\Exception\BadResponseException;
use stdClass;

class AutomatizationService{

    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function sendTemplate($type, $href, $employeeId){
        $entityServices = [
            "customerorder" => new CustomOrderService($this->accountId),
            "demand" => new DemandService($this->accountId),
            "salesreturn" => new SalesReturnService($this->accountId),
            "invoiceout" => new InvoiceoutService($this->accountId),
        ];
        $service = $entityServices[$type];
        $splUrl = explode("/", $href);
        $entityId = array_pop($splUrl);
        $expArray = ["state", "channel", "project", "agent"];
        if($employeeId == null)
            array_push($expArray, "owner");
        $docRes = $service->getByIdWithExpand($entityId, $expArray);

        if(!$docRes->status)
            return $docRes->addMessage("Не удаётся получить документ");
        $compliaceEntityType  = [];
        $i = 0;
        foreach($entityServices as $key => $value){
            $compliaceEntityType[$key] = $i++;
        }
        $state_id = $docRes->data->state->id;
        $channel_id = $docRes->data->channel->id ?? false;
        $project_id = $docRes->data->project->id ?? false;
        $agentAttributes = $docRes->data->agent->attributes ?? false;
        $desc = $docRes->data->description ?? false;

        if($employeeId == null){
            $employeeId = $docRes->data->owner->id;
        }
        $autos = settingModel::join('scenario as scen', "setting_models.accountId", "=", "scen.accountId")
            ->leftJoin('templates as t', 'scen.template_id', "=", "t.id")
            ->join('automation_scenarios as a_scen', "scen.id", "=", "a_scen.scenario_id")
            ->join('automations as a', "a_scen.automation_id", "=", "a.id")
            ->join('employee_models as e', "a.employee_id", "=", "e.id")
            ->where("scen.status", $state_id)
            ->where('scen.entity', $compliaceEntityType[$type])
            ->where("e.employeeId", $employeeId)
            ->select(
                "status",
                "channel",
                "project",
                "e.employeeId",
                "t.uuid as template_uuid",
                "a.line",
                "a.messenger"
            )->get()
            ->all();
        

        $filteredTemplatesAuto = [];
        if($channel_id !== false && $project_id !== false){
            $filteredTemplatesAuto = array_filter($autos, fn($val) => $val->channel == $channel_id
                && $val->project == $project_id
            );

        } else if ($channel_id === false || $project_id === false) {
            if ($channel_id === false) {
                $filteredTemplatesAuto = array_filter($autos, fn($val) => $val->project == $project_id);
            } else if ($project_id === false) {
                $filteredTemplatesAuto = array_filter($autos, fn($val) => $val->channel == $channel_id);
            }
        }

        if(count($filteredTemplatesAuto) == 0)
            return $this->res->success("");

        $templateS = new TemplateService($this->accountId);
        $messengerAttributes = MainSettings::join("messenger_attributes as mes", "main_settings.id", "=", "mes.main_settings_id")
            ->where("main_settings.account_id", $this->accountId)
            ->where("entity_type", "counterparty")
            ->get()
            ->pluck("attribute_id", "name")
            ->all();
        foreach($filteredTemplatesAuto as $t){
            $template_uuid = $t->template_uuid;
            if($template_uuid == null)
            continue;

            $lineId = $t->line;
            $messenger = $t->messenger;

            $body = new stdClass();
            if(!$agentAttributes){
                $messengerErr = "У данного документа у контрагента отсутствуют поля месседжеров";
                if($desc == false)
                    $body->description = $messengerErr;
                else
                    $body->description += PHP_EOL . $messengerErr;
                return $this->msC->put($type, $body, $entityId);
                
            } else {
                $messengerId = $messengerAttributes[$messenger];
                $findedAttribute = array_filter($agentAttributes, fn($val) => $val->id == $messengerId);
                if(count($findedAttribute) == 0){
                    $messengerErr = "У данного документа у контрагента не заполнен месседжер {$messenger}";
                    if($desc == false)
                        $body->description = $messengerErr;
                    else
                        $body->description += PHP_EOL . $messengerErr;
                    return $this->msC->put($type, $body, $entityId);
                } else {
                    $firstAttr = array_shift($findedAttribute);
                    $chatId = $firstAttr->value;
                }
                
            }
            
        
            $prepTemplRes = $templateS->getTemplate($type, $entityId, $template_uuid);
            if(!$prepTemplRes->status){
                return $prepTemplRes;
            }
            $template = $prepTemplRes->data;

            $newClient = new newClient($employeeId);
            try {
                $response = $newClient->sendMessage($lineId, $messenger, $chatId, $template);
            } catch (BadResponseException $e) {
                return $newClient->ResponseExceptionHandler($e);
            }
            
        }
        return response()->json();
        
    }
}