<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeSettings extends Model
{
    protected $table = "attribute_settings";

    protected $guarded = [];
    
    public function variables()
    {
        return $this->hasManyThrough(
            Templates::class,
            Variables::class,
            'main_settings_id', // Внешний ключ в промежуточной таблице, который связан с основной моделью
            'id', // Локальный ключ в промежуточной таблице, который связан с моделью связанных сущностей
            'id', // Локальный ключ в основной модели
            'attribute_settings_id' // Внешний ключ в модели связанных сущностей
        );
    }
}
