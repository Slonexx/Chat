<?php
namespace App\Services\ChatApp;

use App\Clients\MoySklad;
use App\Clients\newClient;
use App\Models\ChatappEmployee;
use App\Models\employeeModel;
use App\Models\MainSettings;
use App\Models\Templates;
use App\Services\MoySklad\Entities\CustomOrderService;
use App\Services\MoySklad\Entities\DemandService;
use App\Services\MoySklad\Entities\InvoiceoutService;
use App\Services\MoySklad\Entities\SalesReturnService;
use App\Services\MoySklad\TemplateService;
use App\Services\Response;
use GuzzleHttp\Exception\BadResponseException;

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
            "demand" => new DemandService($this->accountId),
            "customerorder" => new CustomOrderService($this->accountId),
            "invoiceout" => new InvoiceoutService($this->accountId),
            "salesreturn" => new SalesReturnService($this->accountId),
        ];
        $service = $entityServices[$type];
        $splUrl = explode("/", $href);
        $entityId = array_pop($splUrl);
        $expArray = ["state", "channel", "project"];
        if($employeeId == null)
            array_push($expArray, "owner");
        $docRes = $service->getByIdWithExpand($entityId, $expArray);

        if(!$docRes->status)
            return $docRes->addMessage("Не удаётся получить документ");
        $autos = MainSettings::join('template_auto_settings as auto_s', "main_settings.id", "=", "auto_s.main_settings_id")
            ->where('entity', $type)
            ->select(
                "status",
                "channel",
                "project",
                "template_id",
                "employee_id"
            )->get()
            ->all();
        $state_id = $docRes->data->state->id;
        $channel_id = $docRes->data->channel->id ?? false;
        $project_id = $docRes->data->project->id ?? false;
        if($employeeId == null){
            $employeeId = $docRes->data->project->id;
        }

        $filteredTemplatesAuto = [];
        if($channel_id !== false && $project_id !== false){
            $filteredTemplatesAuto = array_filter($autos, fn($val) => $val->status == $state_id
                && $val->channel == $channel_id
                && $val->project == $project_id
            );

        } else if($channel_id === false && $project_id === false){
            $filteredTemplatesAuto = array_filter($autos, fn($val) => $val->status == $state_id);
        } else if ($channel_id === false) {
            $filteredTemplatesAuto = array_filter($autos, fn($val) => $val->status == $state_id
                && $val->project == $project_id
            );
        } else if ($project_id === false) {
            $filteredTemplatesAuto = array_filter($autos, fn($val) => $val->status == $state_id
                && $val->channel == $channel_id
            );
        }

        if(count($filteredTemplatesAuto) == 0)
            return $this->res->success("");

        $templateIds = collect($filteredTemplatesAuto)->pluck("template_id")->all();

        $templatesForSending = Templates::whereIn("id", $templateIds)->get()->all();

        $templateS = new TemplateService($this->accountId);
        foreach($filteredTemplatesAuto as $t){
            $template_id = $t->template_id;
            $employee_id = $t->employee_id;
            $template = array_filter($templatesForSending, fn($val)=> $val->id == $template_id);

            $prepTemplRes = $templateS->getTemplate($type, $entityId, $template[0]["uuid"]);
            if(!$prepTemplRes->status){
                return $prepTemplRes;
            }
            $employeeModel = employeeModel::where("id", $employee_id)->get();
            if($employeeModel->isEmpty()){
                return $this->res->error("Сотрудник не привязан к данной автоматизации");
            }
            $autoChatappSettings = ChatappEmployee::where("employee_id", $employee_id)->get();
            if($autoChatappSettings->isEmpty()){
                return $this->res->error("Настройки chatappEmployee не найдены");
            }
            $chatappObj = $autoChatappSettings->first();
            $lineId = $chatappObj->lineId;
            $messenger = $chatappObj->messenger;
            $chatId = $chatappObj->chatId;
            $template = $prepTemplRes->data;

            $newClient = new newClient($employeeId);
            try {
                $res = ($newClient->sendMessage($lineId, $messenger, $chatId, $template))->getBody()->getContents();
            } catch (BadResponseException $e) {
                return response()->json([
                    'status' => false,
                    'data' => json_decode($e->getResponse()->getBody()->getContents())
                ]);
            }
            



        }




        return 1;
        
    }
}