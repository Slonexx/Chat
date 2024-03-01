<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsEntities extends Model
{
    protected $table = "ms_entities";

    protected $guarded = [];

    public function fields()
    {
        return $this->hasMany(MsEntityFields::class);
    }

    public static function truncateIfNotEmpty()
    {
        $count = self::count();

        if ($count > 0) {
            self::truncate();
        }
    }
}
