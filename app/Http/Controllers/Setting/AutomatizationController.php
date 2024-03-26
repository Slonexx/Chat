<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Models\AttributeSettings;
use App\Models\MainSettings;
use App\Services\HandlerService;
use App\Services\MoySklad\Attributes\DemandS;
use App\Services\MoySklad\AddFieldsService;
use App\Services\MoySklad\Attributes\CounterpartyS;
use App\Services\MoySklad\Attributes\CustomorderS;
use App\Services\MoySklad\Attributes\InvoiceoutS;
use App\Services\MoySklad\Attributes\SalesreturnS;
use App\Services\MoySklad\CutLogicService;
use App\Services\Response;
use Exception;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Request;
use stdClass;

class AutomatizationController extends Controller{
    function getPage(Request $request, $accountId){
        try{
            $isAdmin = $request->isAdmin;
            $fullName = $request->fullName ?? "Имя аккаунта";
            $uid = $request->uid ?? "логин аккаунта";
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

            return view('setting.automatization.main', [
                // 'saveOrgan' => $saveOrgan,
                // 'template' => $templates,
                // 'message' => $request->message ?? '',
    
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'fullName' => $fullName,
                'uid' => $uid,
            ]);

        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }
}