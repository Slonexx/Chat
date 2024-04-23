<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class employeeModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'accountId',

        'employeeId',
        'employeeName',

        'email',
        'password',
        'appId',

        'access',

        'cabinetUserId',
        'accessToken',
        'refreshToken',
    ];

    public static function getAllEmpl($accountId): object
    {
        $model = employeeModel::where('accountId', $accountId )->get();
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

    public function getEmployee()
    {
        return $this->belongsTo(employeeModel::class, 'employee_id');
    }

}
