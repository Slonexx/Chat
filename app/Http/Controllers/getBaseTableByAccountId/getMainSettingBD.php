<?php

namespace App\Http\Controllers\getBaseTableByAccountId;

use App\Http\Controllers\Controller;
use App\Models\settingModel;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class getMainSettingBD extends Controller
{
    public mixed $accountId;
    public mixed $tokenMs;
    public mixed $accessToken;


    /**
     * @param $accountId
     */
    public function __construct($accountId)
    {
        $this->accountId = $accountId;

        $find = settingModel::query()->where('accountId', $accountId)->first();
        if ($find) {
            $result = $find->getAttributes();
            $this->accountId = $result['accountId'];
            $this->tokenMs = $result['tokenMs'];
        } else {
            $this->accountId = $accountId;
            $this->tokenMs = null;
        }



    }


}
