<?php

namespace App\Models;

use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notes extends Model
{
    protected $table = "notes";

    protected $guarded = [];

    static function getByAccountId($accountId){
        return self::where("accountId", $accountId)->get(["is_messenger", "last_start"]);
    }

    public static function createOrUpdate(array $data): object
    {
        $model = Notes::where('accountId',  $data['accountId'])->get()->first();

        if (empty($model)) $model = new Notes();

        $model->accountId = $data['accountId'];
        $model->is_activity_agent = $data['is_activity_agent'];
        $model->notes = $data['notes'];
        $model->is_messenger = $data['is_messenger'];

        $model->save();
        try {
            $model->save();
            return (object) ['status' => true];
        } catch (\Exception $e){
            return (object) ['status' => false, 'message'=> 'Ошибка: ' . $e->getMessage()];
        }
    }


    public static function getInformationALLAcc($accountId): object
    {
        $model = Notes::where('accountId',  $accountId )->get()->first();
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
}
