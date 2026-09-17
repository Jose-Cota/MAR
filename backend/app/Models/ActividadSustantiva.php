<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActividadSustantiva extends Model
{
    use HasFactory;

    protected $connection = 'poa_prod';
    protected $table = 'acciones_sustantivas';
    protected $primaryKey = 'accion_sustantiva_id';
    public $timestamps = false;

    protected $fillable = [
        'proyecto_id',
        'numero',
        'descripcion',
        'recursos_asociados'
    ];

    public function riesgos()
    {
        return $this->belongsToMany(Riesgo::class, 'actividad_riesgo', 'actividad_sustantiva_id', 'riesgo_id')->withTimestamps();
    }
}
