<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Templates extends Model
{
    protected $table = "templates";

    protected $guarded = [];

    public function attributes()
    {
        return $this->hasManyThrough(
            AttributeSettings::class,
            Variables::class,
            'template_id', // Внешний ключ в промежуточной таблице, который связан с основной моделью
            'id', // Локальный ключ в промежуточной таблице, который связан с моделью связанных сущностей
            'id', // Локальный ключ в основной модели
            'attribute_settings_id' // Внешний ключ в модели связанных сущностей
        );
    }
}
