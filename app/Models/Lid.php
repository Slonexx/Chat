<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lid extends Model
{
    protected $table = "lids";

    protected $guarded = [];

    public static function getFirstByAccountId($accountId){
        return self::where("accountId", $accountId)->get()->first();
    }
}
