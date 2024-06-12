<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendTemplateRequest;
use App\Jobs\JobWithDelay;
use App\Services\ChatApp\AutomatizationService;
use App\Services\MoySklad\Entities\EmployeeService;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use stdClass;

class sendTemplateController extends Controller{

    function handleWebhook(Request $request){
        try{
            $req = $request->all();

            $msUid = $req["auditContext"]["uid"];
            $spldUid = explode("@", $msUid);
            $startUid = $spldUid[0];

            $messageStack = [];
            $errors = [];
            $needToSendTemplate = [];
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
                $obj->href = $href;
                
                if($stateHasChanged){
                    $info = new stdClass();
                    $info->accountId = $accountId;
                    $info->type = $type;
                    $info->href = $href;
                    $info->employeeId = $employeeId;
                    $needToSendTemplate[] = $info;
                    //$obj->data = $res->data;
                    // if (isset($res->message)) $obj->message = $res->message;
                    // if (isset($res->data)) $obj->message = $res->data;
                    // if(!$res->status)
                    //     $errors[] = $obj;
                    // else
                    $messageStack[] = "Отправлено";
                } else {
                    $obj->data = "Статус не был изменён";
                    $messageStack[] = $obj;
                }
            }
            $params = [
                "headers" => [
                    'Content-Type' => 'application/json'
                ],
                "json" => $needToSendTemplate
            ];
            $appUrl = Config::get("Global.url", null);
            if (!is_string($appUrl) || $appUrl == null)
                throw new Error("url отсутствует или имеет некорректный формат");
            $preppedUrl = $appUrl . "api/automation/sendTemplate";
            $connection = "template";
            $timeObj = new stdClass();
            $timeObj->timeout = 1200;
            $timeObj->min_us = 20000;
            $timeObj->max_us = 500000;
            JobWithDelay::dispatch($params, $preppedUrl, $connection, $timeObj)->onConnection($connection)->onQueue("high");

            if(empty($errors)){
                return response()->json($messageStack);
            } else {
                $mesAr = array_merge($messageStack, $errors);
                return response()->json($mesAr);
            }
        } catch(Exception | Error $e){
            return response()->json($e->getMessage());
        }
    }

    function sendTemplate(SendTemplateRequest $req){
        $req->validated();
        $requestData = $req->all();
        /**
         * @var object[]
         */
        $requestObj = json_decode(json_encode($requestData));

        $sendedTemplates = [];
        foreach($requestObj as $objItem){
            $accountId = $objItem->accountId;
            $type = $objItem->type;
            $href = $objItem->href;
            $employeeId  = $objItem->employeeId;
            $autoS = new AutomatizationService($accountId);
            $sendedTemplates[] = $autoS->sendTemplate($type, $href, $employeeId);
        }
        return response()->json($sendedTemplates);
    }


}
