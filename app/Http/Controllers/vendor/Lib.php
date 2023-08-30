<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class Lib extends Controller
{

    private const UNKNOWN = 0;
    public const SETTINGS_REQUIRED = 1;
    private const ACTIVATED = 100;

    public mixed $accountId;
    public mixed $TokenMoySklad;
    public mixed $appId;

    public mixed $status = Lib::UNKNOWN;

    public function __construct($appId, $accountId)
    {
        $this->appId = $appId;
        $this->accountId = $accountId;
    }

    private static function cfg()
    {
        return (json_decode(json_encode(Config::get("Global"))) )->appId;
    }

    public function getStatusName(): ?string
   {
       return match ($this->status) {
           self::SETTINGS_REQUIRED => 'SettingsRequired',
           self::ACTIVATED => 'Activated',
           default => null,
       };
   }

    function persist(): void
    {
        @mkdir('data');
        file_put_contents($this->filename(), serialize($this));
    }

    private function filename(): string
    {
        return self::buildFilename($this->accountId);
    }

    private static function buildFilename($accountId): string
    {
        return "data/$accountId.json";
    }

    static function loadApp($accountId): Lib {
        return self::load(self::cfg()->appId, $accountId);
    }

    static function load($appId, $accountId): Lib {
        $data = @file_get_contents(self::buildFilename($accountId));
        if ($data === false) {
            $app = new Lib($appId, $accountId);
        } else {
            $unser = json_encode( unserialize($data) );
            $app =  json_decode($unser);
        }

        $AppInstance = new Lib($app->appId, $app->accountId);
        $AppInstance->parsing($app);

        return $AppInstance;
    }

    public function parsing($json): void
    {
        $this->accountId = $json->accountId;
        $this->TokenMoySklad = $json->TokenMoySklad;
        $this->appId = $json->appId;
    }


}
