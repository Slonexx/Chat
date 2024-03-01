<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainSettings extends Model
{
    protected $table = "main_settings";

    protected $guarded = [];

    public function templates()
    {
        return $this->hasMany(Templates::class);
    }
    
}
