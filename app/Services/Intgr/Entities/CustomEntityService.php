<?php
namespace App\Services\Intgr\Entities;

use App\Clients\MoySkladIntgr;
use App\Exceptions\CustomEntityException;
use App\Exceptions\MsException;
use App\Services\HandlerService;
use App\Services\HTTPResponseHandler;
use Error;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
use stdClass;

class CustomEntityService {
    private MoySkladIntgr $msClient;

    private HandlerService $handlerS;

    public const URL_IDENTIFIER = "customentity";

    function __construct(MoySkladIntgr $MoySklad) {
        $this->msClient = $MoySklad;
        $this->handlerS = new HandlerService();
    }

    public function getById($id){
        $fullKey = "msUrls." . self::URL_IDENTIFIER;
        $url = Config::get($fullKey, null);
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");
        $joinedUrl = $url . $id;
        $resHandler = new HTTPResponseHandler();
        try{
            $res = $this->msClient->get($joinedUrl);
            return $resHandler->handleOK($res);
        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при получении справочника по id|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при получении справочника по id", previous:$e);
            }
        }
    }

    public function getAll(){
        $url = Config::get("msUrls.companySettingsMetadata", null);
        $resHandler = new HTTPResponseHandler();
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");
        try{
            $res = $this->msClient->get($url);
            $customEntitiesRes = $resHandler->handleOK($res);
            if (property_exists($customEntitiesRes->data, 'customEntities')) $rows = (array) $customEntitiesRes->data->customEntities ?? [];
            else $rows = [];
            return $rows;
        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при получении всех справочников|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при получении всех справочников", previous:$e);
            }
        }
    }

    /**
     * use from createAddFieldsDictionaryType function (вынести)
     * @return response/badResponse check ResponseHandler(createResponse)
     */
    function createDictionary(string $dictionaryName){
        if(!$dictionaryName)
            throw new CustomEntityException("Не найдено имя справочника: $dictionaryName", 1);
        $body = new stdClass();
        $body->name = $dictionaryName;

        $fullKey = "msUrls." . self::URL_IDENTIFIER;
        $url = Config::get($fullKey, null);
        $resHandler = new HTTPResponseHandler();
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");

        try{
            $res = $this->msClient->post($url, $body);
            return $resHandler->handleOK($res, "справочник успешно создан");
        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при создании справочника|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при создании справочника", previous:$e);
            }
        }
    }

    /**
     * use from createAddFieldsDictionaryType function (вынести)
     * @return response/badResponse check ResponseHandler(createResponse)
     */
    function createAttribute($entityTypeAttributes, $body){
        $fullKey = "msUrls." . $entityTypeAttributes;
        $url = Config::get($fullKey, null);
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");

        try{
            $this->msClient->post($url, $body);
        } catch(RequestException $e){
            if($e->hasResponse()){
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $encodedBody = $response->getBody()->getContents();
                throw new MsException("ошибка при создании аттрибута типа справочник|" . $encodedBody, $statusCode);
            } else {
                throw new MsException("неизвестная ошибка при создании аттрибута типа справочник", previous:$e);
            }
        }
    }

    /**
     * настройка тела запроса для создания аттрибута типа справочник
     * use from createAddFieldsDictionaryType function (вынести)
     * @return response/badResponse check ResponseHandler(createResponse)
     */
    function setBody(object $attribute, string $customentityId){
        $body = [];
        $url = Config::get("msUrls.customEntityMeta");

        $attributeName = $attribute->name ?? false;
        if(!$attributeName)
            throw new CustomEntityException("Не найдено имя справочника:" . json_encode($attribute) , 1);
        $body["name"] = $attributeName;

        $body["type"] = "customentity";

        $attributeDescription = $attribute->descripion ?? "";
        if(!isset($attributeDescription))
            throw new CustomEntityException("Не найдено описание аттрибута" . json_encode($attribute) , 2);
        $body["descripion"] = $attributeDescription;

        $attributeShow = $attribute->show ?? null;
        if(isset($attributeShow))
            $body["show"] = $attributeShow;

        $body["required"] = false;

        $body["customEntityMeta"] = (object)[
            "href" => $url . $customentityId,
            "type" => "customentitymetadata",
        ];

        $objectBody = json_decode(json_encode($body));

        return $objectBody;
    }

    /**
     * use from createAddFieldsDictionaryType function (вынести)
     * @param string $addField название справочника
     * @return Response | null
     */
    public function tryToFind(string $dictionaryName, array $dictionaries){
        if(!$dictionaryName)
            throw new CustomEntityException("Не найдено имя справочника: $dictionaryName", 1);

        //в мс не создано ни одного справочника
        if(empty($dictionaries))
            return null;
        $dictionary = array_filter($dictionaries, fn($value)=> $value->name == $dictionaryName);

        //не нашли справочник по имени
        if(count($dictionary) == 0)
            return null;
        else{
            $elem = array_shift($dictionary);
            $dictionaryId = basename($elem->entityMeta->href);
            return $this->handlerS->createResponse(true, $dictionaryId);
        }
    }
    /**
     * use from createAddFieldsDictionaryType function (вынести)
     * @param string[] $dictionaryValues
     * @return response/badResponse check ResponseHandler(createResponse)
     */
    function createValuesMoreThan1000($dictionaryValues, string $customentityId){
        if(!is_array($dictionaryValues))
            throw new CustomEntityException("Значения справочника не являются массивом |" . json_encode($dictionaryValues), 1);

        if(count($dictionaryValues) == 0)
            throw new CustomEntityException("Для значений справочника передан пустой массив", 2);

        $fullKey = "msUrls." . self::URL_IDENTIFIER;
        $url = Config::get($fullKey, null);
        if(!is_string($url) || $url == null)
            throw new Error("url отсутствует или имеет некорректный формат");

        //ограничение на 1000 значений МС
        $body = array_chunk($dictionaryValues, 1000);

        foreach($body as $bodyItem){
            $urlWithId = $url . $customentityId;
            try{
                $this->msClient->post($urlWithId, $bodyItem);
            } catch(RequestException $e){
                if($e->hasResponse()){
                    $response = $e->getResponse();
                    $statusCode = $response->getStatusCode();
                    $encodedBody = $response->getBody()->getContents();
                    throw new MsException("ошибка при заполнении справочника значениями|" . $encodedBody, $statusCode);
                } else {
                    throw new MsException("неизвестная ошибка при заполнении справочника значениями", previous:$e);
                }
            }
        }
    }

}
