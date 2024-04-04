<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\MoySklad;
use App\Services\MoySklad\CutLogicService;
use App\Services\MsFilterService;
use App\Services\Response;
use Exception;
use Illuminate\Support\Facades\Config;
use stdClass;

class EmployeeService {

    private MoySklad $msC;

    public string $accountId;

    private Response $res;

    private const URL_IDENTIFIER = "employee";

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
        $this->res = new Response();
        $this->accountId = $accountId;
    }

    function getByUid($startUid){
        $url = Config::get("Global")[self::URL_IDENTIFIER];
        $prepUrl = "{$url}?filter=uid~={$startUid}";
        $emplRes = $this->msC->getByUrl($prepUrl);
        if(!$emplRes->status)
            return $emplRes->addMessage("Ошибка при фильтрации по сотруднику");
        $employeeId = $emplRes->data->rows[0]->id ?? null;
        return $this->res->success($employeeId);
    }
}