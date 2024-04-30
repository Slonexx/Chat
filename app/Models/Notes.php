<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notes extends Model
{
    protected $table = "notes";

    protected $guarded = [];

    static function getByAccountId($accountId){
        return self::where("accountId", $accountId)->get(["is_messenger", "last_start"]);
    }
}
