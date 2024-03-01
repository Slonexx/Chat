<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsEntityFields extends Model
{
    protected $table = "ms_entity_fields";

    protected $guarded = [];

    public function entities()
    {
        return $this->belongsTo(MsEntities::class)->withDefault();
    }
}
