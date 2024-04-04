<?php
namespace App\Services\MoySklad;

use App\Clients\NCANodeClient;
use App\Clients\UdoClient;
use App\Models\Users;
use App\Services\Response;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Exception, Error;
use Illuminate\Support\Facades\Config;
use stdClass;

class FrontendLogicService{
    function prepareSelect($automationItem, $automationKey, $haystack){
        $autoChannel = $automationItem[$automationKey];
        $automationItem[$automationKey] = [];
        if($autoChannel !== null){
            $fChEqueal = array_filter($haystack, function($key) use ($autoChannel){
                return $key == $autoChannel;
            }, ARRAY_FILTER_USE_KEY);
            $fChNotEqueal = array_filter($haystack, function($key) use ($autoChannel){
                return $key != $autoChannel;
            }, ARRAY_FILTER_USE_KEY);
            $chValue = array_shift($fChEqueal);

            $obj = new stdClass();
            $obj->id = $autoChannel;
            $obj->name = $chValue;
            $obj->selected = true;

            $automationItem[$automationKey][] = $obj;

            foreach($fChNotEqueal as $key =>  $item){
                $obj = new stdClass();
                $obj->id = $key;
                $obj->name = $item;
                $obj->selected = false;
                $automationItem[$automationKey][] = $obj;
            }

        } else {
            $obj = new stdClass();
            $obj->id = null;
            $obj->name = "Не выбрано";
            $obj->selected = true;
            $automationItem[$automationKey][] = $obj;
            foreach($haystack as $key => $chItem){
                $obj = new stdClass();
                $obj->id = $key;
                $obj->name = $chItem;
                $obj->selected = false;
                $automationItem[$automationKey][] = $obj;
            }
        }
        return $automationItem[$automationKey];
    }
}