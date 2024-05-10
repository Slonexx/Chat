<?php

namespace App\Models;

use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lid extends Model
{

    public static function getInformationALLAcc($accountId): object
    {
        $model = Lid::where('accountId',  $accountId )->get()->first();
        if (!empty($model)) {
            return (object) [
                'query' => $model,
                'toArray' => $model->toArray(),
            ];
        } else {
            return (object) [
                'query' => $model,
                'toArray' => null,
            ];
        }

    }

    public static function createOrUpdate($array): object
    {
        $model = Lid::where('accountId',  $array['accountId'])->get()->first();

        if (empty($model)) $model = new Lid();


        $model->accountId = $array['accountId'];
        $model->is_activity_settings = $array['is_activity_settings'];
        $model->is_activity_order = $array['is_activity_order'];
        $model->lid = $array['lid'];
        $model->responsible = $array['responsible'];
        $model->responsible_uuid = $array['responsible_uuid'];
        $model->tasks = $array['tasks'];

        $model->organization = $array['organization'];
        $model->organization_account = $array['organization_account'];
        $model->states = $array['states'];
        $model->project_uid = $array['project_uid'];
        $model->sales_channel_uid = $array['sales_channel_uid'];

        try {
            $model->save();
            return (object) ['status' => true];
        } catch (BadResponseException $e){
            return (object) ['status' => false, 'message'=> 'Ошибка: ' . $e->getMessage()];
        }

    }
    protected $table = "lids";

    protected $guarded = [];

    public static function getFirstByAccountId($accountId){
        return self::where("accountId", $accountId)->get()->first();
    }
}
