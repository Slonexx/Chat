<?php
namespace App\Services\MoySklad;

use App\Clients\oldMoySklad;
use App\Models\MainSettings;
use App\Models\MsEntities;
use App\Models\Templates;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\MoySklad\Entities\oldCustomOrderService;
use App\Services\MoySklad\Entities\DemandService;
use App\Services\MoySklad\Entities\InvoiceoutService;
use App\Services\MoySklad\Entities\SalesReturnService;
use App\Services\Response;

class TemplateService {
    private oldMoySklad $msC;

    private string $accountId;

    private Response $res;

    //private HandlerService $handlerS;

    function __construct($accountId) {
        $this->msC = new oldMoySklad($accountId);
        //$this->handlerS = new HandlerService();
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    function getTemplate($entityType, $entityId, $templateId){
        //1)prepare
        $res = new Response();
        if(empty($entityType) || empty($entityId) || empty($templateId))
            return $this->res->error(
                [
                    $entityType,
                    $entityId,
                    $templateId
                ],
            "Один или несколько параметров пусты");

        $entityServices = [
            "demand" => new DemandService($this->accountId),
            "counterparty" => new CounterpartyService($this->accountId),
            "customerorder" => new oldCustomOrderService($this->accountId),
            "invoiceout" => new InvoiceoutService($this->accountId),
            "salesreturn" => new SalesReturnService($this->accountId),
        ];

        $service = $entityServices[$entityType];
        if($service == null){
            return $res->error($entityType, "entityType не найден");
        }

        // $template = Templates::where('id', $templateId)->first();

        // if($template == null)
        //     return $res->error($templateId, "Шаблон не найден");

        // $templateWithAttributes = $template->attributes()->get()->toArray();

        //2)get ms_entity_fields and info from Ms
        $fieldsArray = MsEntities::join('ms_entity_fields', 'ms_entities.id', '=', 'ms_entity_fields.ms_entities_id')
            ->where('ms_entities.keyword', "=",  $entityType)
            ->select('ms_entity_fields.keyword', 'ms_entity_fields.expand_filter')
            ->get()
            ->toArray();

        $fields = json_decode(json_encode($fieldsArray));

        $templateLogicS = new TemplateLogicService($this->accountId);

        $expandParams = $templateLogicS->prepareForExpandReq($fields);

        $expandRes = $service->getByIdWithExpand($entityId, $expandParams);
        if(!$expandRes->status)
            return $expandRes;
        $expandedInfo = $expandRes->data;
        $objectWithNeededValues = $service->cutMsObjectFromReqExpand($expandedInfo, $expandParams);
        //3)getTemplate and replace
        $template = Templates::where('uuid', $templateId)
            ->get();

        if($template->isEmpty())
            $res->error("", "Шаблон по данному id не найден");
        $content = $template->first()->content;

        $templateValues = $objectWithNeededValues->data;


        if($entityType != "counterparty"){
            $objectWithTextPositions = $templateLogicS->preparePositions($templateValues);
            $readyTemplate = $templateLogicS->insertIn($content, $objectWithTextPositions);
        } else {
            $readyTemplate = $templateLogicS->insertIn($content, $templateValues);
        }


        //тут я говорю найди мне все связанные атрибуты с данным шаблоном. если пусто, то шаблон simple
        $templatesWithAtttributes = Templates::join('variables', 'templates.id', '=', 'variables.template_id')
            ->join('attribute_settings', 'variables.attribute_settings_id', '=', 'attribute_settings.id')
            ->where('templates.uuid', "=",  $templateId)
            ->get()
            ->toArray();

        //4)add. fields
        if($templatesWithAtttributes){
            $templateAttributeValues = [];
            foreach($templatesWithAtttributes as $item){
                $user_var = $item["name"];
                $templateAttributeValues["!{{$user_var}}"] = $item["attribute_id"];
            }

            $resAttributes = $expandedInfo->attributes;

            foreach($templateAttributeValues as $key => $_){
                $findedAttribute = array_filter(
                    $resAttributes,
                    function ($value) use ($templateAttributeValues) {
                        return in_array($value->id, $templateAttributeValues);
                    }
                );
                if(count($findedAttribute) > 0){
                    $firstAttributeById = array_shift($findedAttribute);
                    if($firstAttributeById->type == "customentity")
                        $templateAttributeValues[$key] = $firstAttributeById->value->name;
                    else
                        $templateAttributeValues[$key] = $firstAttributeById->value;
                    $templateAttributeValues[$key] = $firstAttributeById->value;
                    //add only add. fields
                    $readyTemplate = $templateLogicS->insertIn($readyTemplate, $templateAttributeValues);
                }

            }

        }

        return $res->success($readyTemplate);
    }

    function getTemplates($entityType, $entityId){
        //1)prepare
        $res = new Response();
        if(empty($entityType) || empty($entityId))
            return $this->res->error(
                [
                    $entityType,
                    $entityId
                ],
            "Один или несколько параметров пусты");

        $entityServices = [
            "demand" => new DemandService($this->accountId),
            "counterparty" => new CounterpartyService($this->accountId),
            "customerorder" => new oldCustomOrderService($this->accountId),
            "invoiceout" => new InvoiceoutService($this->accountId),
            "salesreturn" => new SalesReturnService($this->accountId),
        ];

        $service = $entityServices[$entityType];
        if($service == null){
            return $res->error($entityType, "entityType не найден");
        }

        // $template = Templates::where('id', $templateId)->first();

        // if($template == null)
        //     return $res->error($templateId, "Шаблон не найден");

        // $templateWithAttributes = $template->attributes()->get()->toArray();

        //2)get ms_entity_fields and info from Ms
        $fieldsArray = MsEntities::join('ms_entity_fields', 'ms_entities.id', '=', 'ms_entity_fields.ms_entities_id')
            ->where('ms_entities.keyword', "=",  $entityType)
            ->select('ms_entity_fields.keyword', 'ms_entity_fields.expand_filter')
            ->get()
            ->toArray();

        $fields = json_decode(json_encode($fieldsArray));

        $templateLogicS = new TemplateLogicService($this->accountId);

        $expandParams = $templateLogicS->prepareForExpandReq($fields);

        $expandRes = $service->getByIdWithExpand($entityId, $expandParams);
        if(!$expandRes->status)
            return $expandRes;
        $expandedInfo = $expandRes->data;
        $objectWithNeededValues = $service->cutMsObjectFromReqExpand($expandedInfo, $expandParams);

        //3)getTemplates and replace
        $setting = MainSettings::where('account_id', $this->accountId)
            ->get();

        if($setting->isEmpty())
            $res->error("", "Настройка по данному account_id не найдена");

        $templateValues = $objectWithNeededValues->data;

        $objectWithTextPositions = null;
        if($entityType != "counterparty"){
            $objectWithTextPositions = $templateLogicS->preparePositions($templateValues);
        }


        $allTemplates = $setting->first()
            ->templates()
            ->select(
                "title",
                "uuid",
                "content"
            )
            ->get()
            ->all();

        $count = 1;

        $allTemplates = array_map(function($template) use ($templateLogicS, $objectWithTextPositions, $expandedInfo, &$count, $entityType, $templateValues) {
            $content = $template->content;
            $uuid = $template->uuid;

            if($entityType != "counterparty"){
                $readyTemplate = $templateLogicS->insertIn($content, $objectWithTextPositions);
            } else {
                $readyTemplate = $templateLogicS->insertIn($content, $templateValues);
            }

            //тут я говорю найди мне все связанные атрибуты с данным шаблоном. если пусто, то шаблон simple
            $templatesWithAtttributes = Templates::join('variables', 'templates.id', '=', 'variables.template_id')
                ->join('attribute_settings', 'variables.attribute_settings_id', '=', 'attribute_settings.id')
                ->where('templates.uuid', "=",  $uuid)
                ->get()
                ->toArray();

            //4)add. fields
            if($templatesWithAtttributes){
                $templateAttributeValues = [];
                foreach($templatesWithAtttributes as $item){
                    $user_var = $item["name"];
                    $templateAttributeValues["!{{$user_var}}"] = $item["attribute_id"];
                }

                $resAttributes = $expandedInfo->attributes;

                foreach($templateAttributeValues as $key => $_){
                    $findedAttribute = array_filter(
                        $resAttributes,
                        function ($value) use ($templateAttributeValues) {
                            return in_array($value->id, $templateAttributeValues);
                        }
                    );
                    if(count($findedAttribute) > 0){
                        $firstAttributeById = array_shift($findedAttribute);
                        if($firstAttributeById->type == "customentity")
                            $templateAttributeValues[$key] = $firstAttributeById->value->name;
                        else
                            $templateAttributeValues[$key] = $firstAttributeById->value;
                    }

                }
                //add only add. fields
                $readyTemplate = $templateLogicS->insertIn($readyTemplate, $templateAttributeValues);

            }

            if($count <= 20)
                $template->content = $readyTemplate;
            else
                unset($template->content);

            $count++;

            return $template;

        }, $allTemplates);

        return $res->success($allTemplates);

    }

    function checkTemplate($content){
        $res = new Response();
        $templateLogicS = new TemplateLogicService($this->accountId);

        $uniqueFields = $templateLogicS->findAllInputForReplace($content);

        if(count($uniqueFields) >= 10){
            $uniqRes = $res->error($content, "Возможно использовать не больше 10 уникальных полей");
            return $uniqRes;
        } else {
            $uniqRes = $res->success($content);
            return $uniqRes;
        }
    }
}
