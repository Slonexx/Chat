<?php

namespace App\Models;


use Exception;
use Illuminate\Database\Eloquent\Model;

class Scenario extends Model
{
    protected $table = "scenario";

    public $incrementing = false;

    protected $guarded = [];

    public static function getInformationALLAcc($accountId): object
    {

        $model = Scenario::where('accountId',  $accountId )->get();
        if (!$model->isEmpty()) {
            $return = [];
            foreach ($model as $item) {
                $items = $item->toArray();
                $template = $item->template()->first();

                $template_uuid = (Templates::getByIdOrNull($item->template_id));
                if ($template_uuid->toArray != null) $template_uuid = $template_uuid->toArray['uuid'];
                else $template_uuid = null;

                $items['id'] = $item->id; // Добавляем id вручную
                $items['template_uuid'] = $template_uuid;
                $items['template']['query'] = $template;
                $items['template']['toArray'] = $template->toArray();
                $return[] = $items;
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
    public static function createOrUpdateIsArray($accountId, array $newArray): array
    {
        if ($newArray == []) return ['status' => false, 'message'=>'Пустой сценарий'];
        foreach ($newArray as $in => $item) {
            $model = Scenario::find($in);
            $is_create = true;
            if ($model != null) $is_create = false;

            $template_id = Templates::GetIsUuid($item['template'], $accountId);
            if ($template_id == null) return ['status' => false, 'message'=>'Ошибка присваивания шаблона к автоматизации'];
            if ($template_id->toArray == null) return ['status' => false, 'message'=>'Ошибка присваивания шаблона к автоматизации'];
            if ($is_create) $model = new Scenario();
            $model->accountId = $accountId;
            $model->entity = $item['entity'];
            $model->status = $item['status'];
            $model->channel = $item['saleschannel'];
            $model->project = $item['project'];
            $model->template_id = $template_id->toArray['id'];
            try {
                $model->save();
            } catch (Exception){
                return ['status' => false, 'message'=>'Не известная ошибка'];
            }
        }
        return ['status' => true];
    }

    public function template()
    {
        return $this->belongsTo(Templates::class);
    }

    public static function deleteIsUuid($accountId, $newArray)
    {
    }

    public function mainSettings()
    {
        return $this->belongsTo(MainSettings::class);
    }

    public function getScenario()
    {
        return $this->belongsTo(Scenario::class, 'scenario_id');
    }
}
