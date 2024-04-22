<?php
namespace App\Services\MoySklad\Entities;

use App\Clients\oldMoySklad;
use App\Services\Response;
use Exception;
use Illuminate\Support\Facades\Config;
use stdClass;

class ProjectService {

    private oldMoySklad $msC;

    public string $accountId;

    private Response $res;

    private const URL_IDENTIFIER = "project";

    function __construct($accountId) {
        $this->msC = new oldMoySklad($accountId);
        $this->res = new Response();
        $this->accountId = $accountId;
    }

    function getAll(){
        $res = $this->msC->getAll(self::URL_IDENTIFIER);
        if(!$res->status)
            return $res->addMessage("Ошибка при получении всех проектов");
        else{
            $projects = $res->data->rows ?? null;
            if($projects === null)
                return $res->success([]);
            $projectsWithName = collect($projects)->pluck("name", "id")->toArray();
            return $this->res->success($projectsWithName);
        }
    }
}