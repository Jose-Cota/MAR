<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadResponsableGasto extends Model
{
    protected $table = 'unidades_responsables_gastos';
    protected $primaryKey = 'unidad_responsable_gasto_id';
    
    public $timestamps = false;

    protected $fillable = [
        'ejercicio_id',
        'numero',
        'nombre',
        'cerrada',
    ];

    protected static function booted()
    {
        // Al eliminar una UR, eliminamos sus ROs y reacomodamos la numeración de las demás UR
        static::deleting(function ($urg) {
            \Illuminate\Support\Facades\DB::table('responsables_operativos')
                ->where('unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id)
                ->delete();
        });

        static::deleted(function ($urg) {
            $urss = static::where('ejercicio_id', $urg->ejercicio_id)
                ->where('numero', '>', $urg->numero)
                ->orderBy('numero', 'asc')
                ->get();
                
            foreach ($urss as $ur) {
                $nuevoNumero = intval($ur->numero) - 1;
                $ur->numero = str_pad($nuevoNumero, 2, '0', STR_PAD_LEFT);
                $ur->save();
            }
        });
    }
}
