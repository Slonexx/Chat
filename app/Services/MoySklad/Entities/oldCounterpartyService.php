<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\oldMoySklad;
use App\Services\MsFilterService;
use App\Services\Response;
use Exception;
use Illuminate\Support\Facades\Config;

class oldCounterpartyService{

    private oldMoySklad $msC;

    public string $accountId;

    private Response $res;

    private const URL_IDENTIFIER = "agent";

    function __construct($accountId, oldMoySklad $MoySklad = null) {
        if ($MoySklad == null) $this->msC = new oldMoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->res = new Response();
        $this->accountId = $accountId;
    }

    public function getById(string $id) {
        $res = $this->msC->getById(self::URL_IDENTIFIER, $id);
        if(!$res->status)
            return $res->addMessage("Ошибка при получении контрагента");
        else
            return $res;
    }

    public function getByParam(string $name, mixed $value, string $errorMes) {

        $filterS = new MsFilterService();
        
        $url = $filterS->prepareUrlWithParam(self::URL_IDENTIFIER, $name, $value);
        $nameRes = $this->msC->getByUrl($url);
        if(!$nameRes->status)
            return $nameRes->addMessage($errorMes);
        else
            return $this->res->success($nameRes->data->rows);

    }

    public function getByIdWithExpand(string $id, array $expandParams) {
        $agentUrl = Config::get("Global")[self::URL_IDENTIFIER];
        $preppedUrl = $agentUrl . $id . "?expand=";
        if(count($expandParams) == 0){
            return $this->res->error($expandParams, "Нет параметров для expand");
        }
        foreach($expandParams as $param){
            if($param != null)
            $preppedUrl = "{$preppedUrl}{$param},";
        }
        $expandedRes = $this->msC->getByUrl($preppedUrl);
        if(!$expandedRes->status)
            return $expandedRes->addMessage("Ошибка при получении расширенного контрагента");
        else
            return $expandedRes;
    }

    /**
     * specific f
     */
    function cutMsObjectFromReqExpand($objectMs){
        try{
            $preppedChangeList = [];

            $preppedChangeList["{agent}"] = $objectMs->name;
            if($objectMs->companyType == "individual"){
                $preppedChangeList["{agentFIO}"] = $objectMs->legalTitle ?? "{agentFIO}";
            } else {
                $preppedChangeList["{agentFIO}"] = "{agentFIO}";
            }

            $preppedChangeList["{name}"] = $objectMs->name;

            $description = $objectMs->description ?? false;
            if(!empty($description))
                $preppedChangeList["{description}"] = $description;

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
            return $res->addMessage("Ошибка при создании контрагента");
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