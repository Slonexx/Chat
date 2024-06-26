<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\oldMoySklad;
use App\Services\Response;
use Exception;
use Illuminate\Support\Facades\Config;
use stdClass;

class DemandService {

    private oldMoySklad $msC;

    public string $accountId;

    private Response $res;

    private const URL_IDENTIFIER = "demand";

    function __construct($accountId) {
        $this->msC = new oldMoySklad($accountId);
        $this->res = new Response();
        $this->accountId = $accountId;
    }

    public function getById(string $id) {
        $res = $this->msC->getById(self::URL_IDENTIFIER, $id);
        if(!$res->status)
            return $res->addMessage("Ошибка при получении отгрузки");
        else
            return $res;
    }

    public function getByIdWithExpand(string $id, array $expandParams) {
        $demandUrl = Config::get("Global")[self::URL_IDENTIFIER];
        $preppedUrl = $demandUrl . $id . "?expand=";
        if(count($expandParams) == 0){
            return $this->res->error($expandParams, "Нет параметров для expand");
        }
        foreach($expandParams as $param){
            if($param != null)
            $preppedUrl = "{$preppedUrl}{$param},";
        }
        $expandedRes = $this->msC->getByUrl($preppedUrl);
        if(!$expandedRes->status)
            return $expandedRes->addMessage("Ошибка при получении расширенной отгрузки");
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

            $salesChannel = $objectMs->salesChannel ?? false;
            if(!empty($salesChannel))
                $preppedChangeList["{salesChannel}"] = $salesChannel->name;
            $preppedChangeList["{rate}"] = $objectMs->rate->currency->name;
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

}
