<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoTrimestral extends Model
{
    protected $table = 'seguimiento_trimestrals';

    protected $fillable = [
        'riesgo_id', 'ejercicio_id', 'trimestre',
        'm1_n', 'm1_d', 'm2_n', 'm2_d', 'm3_n', 'm3_d',
        'notas', 'estatus'
    ];

    public function riesgo()
    {
        return $this->belongsTo(Riesgo::class);
    }}
