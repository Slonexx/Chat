<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Automation extends Model
{
    public $incrementing = false;
    protected $fillable = [
        'id',
        'accountId',
        'is_default',
        'line',
        'messenger',
        'employee_id',
    ];
    public static function getInformationALLAcc($accountId): object
    {
        $model = Automation::with('automation', 'automation.scenario', 'automation.template')
            ->where('accountId',  $accountId)
            ->get();

        if (!$model->isEmpty()) {
            $return = [];
            foreach ($model as $item) {
                $items = $item->toArray();
                unset($items['created_at']);
                unset($items['updated_at']);
                $items['automation'] = $item->automation->toArray();
                $items['employee'] = $item->employee->toArray();
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

    public static function createOrUpdateIsArray($accountId, array $data): void
    {
        $is_bool = !empty($data['id']);

        if ($is_bool){
            $model = Automation::find($data['id']['id']);

            foreach (Automation_scenario::getInformation($model->get()->toArray()[0]['id'])->query as $item) $item->delete();
        }
        else $model = new Automation();

        $model->accountId = $accountId;
        $model->line = $data['line'];
        $model->messenger = $data['messenger'];
        $model->is_default = $data['is_default'];
        $model->employee_id  = $data['employee_id'];
        $model->save();

        $id = $model->get()->toArray()[0]['id'];




        foreach ($data['template'] as $item){
            $model_automation = new Automation_scenario();
            $model_automation->automation_id = $id;
            $model_automation->scenario_id  = $item;
            $model_automation->template_id = Scenario::find($item)->toArray()['template_id'];
            $model_automation->save();
        }



    }

    public function automation(): HasMany
    {
        return $this->hasMany(Automation_scenario::class, 'automation_id', 'id');
    }


    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id');
    }
}
