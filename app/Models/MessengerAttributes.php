<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessengerAttributes extends Model
{   
    protected $table = "messenger_attributes";

    protected $guarded = [];

    function mainSetting()
    {
        return $this->belongsTo(MainSettings::class);
    }

    static function getFirst($accountId, $entityType, $name){
        return self::join('main_settings as m_s', 'messenger_attributes.main_settings_id', '=', 'm_s.id')
        ->where("account_id", $accountId)
        ->where("entity_type", $entityType)
        ->where("name", $name)
        ->select("messenger_attributes.id as id", "attribute_id")
        ->get()
        ->first();
    }
}
