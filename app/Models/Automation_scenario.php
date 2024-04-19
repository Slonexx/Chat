<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Automation_scenario extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'accountId',
        'scenario_id',
        'template_id',
    ];


    public static function getInformation($automation_id): object
    {
        $model = Automation_scenario::where('automation_id',  $automation_id )->get();
        if (!$model->isEmpty()) {
            $return = [];
            foreach ($model as $item) {
                $items = $item->toArray();
                unset($items['created_at']);
                unset($items['updated_at']);
                $items['scenario'] = $item->scenario->toArray();
                $items['template'] = $item->template->toArray();
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

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Templates::class);
    }

}
