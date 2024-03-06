<?php
namespace App\Services\MoySklad;

use App\Clients\MsClient;
use App\Clients\MoySklad;
use App\Models\MsEntities;
use App\Models\OrderSettings;
use App\Models\Templates;
use App\Services\CutService;
use App\Services\HandlerService;
use App\Services\MoySklad\Entities\DemandAttributes;
use App\Services\MoySklad\Entities\DemandService;
use App\Services\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\Template\Template;
use stdClass;

class TemplateService {
    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    //private HandlerService $handlerS;

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
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
            "demand" => new DemandService($this->accountId)
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
        $template = Templates::where('id', $templateId)
            ->get();

        if($template->isEmpty())
            $res->error("Шаблон по данному id не найден");
        $content = $template->first()->content;

        $templateValues = $objectWithNeededValues->data;

        $objectWithTextPositions = $templateLogicS->preparePositions($templateValues);

        $readyTemplate = $templateLogicS->insertIn($content, $objectWithTextPositions);

        //тут я говорю найди мне все связанные атрибуты с данным шаблоном. если пусто, то шаблон simple
        $templatesWithAtttributes = Templates::join('variables', 'templates.id', '=', 'variables.template_id')
            ->join('attribute_settings', 'variables.attribute_settings_id', '=', 'attribute_settings.id')
            ->where('templates.id', "=",  $templateId)
            ->get()
            ->toArray();
        
        //4)add. fields
        if($templatesWithAtttributes){
            $templateAttributeValues = [];
            foreach($templatesWithAtttributes as $item){
                $user_var = $item["name"];
                $templateAttributeValues["{{$user_var}}"] = $item["attribute_id"];
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
                    $templateAttributeValues[$key] = $firstAttributeById->value;
                }

            }
            //add only add. fields
            $readyTemplate = $templateLogicS->insertIn($readyTemplate, $templateAttributeValues);
            
            return $res->success($readyTemplate);
        }

    }
}