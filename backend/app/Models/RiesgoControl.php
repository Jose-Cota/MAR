<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiesgoControl extends Model
{
    use HasFactory;

    protected $table = 'riesgo_controles';

    protected $fillable = [
        'riesgo_id',
        'texto',
        'evidencia_tipo',
        'evidencia_referencia',
        'evidencia_periodicidad',
        'evidencia_responsable',
        'evidencia_link',
    ];

    public function riesgo()
    {
        return $this->belongsTo(Riesgo::class, 'riesgo_id');
    }
}
