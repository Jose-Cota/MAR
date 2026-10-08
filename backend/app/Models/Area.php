<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo de áreas / unidades responsables (tabla legada `areas`).
 */
class Area extends Model
{
    protected $table = 'areas';

    protected $primaryKey = 'area_id';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function riesgos(): HasMany
    {
        return $this->hasMany(Riesgo::class, 'area_id', 'area_id');
    }
}
