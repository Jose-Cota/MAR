<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionTrimestre extends Model
{
    protected $fillable = [
        'unidad_responsable_id',
        'ejercicio_id',
        't1_abierto',
        't2_abierto',
        't3_abierto',
        't4_abierto',
    ];
}
