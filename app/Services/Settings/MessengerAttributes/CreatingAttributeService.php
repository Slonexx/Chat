<?php
namespace App\Services\Settings\MessengerAttributes;

use App\Models\MainSettings;
use App\Models\MessengerAttributes;
use App\Services\Response;
use Exception;
use Illuminate\Support\Facades\Config;

class CreatingAttributeService {

    private string $accountId;

    function __construct($accountId) {
        $this->accountId = $accountId;
    }
    /**
     * создаёт аттрибуты
     * @param string $configName название файла с атрибутами в папке config
     * @param string $entityType тип сущности МС для которой создаются аттрибуты
     * @param string[] $cert_fields название доп.полей, которые необходимо создать для МС(в данном случает attributes)
     * @param mixed $msEntityService Проинициализированный сервис сущностей(должен содержать методы getAllAttributes, createAttribute, getAttributesById)
     */
    function createAttribute(string $configName, string $entityType, array $cert_fields, mixed $msEntityService) : Response{
        $config = Config::get($configName);

        $organizationFields = array_filter($config, fn($key)=> in_array($key, $cert_fields), ARRAY_FILTER_USE_KEY);

        foreach($organizationFields as $key => $attributeField){
            try {
                $settings = new MessengerAttributeService($this->accountId, $entityType, $key);
                if($settings->attribute_id === null){
                    $res = $msEntityService->getAllAttributes(false);
                    if(!$res->status)
                        return $res;
                    $attributes = $res->data;
                    $arrayFindedAttributes = array_filter($attributes, fn($value)=> $value->name == $attributeField->name);
                    $attributeId = null;
                    if(count($arrayFindedAttributes) == 0){
                        //create
                        $attributeField->required = false;
                        $res = $msEntityService->createAttribute($attributeField);
                        if(!$res->status)
                            return $res;
                        $attributeId = $res->data->id;
                    } else {
                        $attribute = array_shift($arrayFindedAttributes);
                        $attributeId = $attribute->id;
                    }
                    $mainSet = MainSettings::where("account_id", $this->accountId)->get()->first();
                    $attributeSettings = [
                        "entity_type" => $entityType,
                        "name" => $key,
                        "attribute_id" => $attributeId
                    ];
                    $mainSet->mesAttrs()->create($attributeSettings);
                } else {
                    $attributeId = $settings->attribute_id;
                    $res = $msEntityService->getAttributesById($attributeId);
                    if(!$res->status){
                        if($res->statusCode == 404){
                            //create
                            $attributeField->required = false;
                            $res = $msEntityService->createAttribute($attributeField);
                            if(!$res->status)
                                return $res;
                            $modelCheck = MessengerAttributes::getFirst($this->accountId, $entityType, $key);
                            if ($modelCheck != null)
                                MessengerAttributes::destroy($modelCheck->id);
                            $mainSet = MainSettings::where("account_id", $this->accountId)->get()->first();
                            $attributeSettings = [
                                "entity_type" => $entityType,
                                "name" => $key,
                                "attribute_id" => $res->data->id
                            ];
                            $mainSet->mesAttrs()->create($attributeSettings);
                        }
                        else{
                            $result = $res->error($res->data);
                            return $result;
                        }
                    }

                }
            } catch(Exception $e){
                return $res->error($e->getMessage(), "Проблемы с созданием аттрибута(");
            }
        }
        $res = new Response();
        return $res->success((object)[], "Аттрибуты для сущности {$entityType} созданы");
    }
}
