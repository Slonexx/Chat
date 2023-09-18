<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class organizationModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'accountId',
        'organId',
        'organName',

        'employeeId',
        'employeeName',

        'lineId',
        'lineName',
    ];

}
