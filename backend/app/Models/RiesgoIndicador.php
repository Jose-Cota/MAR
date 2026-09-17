<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiesgoIndicador extends Model
{
    use HasFactory;

    protected $table = 'riesgo_indicadores';

    protected $fillable = [
        'riesgo_id',
        'nombre',
        'tipo',
        'periodicidad',
    ];

    public function riesgo()
    {
        return $this->belongsTo(Riesgo::class, 'riesgo_id');
    }
}
