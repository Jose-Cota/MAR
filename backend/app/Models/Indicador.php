<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indicador extends Model
{
    use HasFactory;

    protected $connection = 'poa_prod';
    protected $table = 'indicadores';
    protected $primaryKey = 'indicador_id';

    public $timestamps = false;

    protected $fillable = [
        'meta_id',
        'proyecto_id',
        'unidad_medida_id',
        'dimension_id',
        'frecuencia_id',
        'nombre',
        'definicion',
        'metodo_calculo',
        'meta',
        'id_metap',
        'id_metac'
    ];

    public function metaComplementaria()
    {
        return $this->belongsTo(Meta::class, 'meta_id', 'id');
    }
}
