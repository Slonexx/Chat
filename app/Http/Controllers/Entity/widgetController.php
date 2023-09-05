<?php

namespace App\Http\Controllers\Entity;

use App\Clients\MsClient;
use App\Http\Controllers\Controller;
use App\Http\Controllers\vendor\VendorApiController;
use App\Models\employeeModel;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class widgetController extends Controller
{
    public function widgetObject(Request $request, $object): Factory|View|Application
    {


        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($request->contextKey);

        if (isset($employee['errors'])) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => "Проблема с получением данных виджета, просьба срочно сообщить разработчиком ",
            ]);
        }

        $employeeModel = employeeModel::where('employeeId', $employee->id)->get();
        $accountId = $employee->accountId;
        $Client = new MsClient($accountId);

        try {
            $Client->get("https://online.moysklad.ru/api/remap/1.2/entity/employee");
        } catch (BadResponseException $e) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents()),
            ]);
        }

        return view('widget.object', [
            'accountId' => $accountId,
            'entity' => $object,
            'employee' => $employee,
        ]);


    }


    public function widgetInfoAttributes(Request $request): View|Factory|JsonResponse|Application
    {
        $ticket_id = null;

        $accountId = $request->accountId;
        $entity_type = $request->entity_type;
        $objectId = $request->objectId;

        $url = $this->getUrlEntity($entity_type, $objectId);
        $Setting = new getSettingVendorController($accountId);
        try {
            $Client = new MsClient($Setting->TokenMoySklad);
            $body = $Client->get($url);
        } catch (BadResponseException $e) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->errors[0]->error,
            ]);
        }

        try {
            $ClientWeb = new KassClient($accountId);
            $Total = $ClientWeb->ShiftHistory(0, 50)->Data->Total;
            sleep(1);
            $json = $ClientWeb->ShiftHistory($Total - 1, 50)->Data->Shifts[0];

            if (property_exists($json, 'CloseDate')) {
                $Close = true;
            } else $Close = false;

        } catch (BadResponseException $e) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message,
            ]);
        }

        if (property_exists($body, 'attributes')) {
            foreach ($body->attributes as $item) {
                if ($item->name == 'фискальный номер (WebKassa)') {
                    if ($item->value != null) $ticket_id = $item->value;
                    break;
                }
            }
        }
        return response()->json(['ticket_id' => $ticket_id, 'Close' => $Close]);
    }


    public function LOG_widgetInfoAttributes(Request $request): View|Factory|JsonResponse|Application
    {
        $ticket_id = null;

        $accountId = $request->accountId;
        $entity_type = $request->entity_type;
        $objectId = $request->objectId;

        $url = $this->getUrlEntity($entity_type, $objectId);
        $Setting = new getSettingVendorController($accountId);
        try {
            $Client = new MsClient($Setting->TokenMoySklad);
            $body = $Client->get($url);
        } catch (BadResponseException $e) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->errors[0]->error,
            ]);
        }

        try {
            $ClientWeb = new KassClient($accountId);
            $Total = $ClientWeb->ShiftHistory(0, 50)->Data->Total;
            sleep(1);
            $json = $ClientWeb->ShiftHistory($Total - 1, 50)->Data->Shifts[0];

            if (property_exists($json, 'CloseDate')) {
                $Close = true;
            } else $Close = false;

        } catch (BadResponseException $e) {
            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message,
            ]);
        }

        if (property_exists($body, 'attributes')) {
            foreach ($body->attributes as $item) {
                if ($item->name == 'фискальный номер (WebKassa)') {
                    if ($item->value != null) $ticket_id = $item->value;
                    break;
                }
            }
        }
        return response()->json(['ticket_id' => $ticket_id, 'Close' => $Close]);
    }


    private function getUrlEntity($enType, $enId): ?string
    {
        return match ($enType) {
            "customerorder" => "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/" . $enId,
            "demand" => "https://online.moysklad.ru/api/remap/1.2/entity/demand/" . $enId,
            "salesreturn" => "https://online.moysklad.ru/api/remap/1.2/entity/salesreturn/" . $enId,
            default => null,
        };
    }
}
