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
        'riesgo',
        'factores',
        'probabilidad',
        'impacto',
        'probabilidad_inicial',
        'impacto_inicial',
        'status',
        'last_observation',
    ];

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
}
