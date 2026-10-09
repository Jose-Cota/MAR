<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Riesgo extends Model
{
    use HasFactory;

    protected $table = 'riesgos';

    protected $fillable = [
        'local_id',
        'area_id',
        'ejercicio_id',
        'objetivo',
        'efectos_consecuencias',
        'riesgo',
        'factores',
        'factores_internos',
        'factores_externos',
        'probabilidad',
        'impacto',
        'probabilidad_inicial',
        'impacto_inicial',
        'status',
        'last_observation',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'area_id');
    }

    public function controles()
    {
        return $this->hasMany(RiesgoControl::class, 'riesgo_id');
    }

    public function indicadores()
    {
        return $this->hasMany(RiesgoIndicador::class, 'riesgo_id');
    }

    public function actividades()
    {
        return $this->belongsToMany(ActividadSustantiva::class, 'actividad_riesgo', 'riesgo_id', 'actividad_sustantiva_id')->withTimestamps();
    }

    public function seguimientos_mensuales()
    {
        return $this->hasMany(RiesgoSeguimientoMensual::class, 'riesgo_id');
    }

    public function evaluaciones_trimestrales()
    {
        return $this->hasMany(RiesgoEvaluacionTrimestral::class, 'riesgo_id');
    }

    public function seguimiento_trimestral()
    {
        return $this->hasMany(SeguimientoTrimestral::class, 'riesgo_id');
    }

    public function riesgosInstitucionales()
    {
        return $this->belongsToMany(RiesgoInstitucional::class, 'riesgo_institucional_fuente', 'riesgo_id', 'riesgo_institucional_id')->withTimestamps();
    }
}
