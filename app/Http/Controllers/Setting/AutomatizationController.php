<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Models\AttributeSettings;
use App\Models\MainSettings;
use App\Models\TemplateAutoSettings;
use App\Services\HandlerService;
use App\Services\MoySklad\Attributes\DemandS;
use App\Services\MoySklad\AddFieldsService;
use App\Services\MoySklad\Attributes\CounterpartyS;
use App\Services\MoySklad\Attributes\CustomorderS;
use App\Services\MoySklad\Attributes\InvoiceoutS;
use App\Services\MoySklad\Attributes\SalesreturnS;
use App\Services\MoySklad\CutLogicService;
use App\Services\MoySklad\Entities\CounterpartyService;
use App\Services\MoySklad\Entities\CustomOrderService;
use App\Services\MoySklad\Entities\DemandService;
use App\Services\MoySklad\Entities\InvoiceoutService;
use App\Services\MoySklad\Entities\ProjectService;
use App\Services\MoySklad\Entities\SalesChannelService;
use App\Services\MoySklad\Entities\SalesReturnService;
use App\Services\Response;
use Exception;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use stdClass;

class AutomatizationController extends Controller{
    function getPage(Request $request, $accountId){
        try{
            $handlerS = new HandlerService();
            $isAdmin = $request->isAdmin;
            $fullName = $request->fullName ?? "Имя аккаунта";
            $uid = $request->uid ?? "логин аккаунта";

            $savedAuto = MainSettings::join('chatapp_employees as e', 'e.main_settings_id', "=", "main_settings.id")
            ->join('template_auto_settings as auto_s', 'auto_s.employee_id', "=", "e.id")
            ->where("account_id", $accountId)
            ->select(
                "auto_s.uuid",
                "channel", 
                "project", 
                "status",
                "entity"
                )
            ->get()
            ->toArray();

            $statuses = [];
            $entityServices = [
                "demand" => new DemandService($accountId),
                "customerorder" => new CustomOrderService($accountId),
                "invoiceout" => new InvoiceoutService($accountId),
                "salesreturn" => new SalesReturnService($accountId),
            ];

            foreach($entityServices as $entityType => $service){
                $statusesRes = $service->getStatuses();
                if(!$statusesRes->status){
                    return $handlerS->responseHandler($statusesRes, true, false);       
                }
                $statuses[$entityType] = $statusesRes->data;
            }

            $prepStatuses = [];
            foreach($statuses as $entityType => $states){
                $autos = array_filter($savedAuto, fn($value) => $value["entity"] == $entityType);
                foreach($states as $state){
                    $selects = [];
                    // array_filter($savedAuto, fn($value) => $value["entity"] == $entityType
                    //         && $value["status"] == $state->id);
                    if(count($autos) == 0){
                        foreach($states as $state){
                            $obj = new stdClass();
                            $obj->id = $state->id;
                            $obj->name = $state->name;
                            $obj->selected = false;
                            $prepStatuses[$entityType][] = $obj;
                        }
                        $obj = new stdClass();
                        $obj->id = null;
                        $obj->name = "Не выбрано";
                        $obj->selected = true;
                        $prepStatuses[$entityType][] = $obj;
                        break;
                    } else {

                        for($i = 0; $i < count($autos); $i++){
                            $select = [];
                            $obj = new stdClass();
                            $obj->id = $state->id;
                            $obj->name = $state->name;
                            $obj->selected = true;
                            $select[] = $obj;
                            //$prepStatuses[$entityType][$i][] = $obj;
                            $f = array_filter($states, fn($value) => $value->id != $state->id);
                            foreach($f as $unmarked){
                                $obj = new stdClass();
                                $obj->id = $unmarked->id;
                                $obj->name = $unmarked->name;
                                $obj->selected = false;
                                $select[] = $obj;
                            }
                            $selects = $select;
                        }
                        $prepStatuses[$entityType][] = $selects;
                    }
                }
            }

            // $autoWithStatus = array_map(function ($item) use ($statuses){

            // }, $savedAuto);

            foreach($savedAuto as $autoItem){

            }

            $projectS = new ProjectService($accountId);
            $allProjRes = $projectS->getAll();
            if(!$allProjRes->status){
                return $handlerS->responseHandler($allProjRes, true, false);
            }

            $salesChannelS = new SalesChannelService($accountId);
            $allSalesChanRes = $salesChannelS->getAll();
            if(!$allSalesChanRes->status){
                return $handlerS->responseHandler($allSalesChanRes, true, false);
            }
            $saleschannel = $allSalesChanRes->data;
            $project = $allProjRes->data;


            //$uniqueEntities = collect($savedAuto)->pluck("entity")->unique()->values()->all();
            // foreach($statuses as $entityType =>$item){
            //     if(count($item)){
            //         $needDelete = MainSettings::join('chatapp_employees as e', 'e.main_settings_id', "=", "main_settings.id")
            //         ->join('template_auto_settings as auto_s', 'auto_s.employee_id', "=", "e.id")
            //         ->where("account_id", $accountId)
            //         ->where("entity", $entityType)
            //         ->select("auto_s.uuid")
            //         ->get()
            //         ->all();
            //         foreach($needDelete as $item){
            //             TemplateAutoSettings::where("uuid", $item->uuid)->get()->first()->delete();
            //         }

            //     } else
                
                
            // }

            $autoWithStatus = array_map(function ($item) use ($statuses, $saleschannel, $project){
                $status = array_filter($statuses[$item['entity']], function($key) use ($item){
                    return $key == $item['status'];
                }, ARRAY_FILTER_USE_KEY);
                $stKey = array_shift($status);
                $item['status'] = $stKey;

                if($item['channel'] !== null){
                    $channel = array_filter($saleschannel, function($key) use ($item){
                        return $key == $item['channel'];
                    }, ARRAY_FILTER_USE_KEY);
                    $chKey = array_shift($channel);
                    $item['channel'] = $chKey;

                }

                // if($item['project'] !== null){
                //     $project = array_filter($statuses[$item['project']], function($key) use ($item){
                //         return $key == $item['project'];
                //     }, ARRAY_FILTER_USE_KEY);
                //     $projKey = array_shift($project);
                //     $item['project'] = $projKey;

                // }


                return $item;
            }, (array) $savedAuto);


            return view('setting.automatization.main', [
                'savedAuto' => $autoWithStatus,
                // 'template' => $templates,
                // 'message' => $request->message ?? '',
    
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'fullName' => $fullName,
                'uid' => $uid,
            ]);
            //->groupBy('entity')


            // TemplateAutoSettings::where("account_id", $accountId)
            // ->get()
            // ->groupBy('entity')
            // ->toArray();

            // $handlerS = new HandlerService();
            // $attrSet = MainSettings::getGrouppedAttributes($accountId);

            // $availableS = [
            //     "demand" => new DemandS($accountId),
            //     "counterparty" => new CounterpartyS($accountId),
            //     "customerorder" => new CustomorderS($accountId),
            //     "invoiceout" => new InvoiceoutS($accountId),
            //     "salesreturn" => new SalesreturnS($accountId),
            // ];

            
            // $entititesForRequest = [];
            // $services = [];
            
            // foreach($attrSet as $entityType => $array){
            //     $services[$entityType] = $availableS[$entityType];
            // }
            
            // $addFieldsS = new AddFieldsService($accountId);
            // $resAttrs = $addFieldsS->getAttrForEntities($services);

            // if(!$resAttrs->status)
            //     return $handlerS->responseHandler($resAttrs, true, false);

            // $findedInMs = [];

            // foreach((array)$resAttrs->data as $entityType => $array){
            //     $findedInMs[$entityType] = array_filter(
            //         $array, 
            //         fn($val)=> in_array($val->id, $attrSet[$entityType]
            //     ));
            // }

            // $resultArrayWithEntites = [];

            // foreach($findedInMs as $entityType => $arrayAttr){
            //     $attrSet[$entityType] = array_map(function($value) use ($arrayAttr, $attrSet, $entityType){
            //         $attributeName = array_filter($arrayAttr, fn($valueF) => $valueF->id == $value);
            //         // $resultArray = [];
            //         // foreach($array as $key => $uuid){
            //         //     $resultArray[$key] = $currentAttribute[0]->name;
            //         // }
            //         return array_shift($attributeName)->name;
            //     },$attrSet[$entityType]);
            //     $resultArrayWithEntites[$entityType] = $attrSet[$entityType];
            // }

            // $res = new stdClass();
            // $res->message = '';
            // $res->data = $resultArrayWithEntites;
            // return response()->json($res);


        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }
}