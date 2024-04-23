<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Models\AttributeSettings;
use App\Models\MainSettings;
use App\Services\HandlerService;
use App\Services\MoySklad\Attributes\DemandS;
use App\Services\MoySklad\AddFieldsService;
use App\Services\MoySklad\Attributes\oldCounterpartyS;
use App\Services\MoySklad\Attributes\CustomorderS;
use App\Services\MoySklad\Attributes\InvoiceoutS;
use App\Services\MoySklad\Attributes\SalesreturnS;
use App\Services\MoySklad\CutLogicService;
use App\Services\Response;
use Exception;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Request;
use stdClass;

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
                "counterparty" => new oldCounterpartyS($accountId),
                "customerorder" => new CustomorderS($accountId),
                "invoiceout" => new InvoiceoutS($accountId),
                "salesreturn" => new SalesreturnS($accountId),
            ];

            $addFieldsS = new AddFieldsService($accountId);

            $resAttrs = $addFieldsS->getAttrForEntities($services);

            if(!$resAttrs->status)
                return $handlerS->responseHandler($resAttrs, true, false);

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

    function getFilledAddFields(Request $request, $accountId){
        try{
            $handlerS = new HandlerService();

            $services = [
                "demand" => new DemandS($accountId),
                "counterparty" => new oldCounterpartyS($accountId),
                "customerorder" => new CustomorderS($accountId),
                "invoiceout" => new InvoiceoutS($accountId),
                "salesreturn" => new SalesreturnS($accountId),
            ];

            $addFieldsS = new AddFieldsService($accountId);

            $resAttrs = $addFieldsS->getAttrForEntities($services);

            if(!$resAttrs->status)
                return $handlerS->responseHandler($resAttrs, true, false);

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

            $res = new stdClass();
            $res->message = '';
            $res->data = $attrSetAfterDeleting;
            return response()->json($res);

            

        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    function saveAddField(Request $request, $accountId){
        try{
            $res = new Response();
            $handlerS = new HandlerService();
            $setting = MainSettings::where('account_id', $accountId)->get();

            if($setting->isEmpty()){
                $er = $res->error($setting, 'Настройки по данному accountId не найдены');
                return $handlerS->responseHandler($er, true, false);
            }

            $entity_type = $request->entityType ?? false;
            $name = $request->name ?? false;
            $attribute_id = $request->uuid ?? false;

            if(empty($entity_type) || empty($name) || empty($attribute_id)){
                $er = $res->error(
                    [
                        $entity_type,
                        $name,
                        $attribute_id
                    ], 
                "Один или несколько параметров пусты");
                return $handlerS->responseHandler($er, true, false);
            }

            //добавить запрос на мой склад с проверкой на наличие такого доп.поля
            $attribute = $setting->first()->attributes()->create([
                "entity_type" => $entity_type,
                "name" => $name,
                "attribute_id" => $attribute_id,
            ]);

            $res = new stdClass();
            $res->message = 'Успешно создано';
            $res->data = new stdClass();
            $res->data->msUuid = $attribute->attribute_id;
            return response()->json($res);


            //чисто теоретически клиент Б может добавить доп.поле клиенту А зная его UUID в моём складе. 
            //Но так как клиенту выдаются только его доп.поля это не представляется возможным
            //AttributeSettings::where("attribute_id", )

            // // Предположим, у вас есть модель Role с полем uuid
            // $roleUuids = ['uuid1', 'uuid2', 'uuid3']; // UUID ролей, которые вы хотите добавить к пользователю

            // $rolesIds = Role::whereIn('uuid', $roleUuids)->pluck('id')->toArray();

            // $user->roles()->attach($rolesIds);
            
        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }
    
    function deleteAddField($accountId, $uuid){
        try{
            $res = new Response();
            $setting = MainSettings::where("account_id", $accountId)->get();

            if($setting->isEmpty()){
                $er = $res->error($setting, 'Настройки по данному accountId не найдены');
                return response()->json($er);
            }
            $setting->first()
                ->attributes()
                ->where("attribute_id", $uuid)
                ->delete();

            $res = new stdClass();
            $res->message = 'Успешно удалено';
            $res->data = '';
            return response()->json($res);
        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }
}
