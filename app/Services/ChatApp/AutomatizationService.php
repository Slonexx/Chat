<?php
namespace App\Services\ChatApp;

use App\Clients\oldMoySklad;
use App\Clients\newClient;
use App\Models\MainSettings;
use App\Models\settingModel;
use App\Services\MoySklad\Entities\oldCounterpartyService;
use App\Services\MoySklad\Entities\oldCustomOrderService;
use App\Services\MoySklad\Entities\DemandService;
use App\Services\MoySklad\Entities\InvoiceoutService;
use App\Services\MoySklad\Entities\SalesReturnService;
use App\Services\MoySklad\TemplateService;
use App\Services\Response;
use GuzzleHttp\Exception\BadResponseException;
use stdClass;

class AutomatizationService{

    private oldMoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId) {
        $this->msC = new oldMoySklad($accountId);
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function sendTemplate($type, $href, $employeeId){
        $entityServices = [
            "customerorder" => new oldCustomOrderService($this->accountId),
            "demand" => new DemandService($this->accountId),
            "salesreturn" => new SalesReturnService($this->accountId),
            "invoiceout" => new InvoiceoutService($this->accountId),
            "counterparty" => new oldCounterpartyService($this->accountId),
        ];
        $service = $entityServices[$type];
        $entityId = basename($href);


        $expArray = null;
        $isCounterparty = $type == "counterparty";
        if(!$isCounterparty){
            $expArray = ["state", "channel", "project", "agent"];
        } else {
            $expArray = ["state"];
        }
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
        if(!$isCounterparty){
            $agentAttributes = $docRes->data->agent->attributes ?? false;
            $agentEmail = $docRes->data->agent->email ?? false;
            $agentPhone = $docRes->data->agent->phone ?? false;
        } else {
            $agentAttributes = $docRes->data->attributes ?? false;
            $agentEmail = $docRes->data->email ?? false;
            $agentPhone = $docRes->data->phone ?? false;
        }
        $desc = $docRes->data->description ?? false;

        if($employeeId == null){
            $employeeByDefault = MainSettings::join('chat_automations as a', "main_settings.accountId", "=", "a.accountId")
                ->where("a.accountId", $this->accountId)
                ->where("a.is_default", true)
                ->select(
                    "a.employee_id"
                )->get();

            if($employeeByDefault->isNotEmpty()){
                $employeeFirst = $employeeByDefault->first();
                $employeeId = $employeeFirst->employee_id;
            } else {
                $employeeId = $docRes->data->owner->id ?? null;
            }
        }

        if($employeeId == null)
            return $this->res->error("Ошибка при получении employeeId");

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

        } else if ($channel_id === false && $project_id !== false) {
            $filteredTemplatesAuto = array_filter($autos, fn($val) => $val->project == $project_id);
        } else if ($project_id === false && $channel_id !== false) {
            $filteredTemplatesAuto = array_filter($autos, fn($val) => $val->channel == $channel_id);
        } else {
            $filteredTemplatesAuto = $autos;
        }

        if(count($filteredTemplatesAuto) == 0)
            return $this->res->error("Не найдены автоматизации, соответствующие $type c Id=$entityId");

        $templateS = new TemplateService($this->accountId);
        $messengerAttributes = MainSettings::join("messenger_attributes as mes", "main_settings.id", "=", "mes.main_settings_id")
            ->where("main_settings.account_id", $this->accountId)
            ->where("entity_type", "counterparty")
            ->get()
            ->pluck("attribute_id", "name")
            ->all();

        $responsesByAuto = [];
        foreach($filteredTemplatesAuto as $t){
            $template_uuid = $t->template_uuid;
            if($template_uuid == null)
                continue;

            $lineId = $t->line;
            $messenger = $t->messenger;

            $body = new stdClass();
            if(!$agentAttributes and $agentPhone === false and $agentEmail === false){
                $messengerErr = "У данного $type у контрагента отсутствуют доп поля месседжеров";
                if($desc == false)
                    $body->description = $messengerErr;
                else
                    $body->description += PHP_EOL . $messengerErr;
                $this->msC->put($type, $body, $entityId);
                continue;

            } else {
                //chatapp/db
                $compliances = [
                    "grWhatsApp" => "whatsapp",
                    "telegram" => "telegram",
                    "email" => "email",
                    "vkontakte" => "vk",
                    "instagram" => "instagram",
                    "telegramBot" => "telegram_bot",
                    "avito" => "avito"
                ];
                $dbMessenger = $compliances[$messenger];
                $messengerId = $messengerAttributes[$dbMessenger];
                if ($agentAttributes === false) $findedAttribute = [];
                else $findedAttribute = array_filter($agentAttributes, fn($val) => $val->id == $messengerId);

                if(count($findedAttribute) == 0){
                    $chatId = 0;
                    if($messenger == "whatsapp" || $messenger == "telegram" || $messenger == "grWhatsApp"){
                        if($agentPhone)
                            $chatId = $agentPhone;
                    }
                    else if($messenger == "email"){
                        if($agentEmail)
                            $chatId = $agentEmail;
                    }
                    else {
                        $messengerErr = "У данного $type у контрагента не заполнен email и phone";
                        $body->description = $messengerErr;
                        $this->msC->put($type, $body, $entityId);
                    }

                    if ($chatId == 0) {
                        $messengerErr = "У данного $type у контрагента не заполнен месседжер {$messenger}";
                        if($desc === false)
                            $body->description = $messengerErr;
                        else {
                            $body->description = $desc;
                            $body->description .= PHP_EOL . $messengerErr;
                        }
                        $this->msC->put($type, $body, $entityId);
                        continue;
                    }
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
                $res = $newClient->sendMessage($lineId, $messenger, $chatId, $template);
                $responsesByAuto[] = $newClient->ResponseHandler($res);
            } catch (BadResponseException $e) {
                return $newClient->ResponseExceptionHandler($e);
            }

        }
        return $this->res->success($responsesByAuto, "Все шаблоны отправлены");

    }
}
