<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class templateModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'accountId',
        'organId',

        'name',
        'name_uid',

        'message',
    ];

}
