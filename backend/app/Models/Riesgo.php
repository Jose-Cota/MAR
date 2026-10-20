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
        'puntaje',
        'ponderacion',
        'probabilidad_inicial',
        'impacto_inicial',
        'status',
        'last_observation',
    ];

    protected static function booted()
    {
        static::saving(function ($riesgo) {
            if ($riesgo->probabilidad !== null && $riesgo->impacto !== null) {
                $year = ($riesgo->ejercicio_id == 19 || $riesgo->ejercicio_id == 2027) ? 2027 : 2026;
                $peso = \Illuminate\Support\Facades\DB::table('factor_peso')
                    ->where('ejercicio', $year)
                    ->where('ur_id', $riesgo->area_id)
                    ->value('factor_peso') ?? 1;
                $riesgo->puntaje = round((float)$riesgo->probabilidad * (float)$riesgo->impacto * (float)$peso, 2);
            }
        });

        static::saved(function ($riesgo) {
            self::recalcularPonderacionesUR($riesgo->area_id, $riesgo->ejercicio_id);
            foreach ($riesgo->riesgosInstitucionales as $ri) {
                RiesgoInstitucional::recalcularPonderaciones($ri->id);
            }
        });
    }

    public static function recalcularPonderacionesUR($areaId, $ejercicioId)
    {
        $riesgos = self::where('area_id', $areaId)->where('ejercicio_id', $ejercicioId)->get();
        $totalPuntaje = $riesgos->sum('puntaje');
        foreach ($riesgos as $r) {
            $ponderacion = ($totalPuntaje > 0) ? round(($r->puntaje / $totalPuntaje) * 100, 2) : 0;
            self::where('id', $r->id)->update(['ponderacion' => $ponderacion]);
        }
    }

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
