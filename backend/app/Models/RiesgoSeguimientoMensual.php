<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiesgoSeguimientoMensual extends Model
{
    protected $table = 'riesgo_seguimientos_mensuales';

    protected $fillable = [
        'riesgo_id',
        'ejercicio_id',
        'mes',
        'numerador',
        'denominador',
        'valor'
    ];

    public function riesgo()
    {
        return $this->belongsTo(Riesgo::class);
    }
}
