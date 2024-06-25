<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\oldMoySklad;
use App\Services\Response;
use Exception;
use Illuminate\Support\Facades\Config;
use stdClass;

class oldCustomOrderService {

    private oldMoySklad $msC;

    public string $accountId;

    private Response $res;

    private const URL_IDENTIFIER = "customerorder";

    function __construct($accountId, oldMoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new oldMoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->res = new Response();
        $this->accountId = $accountId;
    }

    public function getById(string $id) {
        $res = $this->msC->getById(self::URL_IDENTIFIER, $id);
        if(!$res->status)
            return $res->addMessage("Ошибка при получении заказа покупателя");
        else
            return $res;
    }

    public function getByIdWithExpand(string $id, array $expandParams) {
        $customOrderUrl = Config::get("Global")[self::URL_IDENTIFIER];
        $preppedUrl = $customOrderUrl . $id . "?expand=";
        if(count($expandParams) == 0){
            return $this->res->error($expandParams, "Нет параметров для expand");
        }
        foreach($expandParams as $param){
            if($param != null)
            $preppedUrl = "{$preppedUrl}{$param},";
        }
        $expandedRes = $this->msC->getByUrl($preppedUrl);
        if(!$expandedRes->status)
            return $expandedRes->addMessage("Ошибка при получении расширенного заказа");
        else
            return $expandedRes;
    }

    /**
     * specific f
     */
    function cutMsObjectFromReqExpand($objectMs){
        try{
            $preppedChangeList = [];

            $preppedChangeList["{agent}"] = $objectMs->agent->name;
            if($objectMs->agent->companyType == "individual"){
                $preppedChangeList["{agentFIO}"] = $objectMs->agent->legalTitle ?? "{agentFIO}";
            } else {
                $preppedChangeList["{agentFIO}"] = "{agentFIO}";
            }


            $preppedChangeList["{name}"] = $objectMs->name;
            $preppedChangeList["{organization}"] = $objectMs->organization->name;

            $deliveryPlannedMoment = $objectMs->deliveryPlannedMoment ?? false;
            if(!empty($deliveryPlannedMoment))
                $preppedChangeList["{deliveryPlannedMoment}"] = $deliveryPlannedMoment;

            $salesChannel = $objectMs->salesChannel ?? false;
            if(!empty($salesChannel))
                $preppedChangeList["{salesChannel}"] = $salesChannel->name;
            $preppedChangeList["{rate}"] = $objectMs->rate->currency->name;

            $store = $objectMs->store ?? false;
            if(!empty($store))
                $preppedChangeList["{store}"] = $objectMs->store->name;

            $contract = $objectMs->contract ?? false;
            if(!empty($contract))
                $preppedChangeList["{contract}"] = $contract->name;

            $project = $objectMs->project ?? false;
            if(!empty($project))
                $preppedChangeList["{project}"] = $project->name;

            $shipmentAddress = $objectMs->shipmentAddress ?? false;
            if(!empty($shipmentAddress))
                $preppedChangeList["{shipmentAddress}"] = $shipmentAddress;

            $description = $objectMs->description ?? false;
            if(!empty($description))
                $preppedChangeList["{description}"] = $description;

            $state = $objectMs->state ?? false;
            if(!empty($state))
                $preppedChangeList["{state}"] = $state->name;

            if (property_exists($objectMs, 'sum')) {
                $preppedChangeList["{sum}"] = $objectMs->sum > 0 ? $objectMs->sum/100 : 0;
            } else  $preppedChangeList["{sum}"] = 0;


            $arrayPositions = $objectMs->positions->rows;

            $preppedChangeList["{positions}"] = [];
            if(count($arrayPositions) > 0)
            foreach($arrayPositions as $position){
                $temp = new stdClass();
                $temp->price = $position->price;
                $temp->count = $position->quantity;
                $temp->name = $position->assortment->name;
                $preppedChangeList["{positions}"][] = $temp;
            }

            $res = new Response();

            return $res->success($preppedChangeList);

        } catch (Exception $e){
            $res = new Response();
            $answer = $res->error($e, $e->getMessage());
            return $answer;
        }
    }

    public function create($body){
        $res = $this->msC->post(self::URL_IDENTIFIER, $body);
        if(!$res->status)
            return $res->addMessage("Ошибка при создании заказа покупателя");
        else
            return $res;
    }

    function update($id, $body, $errorMes){
        $res = $this->msC->put(self::URL_IDENTIFIER, $body, $id);
        if(!$res->status)
            return $res->addMessage($errorMes);
        else
            return $res;
    }


}
