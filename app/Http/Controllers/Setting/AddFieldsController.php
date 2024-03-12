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
use Exception;
use Illuminate\Http\Request;

class AddFieldsController extends Controller
{
    function getAddFields(Request $request, $accountId){
        try{
            $handlerS = new HandlerService();
            $isAdmin = $request->isAdmin;
            $fullName = $request->fullName ?? "Имя аккаунта";
            $uid = $request->uid ?? "логин аккаунта";

            $services = [
                "demand" => new DemandS($accountId),
                "counterparty" => new CounterpartyS($accountId),
                "customerorder" => new CustomorderS($accountId),
                "invoiceout" => new InvoiceoutS($accountId),
                "salesreturn" => new SalesreturnS($accountId),
            ];

            $addFieldsS = new AddFieldsService($accountId);

            $resAttrs = $addFieldsS->getAttrForEntities($services);

            if(!$resAttrs->status)
                return $handlerS->responseHandler($resAttrs);

            //1)check irrelevant
            $attrSet = MainSettings::getGrouppedAttributes($accountId);

            $msAttr = $resAttrs->data;

            $attrForDeleting = [];

            $attrSetAfterDeleting = [];

            foreach($attrSet as $entityType => $entityUuid){
                $attrForDeleting = array_filter($entityUuid, function ($item) use ($msAttr, $entityType){
                    $entityAttributes = $msAttr->$entityType;
                    $findedAttributesImMs = array_filter($entityAttributes, fn($value) => $value->id == $item);
                    return count($findedAttributesImMs) > 0 ? false : true;
                });
                $attrSetAfterDeleting[$entityType] = $entityUuid;
                if(count($attrForDeleting) > 0)
                    AttributeSettings::where("entity_type", $entityType)
                        ->whereIn("attribute_id", $attrForDeleting)
                        ->delete();
                foreach($attrForDeleting as $key => $item){
                    unset($attrSetAfterDeleting[$entityType][$key]);
                }
                
            }

            $attrForDeleting = [];
            $attrSet = [];

            $attributesWithoutFilled = (array) $msAttr;
            
            //2)delete filled attributes from all available attributes
            foreach($attrSetAfterDeleting as $entityType => $attributeArray){
                $attributesWithoutFilled[$entityType] = array_filter($msAttr->$entityType, fn($value)=> !in_array($value->id, $attributeArray));
                $keysToKeep = [ 'id', 'name' ];
                $cutLogicS = new CutLogicService();
                $cuttedAttrArray = $cutLogicS->cutArrayWithKeys($attributesWithoutFilled[$entityType], $keysToKeep);
                $attributesWithoutFilled[$entityType] = $cuttedAttrArray;
            }

            

            return view('setting.add_fields.add_fields', [
                'addFieldsWithValues' => $attrSetAfterDeleting,
                'attributesWithoutFilled' => $attributesWithoutFilled,
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

    function saveAddFields(Request $request, $accountId){
        try{
            $handlerS = new HandlerService();
            
        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }
}
