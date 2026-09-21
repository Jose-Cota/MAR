<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiesgoEvaluacionTrimestral extends Model
{
    protected $table = 'riesgo_evaluaciones_trimestrales';

    protected $fillable = [
        'riesgo_id',
        'ejercicio_id',
        'trimestre',
        'etiqueta',
        'probabilidad',
        'impacto',
        'evidencia_control',
        'incidencia',
        'accion_mitigacion',
        'estatus',
        'responsable'
    ];

    public function riesgo()
    {
        return $this->belongsTo(Riesgo::class);
    }
}
