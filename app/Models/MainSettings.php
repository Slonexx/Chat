<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainSettings extends Model
{
    protected $table = "main_settings";

    protected $guarded = [];

    public function templates()
    {
        return $this->hasMany(Templates::class);
    }

    public function attributes()
    {
        return $this->hasMany(AttributeSettings::class);
    }

    static function getGrouppedAttributes($accountId){
        return MainSettings::join('attribute_settings', "main_settings.id", "=", "attribute_settings.main_settings_id")
            ->where("account_id", $accountId)
            ->get()
            ->groupBy('entity_type') // Замените 'column_name' на название столбца, по которому нужно сгруппировать
            ->map(function ($group) {
                return $group->pluck('attribute_id', 'name'); // Выбираем столбец 'value' в качестве значения и столбец 'id' в качестве ключа
            })
            ->toArray();
    }

    public function chatappEmployees()
    {
        return $this->hasMany(ChatappEmployee::class);
    }
    
}
