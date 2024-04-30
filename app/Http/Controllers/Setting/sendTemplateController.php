<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Services\HandlerService;
use App\Services\ChatApp\AutomatizationService;
use App\Services\MoySklad\Entities\EmployeeService;
use Error;
use Exception;
use Illuminate\Http\Request;

class sendTemplateController extends Controller{

    public function sendTemplate(Request $request){
        try{
            $req = $request->all();
            $handlerS = new HandlerService();

            $msUid = $req["auditContext"]["uid"];
            $spldUid = explode("@", $msUid);
            $startUid = $spldUid[0];

            foreach($req['events'] as $event){
                $type = $event['meta']['type'] ?? null;
                $href = $event['meta']['href'] ?? null;
                $accountId = $event['accountId'] ?? null;
                $employeeS = new EmployeeService($accountId);
                $employeeId = null;
                if($startUid != ""){
                    $employeeIdRes = $employeeS->getByUid($startUid);
                    if(!$employeeIdRes->status){
                        return $handlerS->responseHandler($employeeIdRes, true, false);
                    }

                }

                $stateHasChanged = in_array('state', $event['updatedFields']);
                if($stateHasChanged){
                    $autoS = new AutomatizationService($accountId);
                    $res = $autoS->sendTemplate($type, $href, $employeeIdRes->data);
                    return $handlerS->responseHandler($res, true, false);
                } else {
                    return response()->json();
                }
            }
        } catch(Exception | Error $e){
            return response()->json($e->getMessage(), 500);
        }
    }


}
