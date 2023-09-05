<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class getSettingVendorController extends Controller
{
    var $appId;
    var $accountId;
    var $TokenMoySklad;


    public function __construct($accountId)
    {

        $json = Lib::loadApp((json_decode(json_encode(Config::get("Global"))) )->appId, $accountId);

        $this->appId = $json->appId;
        $this->accountId = $json->accountId;
        $this->TokenMoySklad = $json->TokenMoySklad;


        return $json;

    }



}
