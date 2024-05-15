<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Services\HandlerService;
use App\Services\ChatApp\AutomatizationService;
use App\Services\MoySklad\Entities\EmployeeService;
use Error;
use Exception;
use Illuminate\Http\Request;
use stdClass;

class sendTemplateController extends Controller{

    function sendTemplate(Request $request){
       // try{
            $req = $request->all();
            $handlerS = new HandlerService();

            $msUid = $req["auditContext"]["uid"];
            $spldUid = explode("@", $msUid);
            $startUid = $spldUid[0];

            $messageStack = [];
            $errors = [];

            foreach($req['events'] as $event){
                $type = $event['meta']['type'] ?? null;
                $href = $event['meta']['href'] ?? null;
                $accountId = $event['accountId'] ?? null;
                $employeeS = new EmployeeService($accountId);
                $obj = new stdClass();
                if($startUid != ""){
                    $employeeIdRes = $employeeS->getByUid($startUid);
                    if(!$employeeIdRes->status){

                        $obj->href = $href;
                        $obj->message = $employeeIdRes->message;
                        $errors[] = $obj;
                        continue;
                    }
                    $employeeId = $employeeIdRes->data;

                } else {
                    $obj->href = $href;
                    $obj->message = "по uid не был найден сотрудник";
                    $errors[] = $obj;
                    continue;

                }

                $stateHasChanged = in_array('state', $event['updatedFields']);
                $obj = new stdClass();
                if($stateHasChanged){
                    $autoS = new AutomatizationService($accountId);
                    $res = $autoS->sendTemplate($type, $href, $employeeId);
                    $obj->href = $href;
                    if (isset($res->message)) $obj->message = $res->message;
                    if (isset($res->data)) $obj->message = $res->data;
                    if(!$res->status)
                        $messageStack[] = $obj;
                    else
                        $errors[] = $obj;
                } else {
                    $obj->href = $href;
                    $obj->message = "Статус не был изменён";
                    $messageStack[] = $obj;
                }
            }
            if(empty($errors)){
                return response()->json($messageStack);
            } else {
                $mesAr = array_merge($messageStack, $errors);
                return response()->json($mesAr, 400);
            }
            if(empty($errors)){
                return response()->json($messageStack);
            } else {
                $mesAr = array_merge($messageStack, $errors);
                return response()->json($mesAr, 400);
            }
       /* } catch(Exception | Error $e){
            return response()->json($e->getMessage(), 500);
        }*/
    }


}
