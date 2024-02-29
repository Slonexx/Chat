<?php
namespace App\Services\Entity;

use App\Clients\MsClient;
use App\Models\AttributeSettings;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use App\Services\HandlerService;
use App\Services\MsFilterService;
use App\Models\OrderSettings;
use Illuminate\Database\QueryException;
use App\Services\Entity\AgentService;
use stdClass;

/**
 * @property MsClient $msClient
 * @property string $accountId
 * @property HandlerService $handlerS
 * */
class CustomOrderService {

    private MsClient $msClient;

    private string $accountId;

    private mixed $shipmentAddress;

    private mixed $attributes;

    private object $positions;

    public const URL_IDENTIFIER = "customerOrderURL";

    public const ATTRIBUTES_URL_IDENTIFIER = "customerOrderMetadataAttributes";

    private $response = [
        "statusCode" => 200,
        "status" => true
    ];

    private $badResponse = [
        "statusCode" => 400,
        "status" => false
    ];

    //private string $additionalField;

    private HandlerService $handlerS;

    function __construct(string $accountId, /*$fieldSettings*/) {
        $this->msClient = new MsClient($accountId);
        $this->accountId = $accountId;
        //$this->additionalField = $fieldSettings;
        $this->response = json_decode(json_encode($this->response));
        $this->badResponse = json_decode(json_encode($this->badResponse));
        $this->handlerS = new HandlerService();
    }

    public function setProperty($name, $value) {
        $this->attributes[$name] = $value;
    }
    /**
     * Достаёт метаданные из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getMetaFromBody(object $body){
        $meta = $body->meta ?? false;
        if(!$meta)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует meta");
        else{
            $customerOrderMeta = $this->handlerS->FormationMeta($meta);
            return $this->handlerS->createResponse(true, $customerOrderMeta);
        }
    }

    public function getMeta(string $id){
        $urlIdentifier = "customerOrderURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
        dd($obj);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;
                $statusCode = $obj->statusCode;
                if(property_exists($data, "meta")) {
                    $result = response()->json($data->meta, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists meta", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }
    /**
     * Достаёт метаданные агента из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getAgentFromBody(object $body){
        $agent = $body->agent ?? false;
        if(!$agent)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует agent");
        else{
            return $this->handlerS->createResponse(true, $agent);
        }
    }

    public function getAgent(string $id){
        $obj = $this->msClient->getById($id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;
                $statusCode = $obj->statusCode;
                if(property_exists($data, "agent")) {
                    $agent = $data->agent;
                    $result = response()->json($agent->meta, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists agent", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getAgentMetaById(string $id){
        $response = $this->msClient->getById("agent", $id);
        if($response->status == false)
            return $response;
        $meta = $response->data->meta;
        return $this->handlerS->createResponse(true, $meta);
    }

    public function getOrganization(string $id){
        $obj = $this->msClient->getById($id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;
                $statusCode = $obj->statusCode;
                if(property_exists($data, "organization")) {
                    $organization = $data->organization;
                    $result = response()->json($organization->meta, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists organization", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }
    /**
     * Достаёт метаданные орагнизации из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getOrganizationFromBody(object $body){
        $organization = $body->organization ?? false;
        if(!$organization)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует organization");
        else
            return $this->handlerS->createResponse(true, $organization);
    }
    /**
     * Достаёт метаданные склада из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getStoreFromBody(object $body){
        $store = $body->store ?? false;
        if(!$store)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует store");
        else
            return $this->handlerS->createResponse(true, $store);
    }
    /**
     * Достаёт метаданные счёта организации из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getOrganizationAccountFromBody(object $body){
        $organizationAccount = $body->organizationAccount ?? false;
        if(!$organizationAccount)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует organizationAccount");
        else
            return $this->handlerS->createResponse(true, $organizationAccount);
    }

    /**
     * Достаёт метаданные проекта из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getProjectFromBody(object $body){
        $project = $body->project ?? false;
        if(!$project)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует project");
        else
            return $this->handlerS->createResponse(true, $project);
    }

    /**
     * Достаёт метаданные канала продаж из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getSalesChannelFromBody(object $body){
        $salesChannel = $body->salesChannel ?? false;
        if(!$salesChannel)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует salesChannel");
        else
            return $this->handlerS->createResponse(true, $salesChannel);
    }

    /**
     * Достаёт отметку о проведении из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getApplicableFromBody(object $body){
        $applicable = $body->applicable ?? false;
        if(!$applicable)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует applicable");
        else
            return $this->handlerS->createResponse(true, $applicable);
    }

    /**
     * Достаёт метаданные владельца (сотрудника) из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getOwnerFromBody(object $body){
        $owner = $body->owner ?? false;
        if(!$owner)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует owner");
        else
            return $this->handlerS->createResponse(true, $owner);
    }

    /**
     * Достаёт метаданные отдела сотрудника из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getGroupFromBody(object $body){
        $group = $body->group ?? false;
        if(!$group)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует group");
        else
            return $this->handlerS->createResponse(true, $group);
    }

    /**
     * Достаёт адрес доставки из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getShipmentAddressFromBody(object $body){
        $shipmentAddress = $body->shipmentAddress ?? false;
        if(!$shipmentAddress)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует shipmentAddress");
        else
            return $this->handlerS->createResponse(true, $shipmentAddress);
    }

    /**
     * Достаёт подробный адрес доставки из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getShipmentAddressFullFromBody(object $body){
        $shipmentAddressFull = $body->shipmentAddressFull ?? false;
        if(!$shipmentAddressFull)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует shipmentAddressFull");
        else
            return $this->handlerS->createResponse(true, $shipmentAddressFull);
    }

    /**
     * Достаёт валюту из созданного customerOrder 
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getRateFromBody(object $body){
        $rate = $body->rate ?? false;
        if(!$rate)
            return $this->handlerS->createResponse(false, "В теле запроса отсутствует rate");
        else
            return $this->handlerS->createResponse(true, $rate);
    }

    /**
     * Достаёт позиции из созданного customerOrder.
     * @param object $body customerOrder body
     * @return object response/badResponse 
     */
    public function getPositionsFromBody(object $body){
        $customerorderId = $body->id;

        $msFilterS = new MsFilterService();
        $customerOrderURL = $msFilterS->getUrl("customerOrderURL");
        $preparedUrl = "{$customerOrderURL}{$customerorderId}/positions";
        
        $response = $this->msClient->getByUrl($preparedUrl);
        if(!$response->status){
            $response->message = "Не удалось получить позиции customerOrder {$customerorderId}";
            return $response;
        }

        $positionsMs = $response->data->rows;
        $positions = [];
        if(count($positionsMs) == 0){
            return $this->handlerS->createResponse(false, "Позиции в customerOrder {$customerorderId} не найдены");
        }
        foreach($positionsMs as $item){
            $position = new stdClass();
            $position->quantity = $item->quantity;
            $position->price = $item->price;
            $position->discount = $item->discount;
            $position->vat = $item->vat;
            $position->assortment = $item->assortment;
            $reserve = $item->reserve ?? false;
            if($reserve)
                $position->reserve = $reserve;
            $positions[] = $position;
        }
        return $this->handlerS->createResponse(true, $positions);
        
    }

