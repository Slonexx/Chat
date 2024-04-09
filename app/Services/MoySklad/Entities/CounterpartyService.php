<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\MoySklad;
use App\Models\MessengerAttributes;
use App\Services\MsFilterService;
use App\Services\Response;
use Exception;
use Illuminate\Support\Facades\Config;
use stdClass;

class CounterpartyService{

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    private const URL_IDENTIFIER = "agent";

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
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

    public function getByPhone(string $name, string $phone, string $addF, string $attribute_id) {

        $filterS = new MsFilterService();
        
        $url = $filterS->prepareUrlWithParam(self::URL_IDENTIFIER, "name", $name);
        $nameRes = $this->msC->getByUrl($url);
        if(!$nameRes->status)
            return $nameRes->addMessage("Ошибка при поиске контрагента по имени");

        if(count($nameRes->data->rows) == 0){
            $url = $filterS->prepareUrlWithParam(self::URL_IDENTIFIER, "phone", $phone);
            $phoneRes = $this->msC->getByUrl($url);
            if(!$phoneRes->status)
                return $phoneRes->addMessage("Ошибка при поиске контрагента по номеру");

            if(count($phoneRes->data->rows) == 0 && $addF != false){    
                
                $filterUrl = $filterS->prepareUrlForFilter(self::URL_IDENTIFIER, "agentMetadataAttributes", $attribute_id, $addF);
                $res = $this->msC->getByUrl($filterUrl);
                if(!$res->status)
                    return $res->addMessage("Ошибка при поиске контрагента по доп полю");
                else
                    return $this->res->success($res->data->rows); 

            } else
                return $this->res->success($phoneRes->data->rows);
                 
        } else
            return $this->res->success($nameRes->data->rows);

    }

    public function getByEmail(string $email, string $attribute_id) {

        $filterS = new MsFilterService();
    
        if($email != false){    
            
            $filterUrl = $filterS->prepareUrlForFilter(self::URL_IDENTIFIER, "agentMetadataAttributes", $attribute_id, $email);
            $res = $this->msC->getByUrl($filterUrl);
            if(!$res->status)
                return $res->addMessage("Ошибка при поиске контрагента по доп полю");
            else
                return $this->res->success($res->data->rows); 

        } else
            return $this->res->success([]);
                

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

    function addTags($id, $tags){
        $body = new stdClass();
        $body->tags = $tags;
        $res = $this->msC->put(self::URL_IDENTIFIER, $body, $id);
        if(!$res->status)
            return $res->addMessage("Невозможно обновить теги контрагента");
        else
            return $res;
    }
}