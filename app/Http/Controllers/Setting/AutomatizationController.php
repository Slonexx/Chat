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
            
            $automation = [];
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

            ////////////STATUSES////////////
            //$prepStatuses = [];
            foreach($statuses as $entityType => $states){
                $autos = array_filter($savedAuto, fn($value) => $value["entity"] == $entityType);
                foreach($states as $state){
                    if(count($autos) == 0)
                        break;
                    $fA = array_filter($autos, fn($value) => $value['status'] == $state->id);
                    $shftedAuto = array_shift($fA);

                    $fSEqueal = array_filter($states, fn($value) => $value->id == $shftedAuto['status']);
                    $fSNotEqueal = array_filter($states, fn($value) => $value->id != $shftedAuto['status']);

                    $stateEqual = array_shift($fSEqueal);

                    $obj = new stdClass();
                    $obj->id = $stateEqual->id;
                    $obj->name = $stateEqual->name;
                    $obj->selected = true;

                    $shftedAuto['status'] = [];
                    $shftedAuto['status'][] = $obj;

                    foreach($fSNotEqueal as $state){
                        $obj = new stdClass();
                        $obj->id = $state->id;
                        $obj->name = $state->name;
                        $obj->selected = false;
                        $shftedAuto['status'][] = $obj;
                    }
                    $automation[] = $shftedAuto;
                    // $selects = [];
                    // if(count($autos) == 0){
                    //     foreach($states as $state){
                    //         $obj = new stdClass();
                    //         $obj->id = $state->id;
                    //         $obj->name = $state->name;
                    //         $obj->selected = false;
                    //         $prepStatuses[$entityType][] = $obj;
                    //     }
                    //     $obj = new stdClass();
                    //     $obj->id = null;
                    //     $obj->name = "Не выбрано";
                    //     $obj->selected = true;
                    //     $prepStatuses[$entityType][] = $obj;
                    //     break;
                    // } else {
                        


                        // for($i = 0; $i < count($autos); $i++){
                        //     $select = [];
                            
                        //     $obj = new stdClass();
                        //     $obj->id = $state->id;
                        //     $obj->name = $state->name;
                        //     $obj->selected = true;
                        //     $select[] = $obj;
                        //     //$prepStatuses[$entityType][$i][] = $obj;
                        //     // foreach($f as $unmarked){
                        //     //     $obj = new stdClass();
                        //     //     $obj->id = $unmarked->id;
                        //     //     $obj->name = $unmarked->name;
                        //     //     $obj->selected = false;
                        //     //     $select[] = $obj;
                        //     // }
                        //     // $selects = $select;
                        // }
                        // $prepStatuses[$entityType][] = $selects;
                    //}
                }

            }
            ////////////STATUSES////////////

            $availableEntities = array_keys($entityServices);


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

            for($i = 0; $i< count($automation); $i++){
                ////////////ENTITIES////////////
                $currentEntity = $automation[$i]['entity'];
                $obj = new stdClass();
                $obj->name = $currentEntity;
                $obj->selected = true;
                $automation[$i]['entity'] = [];
                $automation[$i]['entity'][] = $obj;

                $fENotEqueal = array_filter($availableEntities, fn($value) => $value != $currentEntity);
                foreach($fENotEqueal as $item){
                    $obj = new stdClass();
                    $obj->name = $item;
                    $obj->selected = false;
                    $automation[$i]['entity'][] = $obj;
                }
                ////////////ENTITIES////////////

                ////////////CHANNEL////////////
                $autoChannel = $automation[$i]['channel'];
                $automation[$i]['channel'] = [];
                if($autoChannel !== null){
                    $fChEqueal = array_filter($saleschannel, function($key) use ($autoChannel){
                        return $key == $autoChannel;
                    }, ARRAY_FILTER_USE_KEY);
                    $fChNotEqueal = array_filter($saleschannel, function($key) use ($autoChannel){
                        return $key != $autoChannel;
                    }, ARRAY_FILTER_USE_KEY);
                    $chValue = array_shift($fChEqueal);

                    $obj = new stdClass();
                    $obj->id = $autoChannel;
                    $obj->name = $chValue;
                    $obj->selected = true;

                    $automation[$i]['channel'][] = $obj;

                    foreach($fChNotEqueal as $key =>  $item){
                        $obj = new stdClass();
                        $obj->id = $key;
                        $obj->name = $item;
                        $obj->selected = false;
                        $automation[$i]['channel'][] = $obj;
                    }

                } else {
                    $obj = new stdClass();
                    $obj->id = null;
                    $obj->name = "Не выбрано";
                    $obj->selected = true;
                    $automation[$i]['channel'][] = $obj;
                    foreach($saleschannel as $key => $chItem){
                        $obj = new stdClass();
                        $obj->id = $key;
                        $obj->name = $chItem;
                        $obj->selected = false;
                        $automation[$i]['channel'][] = $obj;
                    }
                }
                ////////////CHANNEL////////////

                ////////////PROJECT////////////
                $autoProject = $automation[$i]['project'];
                $automation[$i]['project'] = [];
                if($autoProject !== null){
                    $fPEqueal = array_filter($project, function($key) use ($autoProject){
                        return $key == $autoProject;
                    }, ARRAY_FILTER_USE_KEY);
                    $fPNotEqueal = array_filter($project, function($key) use ($autoProject){
                        return $key != $autoProject;
                    }, ARRAY_FILTER_USE_KEY);
                    $pValue = array_shift($fPEqueal);

                    $obj = new stdClass();
                    $obj->id = $autoProject;
                    $obj->name = $pValue;
                    $obj->selected = true;

                    $automation[$i]['project'][] = $obj;

                    foreach($fPNotEqueal as $key =>  $item){
                        $obj = new stdClass();
                        $obj->id = $key;
                        $obj->name = $item;
                        $obj->selected = false;
                        $automation[$i]['project'][] = $obj;
                    }

                } else {
                    $obj = new stdClass();
                    $obj->id = null;
                    $obj->name = "Не выбрано";
                    $obj->selected = true;
                    $automation[$i]['project'][] = $obj;
                    foreach($project as $key => $chItem){
                        $obj = new stdClass();
                        $obj->id = $key;
                        $obj->name = $chItem;
                        $obj->selected = false;
                        $automation[$i]['project'][] = $obj;
                    }
                }
                ////////////PROJECT////////////
                
            }

            return view('setting.automatization.main', [
                'savedAuto' => $automation,
                // 'template' => $templates,
                // 'message' => $request->message ?? '',
    
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'fullName' => $fullName,
                'uid' => $uid,
            ]);
            

            // array_map(function ($item) use ($statuses, $saleschannel, $project){
            //     $status = array_filter($statuses[$item['entity']], function($key) use ($item){
            //         return $key == $item['status'];
            //     }, ARRAY_FILTER_USE_KEY);
            //     $stKey = array_shift($status);
            //     $item['status'] = $stKey;

            //     if($item['channel'] !== null){
            //         $channel = array_filter($saleschannel, function($key) use ($item){
            //             return $key == $item['channel'];
            //         }, ARRAY_FILTER_USE_KEY);
            //         $chKey = array_shift($channel);
            //         $item['channel'] = $chKey;

            //     }

            //     // if($item['project'] !== null){
            //     //     $project = array_filter($statuses[$item['project']], function($key) use ($item){
            //     //         return $key == $item['project'];
            //     //     }, ARRAY_FILTER_USE_KEY);
            //     //     $projKey = array_shift($project);
            //     //     $item['project'] = $projKey;

            //     // }


            //     return $item;
            // }, (array) $savedAuto);


            // array_map(function ($item) use ($availableEntities){
            //     $obj = new stdClass();
            //     $obj->name = $item->entity;
            //     $obj->selected = true;

            //     $shftedAuto['status'] = [];
            //     $shftedAuto['status'][] = $obj;
            
            //     $fENotEqueal = array_filter($availableEntities, fn($value) => $value != $item->entity);
            //     return $item;
            // }, $automation);


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