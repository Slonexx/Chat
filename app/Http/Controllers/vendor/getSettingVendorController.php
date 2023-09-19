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
    public string $appId;
    public string $accountId;
    public string $TokenMoySklad;
    public mixed $status;


    public function __construct($accountId)
    {

        $json = Lib::loadApp($accountId);


        $this->appId = $json->appId;
        $this->accountId = $json->accountId;
        $this->TokenMoySklad = $json->TokenMoySklad;
        $this->status = $json->status;


        return $json;

    }



}
