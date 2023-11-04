<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class polesModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'accountId',
        'name',
        'name_uid',

        'i',
        'pole',
        'add_pole',
        'entity',
    ];

}
