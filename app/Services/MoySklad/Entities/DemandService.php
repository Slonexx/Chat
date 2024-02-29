<?php
namespace App\Services\Entity;

use App\Clients\MsClient;
use App\Services\HandlerService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use stdClass;

class DemandService {

    private MsClient $msClient;

    public string $accountId;

    private string $urlIdentifier = "demandURL";

    private HandlerService $handlerS;

    function __construct($accountId) {
        $this->msClient = new MsClient($accountId);
        $this->accountId = $accountId;
        $this->handlerS = new HandlerService();
    }

    public function getMeta(string $id){
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
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

    public function getAgent(string $id){
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
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

    public function getOrganization(string $id){
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
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

    public function getPositions(string $id){
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
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
        $urlIdentifier = "demandURL";
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
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
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
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
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
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
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
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
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
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
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
        $urlIdentifier = "demandMetadataAttributes";
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

    public function getSum(string $id) {
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;       
                $statusCode = $obj->statusCode;
                if(property_exists($data, "sum")) {
                    $sum = $data->sum;
                    $result = response()->json($sum, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists sum", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getStore(string $id) {
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;       
                $statusCode = $obj->statusCode;
                if(property_exists($data, "store")) {
                    $store = $data->store;
                    $result = response()->json($store, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists store", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getSalesChannel(string $id) {
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;       
                $statusCode = $obj->statusCode;
                if(property_exists($data, "salesChannel")) {
                    $salesChannel = $data->salesChannel;
                    $result = response()->json($salesChannel, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists salesChannel", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getShipmentAddress(string $id) {
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getById($urlIdentifier, $id);
        $result;
        try {
            if($obj !== false) {
                $data = $obj->data;       
                $statusCode = $obj->statusCode;
                if(property_exists($data, "shipmentAddress")) {
                    $shipmentAddress = $data->shipmentAddress;
                    $result = response()->json($shipmentAddress, $statusCode);
                    return $result;
                } else {
                    return new Response("Not exists shipmentAddress", 400);
                }
            } else {

                throw new Error();
            }
        } catch(e){
            return new Response("Something wrong", $obj->statusCode);
        }
    }

    public function getById(string $id) {
        $urlIdentifier = "demandURL";
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

    public function getAll() {
        $urlIdentifier = "demandURL";
        $obj = $this->msClient->getAll($urlIdentifier);
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

    public function getTemplate($orderMeta) {
        $urlIdentifier = "demandPutURL";
        $handlerS = new HandlerService();
        $preparedOrderMeta = $handlerS->FormationMeta($orderMeta);

        $body = (object)[
            "customerOrder" => $preparedOrderMeta
        ];

        $response = $this->msClient->putWithoutId($urlIdentifier, $body);
        if(!$response->status)
            $response->message = "Не удалось получить шаблон отгрузки";
        return $response;
    }

    public function create($template) {
        $response =  $this->msClient->post($this->urlIdentifier, $template);
        if(!$response->status)
            $response->message = "Не удалось создать отгрузку";
        return $response;
    }
    /**
     * Берёт все поля из заказа покупателя и создаёт тело для отправки
     * @param object $customerOrder созданный customerOrder
     * @return object response/badResponse 
     */
    public function setBody(object $customerOrder){
        $body = new stdClass();

        $customerOrderS = new CustomOrderService($this->accountId);

        //agent!
        $agent = $customerOrderS->getAgentFromBody($customerOrder);
        if(!$agent->status)
            return $this->handlerS->createResponse(false, $agent->data);
        else
            $body->agent = $agent->data;

        //organization!
        $organization = $customerOrderS->getOrganizationFromBody($customerOrder);
        if(!$organization->status)
            return $this->handlerS->createResponse(false, $organization->data);
        else
            $body->organization = $organization->data;

        //store!
        $store = $customerOrderS->getStoreFromBody($customerOrder);
        if(!$store->status)
            return $this->handlerS->createResponse(false, $store->data);
        else
            $body->store = $store->data;

        //customerOrder!
        $customerOrderMeta = $customerOrderS->getMetaFromBody($customerOrder);
        if(!$customerOrderMeta->status)
            return $this->handlerS->createResponse(false, $customerOrderMeta->data);
        else
            $body->customerOrder = $customerOrderMeta->data;

        //organizationAccount
        $organizationAccount = $customerOrderS->getOrganizationAccountFromBody($customerOrder);
        if($organizationAccount->status)
            $body->organizationAccount = $organizationAccount->data;

        //project
        $project = $customerOrderS->getProjectFromBody($customerOrder);
        if($project->status)
            $body->project = $project->data;

        //salesChannel
        $salesChannel = $customerOrderS->getSalesChannelFromBody($customerOrder);
        if($salesChannel->status)
            $body->salesChannel = $salesChannel->data;
        
        //applicable
        $applicable = $customerOrderS->getApplicableFromBody($customerOrder);
        if($applicable->status)
            $body->applicable = $applicable->data;

        //owner
        $owner = $customerOrderS->getOwnerFromBody($customerOrder);
        if($owner->status)
            $body->owner = $owner->data;

        //group
        $group = $customerOrderS->getGroupFromBody($customerOrder);
        if($group->status)
            $body->group = $group->data;

        //shipmentAddress
        $shipmentAddress = $customerOrderS->getShipmentAddressFromBody($customerOrder);
        if($shipmentAddress->status)
            $body->shipmentAddress = $shipmentAddress->data;

        //shipmentAddressFull
        $shipmentAddressFull = $customerOrderS->getShipmentAddressFullFromBody($customerOrder);
        if($shipmentAddressFull->status)
            $body->shipmentAddressFull = $shipmentAddressFull->data;

        //rate
        $rate = $customerOrderS->getRateFromBody($customerOrder);
        if($rate->status)
            $body->rate = $rate->data;

        //positions
        $positions = $customerOrderS->getPositionsFromBody($customerOrder);
        if($positions->status)
            $body->positions = $positions->data;

        return $this->handlerS->createResponse(true, $body);
    }
    
}