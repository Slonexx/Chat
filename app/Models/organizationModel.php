<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class organizationModel extends Model
{
    use HasFactory;

    protected $table = "organization_models";


    protected $fillable = [
        'accountId',
        'organId',
        'organName',

        'employeeId',
        'employeeName',

        'lineId',
        'lineName',
    ];


    public static function getInformation(mixed $accountId): object
    {
        $model = organizationModel::where('accountId', $accountId)->get([
            'accountId', 'org_uid', 'login', 'pass', 'auth', 'email', 'group_code', 'sno', 'inn', 'payment_address'
        ]);
        if (!$model->isEmpty()) {
            $toArray = null;
            foreach ($model as $item) {
                $toArray[] = $item->toArray();
            }

            return (object)[
                'query' => $model,
                'toArray' => $toArray,
            ];
        } else {
            return (object)[
                'query' => $model,
                'toArray' => null,
            ];
        }
    }
    /**
     * @return object[] lineId for Chatapp
     */
    public static function getLineIdByAccountId(string $accountId){
        return self::where("accountId", $accountId)
            ->get(["lineId", "employeeId", "organId", "lineName"])
            ->all();
    }

}
