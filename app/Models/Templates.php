<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Templates extends Model
{
    protected $table = "templates";

    protected $guarded = [];

    public static function getAllMainsTemplates($accountId): object
    {
        $id = MainSettings::where('account_id',  $accountId )->get()->first();

        if ($id != null) $id = $id->toArray();
        else  return (object) [ 'query' => null, 'toArray' => null, ];

        $model = Templates::where('main_settings_id',  $id['id'] )->get();
        if (!$model->isEmpty()) {
            $return = [];
            foreach ($model as $item) {
                $return[] = $item->toArray();
            }
            return (object) [
                'query' => $model,
                'toArray' => $return,
            ];
        } else {
            return (object) [
                'query' => $model,
                'toArray' => null,
            ];
        }
    }

    public static function getByIdOrNull($id){
        $model = Templates::find($id);

        if ($model != null){
            return (object) ['model' => $model, 'toArray' => $model->toArray()];
        } else (object) ['model' => null, 'toArray' => null];
    }
    public static function GetIsUuid($id, $accountId){
        $model = Templates::where('uuid', $id)->get()->first();

        if ($model != null){
            return (object) ['model' => $model, 'toArray' => $model->toArray()];
        } else (object) ['model' => null, 'toArray' => null];
    }


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

    public function scenario()
    {
        return $this->hasOne(Scenario::class, 'template_id');
    }

    public function getTemplate()
    {
        return $this->belongsTo(Templates::class, 'template_id');
    }
}
