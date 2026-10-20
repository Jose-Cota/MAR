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
        'control',
        'indicador',
        'probabilidad_sugerida',
        'impacto_sugerido',
        'probabilidad',
        'impacto',
        'ponderacion',
        'ponderacion_probabilidad',
        'ponderacion_impacto',
        'justificacion_valoracion',
        'estatus',
    ];

    public function fuentes()
    {
        return $this->belongsToMany(Riesgo::class, 'riesgo_institucional_fuente', 'riesgo_institucional_id', 'riesgo_id')->withTimestamps();
    }

    /**
     * Calcula la ponderación de probabilidad e impacto según las fórmulas:
     * 1. Sumar los puntajes de todos los riesgos de UR asociados (Sumatoria / PRA)
     * 2. Ponderación Probabilidad: (puntaje RUR / Sumatoria) * Probabilidad RUR
     *    Ponderación Impacto:      (puntaje RUR / Sumatoria) * Impacto RUR
     * 3. Sumar todas las ponderaciones
     * 4. Asignar los resultados a ponderacion_probabilidad y ponderacion_impacto
     */
    public static function recalcularPonderaciones($riId = null)
    {
        $query = self::with('fuentes');
        if ($riId) {
            $query->where('id', $riId);
        }
        $ris = $query->get();

        foreach ($ris as $ri) {
            $sumatoria = (float) $ri->fuentes->sum('puntaje');
            if ($sumatoria > 0) {
                $sumaPondProb = 0;
                $sumaPondImp = 0;
                foreach ($ri->fuentes as $f) {
                    $ratio = (($f->puntaje ?? 0) / $sumatoria);
                    $sumaPondProb += $ratio * (float) $f->probabilidad;
                    $sumaPondImp += $ratio * (float) $f->impacto;
                }
                $ri->update([
                    'ponderacion_probabilidad' => round($sumaPondProb, 4),
                    'ponderacion_impacto' => round($sumaPondImp, 4),
                ]);
            } else {
                $ri->update([
                    'ponderacion_probabilidad' => null,
                    'ponderacion_impacto' => null,
                ]);
            }
        }
    }

    public static function recalcularPonderacionProbabilidad($riId = null)
    {
        self::recalcularPonderaciones($riId);
    }

    public static function recalcularPonderacionImpacto($riId = null)
    {
        self::recalcularPonderaciones($riId);
    }
}
