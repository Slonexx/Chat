<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\oldMoySklad;
use App\Services\HandlerService;
use Illuminate\Support\Facades\Config;
use stdClass;

class CustomEntityService {
    private oldMoySklad $msClient;

    private string $accountId;

    private HandlerService $handlerS;

    public const URL_IDENTIFIER = "customentity";

    function __construct($accountId, oldMoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msClient = new oldMoySklad($accountId);
        else  $this->msClient = $MoySklad;
        $this->handlerS = new HandlerService();
        $this->accountId = $accountId;
    }

    public function getById($id){
        $res = $this->msClient->getById(self::URL_IDENTIFIER, $id);
        if(!$res->status)
            return $this->handlerS->createResponse(false, $res->data, false, "Ошибка при получении customentity");
        else
            return $res;
    }

    /**
     * use from createAddFieldsDictionaryType function (вынести)
     * @return response/badResponse check ResponseHandler(createResponse)
     */
    function createDictionary(object $attribute){
        $body = new stdClass();
        $dictionaryName = $attribute->name ?? false;
        if(!$dictionaryName)
            return $this->handlerS->createResponse(false, $attribute, false, "Не найдено имя справочника");

        $body->name = $dictionaryName;

        $res = $this->msClient->post(self::URL_IDENTIFIER, $body);
        if(!$res->status)
            return $this->handlerS->createResponse(false, $res->data, false, "Ошибка при создании customentity");
        else
            return $res;
    }

    /**
     * use from createAddFieldsDictionaryType function (вынести)
     * @return response/badResponse check ResponseHandler(createResponse)
     */
    function createAttribute($entityTypeAttributes, $body){
        if($entityTypeAttributes == false)
            return $this->handlerS->createResponse(false, $entityTypeAttributes, false, "entityTypeAttributes = false");

        $res = $this->msClient->post($entityTypeAttributes, $body);
        if(!$res->status)
            return $this->handlerS->createResponse(false, $res->data, false, "Ошибка при создании customentity");
        else
            return $res;
    }

    /**
     * use from createAddFieldsDictionaryType function (вынести)
     * @return response/badResponse check ResponseHandler(createResponse)
     */
    function setBody(object $attribute, string $customentityId){
        $body = [];
        $url = Config::get("Global.customEntityMeta");

        $attributeName = $attribute->name ?? false;
        if(!$attributeName)
            return $this->handlerS->createResponse(false, $attribute, false, "Не найдено имя аттрибута");
        $body["name"] = $attributeName;

        $body["type"] = "customentity";

        $attributeDescription = $attribute->descripion ?? "";
        if(!isset($attributeDescription))
            return $this->handlerS->createResponse(false, $attribute, false, "Не найдено описание аттрибута");
        $body["descripion"] = $attributeDescription;

        $attributeShow = $attribute->show ?? "";
        if($attributeShow !== "")
            $body["show"] = $attributeShow;

        $body["required"] = false;


        $body["customEntityMeta"] = [
            "href" => $url . $customentityId,
            "type" => "customentitymetadata",
        ];

        $objectBody = json_decode(json_encode($body));

        return $this->handlerS->createResponse(true, $objectBody);
    }

    /**
     * use from createAddFieldsDictionaryType function (вынести)
     * @return response/badResponse check ResponseHandler(createResponse)
     */
    public function tryToFind($addField){
        $dictionaryName = $addField->name ?? false;
        if(!$dictionaryName)
            return $this->handlerS->createResponse(false, $addField, false, "Не найдено имя справочника");

        $res = $this->msClient->getAll("companySettingsMetadata");
        if(!$res->status)
            return $this->handlerS->createResponse(false, $res->data, false, "Ошибка при поиске customentity");

        $rows = $res->data->customEntities ?? false;
        if($rows === false)
            return null;
        $dictionary = array_filter($rows, fn($value)=> $value->name == $dictionaryName);
        if(count($dictionary) == 0)
            return null;
        else{
            $elem = array_shift($dictionary);
            $urlArray = explode("/", $elem->entityMeta->href);
            $dictionaryId = array_pop($urlArray);
            return $this->handlerS->createResponse(true, $dictionaryId);

        }
    }
    /**
     * use from createAddFieldsDictionaryType function (вынести)
     * @return response/badResponse check ResponseHandler(createResponse)
     */
    function createValuesMoreThan1000(object $attribute, string $customentityId){
        $dictionaryValues = $attribute->values ?? false;
        if(!$dictionaryValues)
            return $this->handlerS->createResponse(false, $attribute, false, "Не найден(о/ы) значени(е/я) справочника");

        if(!is_array($dictionaryValues))
            return $this->handlerS->createResponse(false, $attribute, false, "Значения справочника не являются массивом");

        if(count($dictionaryValues) == 0)
            return $this->handlerS->createResponse(false, $attribute, false, "Для значений справочника передан пустой массив");

        $body = array_chunk($dictionaryValues, 1000);
        foreach($body as $bodyItem){
            $res = $this->msClient->postById(self::URL_IDENTIFIER, $customentityId, $bodyItem);
            if(!$res->status)
                return $this->handlerS->createResponse(false, $res->data, false, "Ошибка при заполнении customentity значениями");
        }
        

        return $this->handlerS->createResponse(true, "");
    }

}