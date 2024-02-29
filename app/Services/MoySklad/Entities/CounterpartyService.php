<?php
namespace App\Services\Entity;

use App\Clients\MsClient;
use App\Models\OrderSettings;
use App\Services\HandlerService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;


class CounterpartyService {
    private MsClient $msClient;

    private string $accountId;

    private HandlerService $handlerS;

    private string $AgentUrlIdentifier = "agent";

    function __construct($accountId) {
        $this->msClient = new MsClient($accountId);
        $this->handlerS = new HandlerService();
        $this->accountId = $accountId;
    }

    function searchByCounterpartyForFilter($agentData) {
        
        $url = Config::get('Global')[$this->AgentUrlIdentifier];

        //$haveFirstName = property_exists($agentData, "firstName");
        //$haveLastName = property_exists($agentData, "lastName");
        //$haveCellPhone = property_exists($agentData, "cellPhone");

        //if($haveFirstName && $haveCellPhone) {
            $firstName = $agentData->firstName;
            $lastName = $agentData->lastName;
            $fullName = $agentData->fullName;
            $phone = $agentData->phone;
            $tag = $agentData->tag;
            $preparedUrl = "{$url}?filter=name~={$lastName};name=~{$firstName};phone=~{$phone}";
    
            $response = $this->msClient->getByUrl($preparedUrl);
            if(!$response->status){
                $response->message = "Произошла ошибка при поиске контрагента";
                return $response;
            }

            $rows = $response->data->rows;
            if(count($rows) == 0) {
                $response = $this->create($fullName, $phone, $tag);
                if(!$response->status){
                    $response->message = "Ошибка при создании контрагента";
                    return $response;
                }
                return $this->handlerS->createResponse(true, $response->data->meta);
            } else {
                return $this->handlerS->createResponse(true, $rows[0]->meta);
            }      

        // } else {
        //     return $this->handlerS->createResponse(false, "Отсутствует firstName и/или cellPhone");
        // }
    }

    function create($fullName, $phone, $tag){
        $body = (object) array(
            "name" => $fullName,
            "phone" => $phone,
            "companyType"=> "individual",
            "tags" => [$tag]
        );
        return $this->msClient->post($this->AgentUrlIdentifier, $body);
    }
}