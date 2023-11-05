<?php

namespace App\Http\Controllers\Entity;

use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Models\polesModel;
use App\Models\templateModel;
use App\Services\ticket\DevService;
use App\Services\ticket\TicketService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

class PopapController extends Controller
{
    public function Popup($object): Factory|View|Application
    {
        return view( 'popup.ViewPopap', ['Entity' => $object,] );
    }

    public function template(): Factory|View|Application
    {
        return view( 'popup.template' );
    }
    public function getTemplate(Request $request)
    {
        $data = json_decode(json_encode([
            'accountId' => $request->accountId ?? "",
            'object_Id' => $request->object_Id ?? "",
            'entity_type' => $request->entity_type ?? "",
        ]));

        $client = new MsClient($data->accountId);
        try {
            $entity = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $data->entity_type . '/' . $data->object_Id);
        } catch (BadResponseException $e){
            return response()->json([
                'status' => true,
                'data' => $e->getMessage(),
            ]);
        }

        $res = [];
        try {
            $model = templateModel::where('accountId', $data->accountId)->get();
            if (!$model->isEmpty()) {
                foreach ($model as $item) {
                    $array = $item->toArray();

                    $polesModel = polesModel::where('accountId', $data->accountId)->where('name_uid', ($item->toArray())['name_uid'])->get();
                    if (!$polesModel->isEmpty()) {
                        foreach ($polesModel as $polesModelItem) {
                            $polesModelItemToArray = $polesModelItem->toArray();
                            $array['message'] = $this->messageUpdate($client, $entity, $array['message'], $polesModelItemToArray['i'], $polesModelItemToArray['pole'], $polesModelItemToArray['add_pole']);
                        }
                    }
                    $res[] = $array;
                }
            }
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => true,
                'data' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $res,
        ]);

    }


    function messageUpdate(MsClient $client, mixed $entity, string $message, $index, $pole, $add_pole): string
    {
        $word_pole_index = 'поле_'.$index;
        $word_add_pole_index = 'доп_поле_'.$index;
        $text_pole = '';
        $text_add_pole = '';

        if ($pole != null) {
            switch ($index) {
                case '0': {
                    $text_pole = $entity->name;
                    break;
                }
                case '1': {
                    try {
                        $metaGet = $client->get($entity->organization->meta->href);
                        $text_pole = $metaGet->name;
                    } catch (BadResponseException){ }
                    break;
                }

                case '2': {
                    if (property_exists($entity, 'deliveryPlannedMoment')) { $text_pole = substr($entity->deliveryPlannedMoment, 0, -5); }
                    break;
                }

                case '3': {
                    if (property_exists($entity, 'salesChannel')) {
                        try {
                            $metaGet = $client->get($entity->salesChannel->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException){ }
                    }
                    break;
                }

                case '4': {
                    if (property_exists($entity, 'rate')) {
                        try {
                            $metaGet = $client->get($entity->rate->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException){ }
                    }
                    break;
                }

                case '5': {
                    if (property_exists($entity, 'store')) {
                        try {
                            $metaGet = $client->get($entity->store->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException){ }
                    }
                    break;
                }

                case '6': {
                    if (property_exists($entity, 'contract')) {
                        try {
                            $metaGet = $client->get($entity->contract->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException){ }
                    }
                    break;
                }

                case '7': {
                    if (property_exists($entity, 'project')) {
                        try {
                            $metaGet = $client->get($entity->project->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException){ }
                    }
                    break;
                }

                case '8': {
                    if (property_exists($entity, 'shipmentAddress')) { $text_pole = $entity->shipmentAddress; }
                    break;
                }

                case '9': {
                    if (property_exists($entity, 'description')) { $text_pole = $entity->description; }
                    break;
                }

                case '10': {
                    if (property_exists($entity, 'state')) {
                        try {
                            $metaGet = $client->get($entity->state->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException){ }
                    }
                    break;
                }

                case '11': {
                    if (property_exists($entity, 'sum')) {  $text_pole = $entity->sum / 100; }
                    break;
                }

                case '12': {
                    if (property_exists($entity, 'agent')) {
                        try {
                            $metaGet = $client->get($entity->agent->meta->href);
                            $text_pole = $metaGet->name;
                        } catch (BadResponseException){ }
                    }
                    break;
                }

                default: break;
            }
        }

        if ($add_pole != null) {
            if (property_exists($entity, 'attributes')) {
                foreach ($entity->attributes as $item) {
                    if ($item->id == $add_pole) {

                        if (is_bool($item->value)) {
                            if ($item) { $text_add_pole = "активно"; } else { $text_add_pole = "не активно"; }
                        } else {
                            $text_add_pole = $item->value;
                        }
                    }
                }

            }
        }

        // Разбиваем строку по пробелам, запятам и другим знакам препинания
        $words = preg_split('/([\s,]+)/', $message, -1, PREG_SPLIT_DELIM_CAPTURE);

        //dd($words);

        foreach ($words as &$word) {
            if ($word == $word_pole_index) {
                $word = $text_pole;
            } elseif ($word == $word_add_pole_index) {
                $word = $text_add_pole;
            }
        }


        $new_message = implode('', $words);

        return $new_message;
    }

}