    public function getOrganizationMetaById(string $id){
        $response = $this->msClient->getById("organization", $id);
        if($response->status == false)
            return $response;
        $meta = $response->data->meta;
        return $this->handlerS->createResponse(true, $meta);
    }

    public function getPositions(string $id){
        $obj = $this->msClient->getById($id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;
                $statusCode = $obj->statusCode;
                if(property_exists($data, "positions")) {
                    $positions = $data->positions;
                    $result = response()->json($positions->meta, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists positions", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getObject(string $id){
        $urlIdentifier = "customerOrderURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;
                $statusCode = $obj->statusCode;

                $result = response()->json($data, $statusCode);
                return $result;
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getRate(string $id){
        $obj = $this->msClient->getById($id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;
                $statusCode = $obj->statusCode;
                if(property_exists($data, "rate")) {
                    $rate = $data->rate->currency->meta;
                    $result = response()->json($rate, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists rate", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getDescription(string $id){
        $obj = $this->msClient->getById($id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;
                $statusCode = $obj->statusCode;
                if(property_exists($data, "description")) {
                    $description = $data->description;
                    $result = response()->json($description, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists description", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getExternalCode(string $id){
        $obj = $this->msClient->getById($id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;
                $statusCode = $obj->statusCode;
                $externalCode = $data->externalCode;
                $result = response()->json($externalCode, $statusCode);
                return $result;

            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getAttributes(string $id){
        $obj = $this->msClient->getById($id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;
                $statusCode = $obj->statusCode;
                if(property_exists($data, "attributes")) {
                    $attributes = $data->attributes;
                    $result = response()->json($attributes, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists attributes", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getAttributesByName(string $id, string $name){
        $obj = $this->msClient->getById($id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;
                $statusCode = $obj->statusCode;
                if(property_exists($data, "attributes")) {
                    $attributes = $data->attributes;
                    $findedAttributes = array_filter(
                        $attributes,
                        fn ($value) => $value->name == $name
                    );
                    $arrayMeta;
                    foreach($findedAttributes as $item){
                        $arrayMeta[] = $item->meta;
                    }
                    $result = response()->json($arrayMeta, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists attributes", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getMetadataAttributes(){
        $url = Config::get('Global')[$urlIdentifier];
        $obj = $this->msClient->getByUrl($url);
        $result;
        try {
            if($obj !== false) {
                $size = $obj->data->meta->size;       
                $statusCode = $obj->statusCode;
                if($size > 0) {
                    $metadataAttributes = $obj->data->rows;
                    $result = response()->json($metadataAttributes, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists metadata attributes", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    //public function setAttributes(object $attributesValues){
        // $attributes = [
        //     "orderId",
        //     "deliveryType",
        //     "link",
        //     "futureFeatures"
        // ];
        // foreach($attributes as $item){
        //     $attributeIsExists = property_exists($attributesValues, $item);
        //     if($attributeIsExists)
        //         $this->setProperty($item, $attributesValues->{$item});

        // }

    //    $this->attributes = $attributesValues;

    //}

    public function setAddress(object $inputAddress){
        $orderIdIsExists = property_exists($inputAddress, "address");
        if($orderIdIsExists) {
            $address = (object) $inputAddress->address;
            $msAddress = [
                "city" => $address->town,
                "street" => $address->streetName,
                "house" => $address->streetNumber,
            ];
            $this->shipmentAddress = $msAddress;
        }

    }

    public function setPositions(object $positions){
        $this->positions = $positions;
    }

    public function getByIdCustomOrderStates($id) {
        $urlIdentifier = "customerOrderStatesURL";
        return $this->msClient->getById($urlIdentifier, $id);
    }
    /**
     * @param mixed $body (object)[
     * 
     *  "organization" => (object)[
     * 
     *   "meta" => (object)[
     *      *organization_meta*
     *   ]
     * 
     *  ],
     * 
     *  "agent" => (object)[
     * 
     *   "meta" => (object)[
     *      *agent_meta*
     *   ]
     * 
     *  ]
     * ]
     */
    public function postByBody($kaspiOrder) {
        try{
            $orderSet = OrderSettings::where("account_id", $this->accountId)
                ->get(["organization_id", "counterparty_id"])
                ->toArray();
        } catch(QueryException $e) {
            return $this->handlerS->createResponse(false, $e->getMessage());
        }    

        if(count($orderSet) == 0) {
            return $this->handlerS->createResponse(false, "Настройки по данному accountId не найдены");
        }
        $setting = json_decode(json_encode($orderSet[0]));
        $orgId = $setting->organization_id;
        $agentId = $setting->counterparty_id;
        if($agentId != null){
            $agentMeta = $this->getAgentMetaById($agentId);
            if($agentMeta->status == false)
                return $agentMeta;
        } else {
            $agentS = new AgentService($this->accountId);
            $agentMeta = $agentS->searchByCounterpartyForFilter($kaspiOrder);
            if(!$agentMeta->status)
                return $agentMeta;
        }
        $organizationMeta = $this->getOrganizationMetaById($orgId);
        if(!$organizationMeta->status)
            return $organizationMeta;

        $preparedOrg = $this->handlerS->FormationMeta($organizationMeta->data);
        $preparedAgent = $this->handlerS->FormationMeta($agentMeta->data);
        $body = (object)[
            "organization" => $preparedOrg,
            "agent" => $preparedAgent
        ];
        //return $this->postByBody($body);

        $msFilterS = new MsFilterService();
        $tengeCurrencyURL = $msFilterS->prepareUrlWithParam("currencyUrl", "name", "тенге");

        $additionalUrl = $msFilterS->getUrl("currencyUrl");

        $answer = $this->msClient->getByUrl($tengeCurrencyURL);
        $rows = $answer->data->rows;
        $currencyMeta = null;
        if(count($rows) !== 0){
            //0 - tenge
            $currencyMeta = (object) $rows[0]->meta;
            
        } else {
            $tenge = (object) array(
                "name" => "тенге",
                "code" => "398",
                "isoCode" => "KZT"
            );
            $answer = $this->msClient->postByUrl($additionalUrl, $tenge);
            if($answer->status == false)
                return $answer;
            $currencyMeta = $answer->data->meta;
            // $statusCode = $answer->statusCode;
            // if($statusCode !== 200) {
            //     return response()->json("Currency KZT didn't create", $statusCode);
            // }
        }
        $preparedMeta = $this->handlerS->FormationMeta($currencyMeta);
        $rate = (object) array(
            "currency" => $preparedMeta,
            "value" => 1
        );
        $body->rate = $rate;
        //account
        //store
        //sales channel
        //project
        //info_counterparty 1-description 2-shipmentAddressFull->comment 3-1&2

        // $body->shipmentAddressFull = $this->shipmentAddress;
        // $body->positions = $this->positions;
        // $body-> attributes = $this->attributes;

        $sendedCustomerOrder = $this->msClient->post(self::URL_IDENTIFIER, $body);
        return $sendedCustomerOrder;
        
    }

    public function changeStatus($customerOrderId, $body) {
        $result = $this->msClient->put(self::URL_IDENTIFIER, $body, $customerOrderId);
        if(!$result->status) {
            return $this->handlerS->createResponseMessageFirst(false, $result->data, false, "Ошибка при изменении статуса заказа покупателя в MC");
        } else {
            return $this->handlerS->createResponseMessageFirst(true, $result->data, false, "Статус заказа покупателя в MC успешно обновлён!");
        }
    }

    public function changeApplicable($customerOrderId, $body) {
        $result = $this->msClient->put(self::URL_IDENTIFIER, $body, $customerOrderId);
        if(!$result->status) {
            return $this->handlerS->createResponse(false, $result->data, false, "Ошибка при снятии флажка 'Проведён' заказа покупателя в MC");
        } else {
            return $result;
        }
        
    }

    public function getCustomerOrderId($attributeKaspiId, $kaspiId) {
        $primaryURL =  Config::get('Global')[self::URL_IDENTIFIER];

        $secondaryUrlIdentifier = "customerOrderMetadataAttributes";
        $joinedSecondaryURL =  Config::get('Global')[$secondaryUrlIdentifier] . $attributeKaspiId;

        $preparedURL = "{$primaryURL}?filter={$joinedSecondaryURL}={$kaspiId}";
        $response = $this->msClient->getByUrl($preparedURL);

        if($response->status){
            $customerOrder = $response->data->rows;
            $findedCustomerOrder = count($customerOrder);
            $handlerS = new HandlerService();
            if($findedCustomerOrder == 0) {
                return $handlerS->createResponse(false, "Заказы не найдены");
            } else {
                return $handlerS->createResponse(true, $customerOrder[0]->id);
            }
            
        } else {
            return $response;
        }
    }

    public function checkCreating(string $kaspiId) {
        $msFilterS = new MsFilterService();
        $handlerS = new HandlerService();

        try{
            $kaspiAttributeId = AttributeSettings::where("account_id", $this->accountId) 
                ->where("type", "kaspi_id")
                ->get(["attribute_id"]);
        } catch(QueryException $e) {
            return $handlerS->createResponse(false, $e->getMessage());
        }

        if(count($kaspiAttributeId) == 0)
            return $handlerS->createResponse(false, "id аттрибута kaspi_id не найден");

        $orderSettings = $kaspiAttributeId->first();
        $orderSetting = json_decode(json_encode($orderSettings));
        $attributeId = $orderSetting->attribute_id;
        $preparedUrl = $msFilterS->prepareUrlForFilter("customerOrderURL", "customerOrderMetadataAttributes", $attributeId, $kaspiId);

        $response = $this->msClient->getByUrl($preparedUrl);

        $rows = $response->data->rows;

        if(count($rows) == 0)
            return $handlerS->createResponse(true, "+");
        else 
            return $handlerS->createResponse(false, "Заказ уже был создан");
    }

    function create($body){
        $result = $this->msClient->post(self::URL_IDENTIFIER, $body);
        if(!$result->status) {
            $result->message = "Ошибка при создании заказа покупателя в MC";
        } else {
            $result->message = "Заказ покупателя в MC успешно создан!";
        }
        return $result;
    }

    public function getStatus(string $orderId) {
        $res = $this->msClient->getById(self::URL_IDENTIFIER, $orderId);
        if(!$res->status){
            return $this->handlerS->createResponseMessageFirst(false, $res->data, false, "Ошибка при получении заказа покупателя в MC");
        }
        $url = $res->data->state->meta->href;
        $res = $this->msClient->getByUrl($url);
        if(!$res->status){
            return $this->handlerS->createResponseMessageFirst(false, $res->data, false, "Ошибка при получении статуса заказа покупателя в MC");
        }
        $msStateId = $res->data->id;
        return $this->handlerS->createResponseMessageFirst(true, $msStateId, false, "Статус заказа покупателя в MC успешно получен!");
    }



    
}