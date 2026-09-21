<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiesgoInstitucional extends Model
{
    use HasFactory;

    protected $table = 'riesgos_institucionales';

    protected $fillable = [
        'folio',
        'ejercicio_id',
        'objetivo',
        'riesgo',
        'factores',
        'probabilidad_sugerida',
        'impacto_sugerido',
        'probabilidad',
        'impacto',
        'justificacion_valoracion',
        'estatus',
    ];

    public function fuentes()
    {
        return $this->belongsToMany(Riesgo::class, 'riesgo_institucional_fuente', 'riesgo_institucional_id', 'riesgo_id')->withTimestamps();
    }
}
