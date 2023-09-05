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

}
