<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponsableOperativo extends Model
{
    protected $table = 'responsables_operativos';
    protected $primaryKey = 'responsable_operativo_id';
    
    // Si la tabla no tiene created_at y updated_at, agregamos:
    public $timestamps = false;

    protected $fillable = [
        'unidad_responsable_gasto_id',
        'numero',
        'nombre',
    ];

    protected static function booted()
    {
        static::deleted(function ($ro) {
            $ros = static::where('unidad_responsable_gasto_id', $ro->unidad_responsable_gasto_id)
                ->where('numero', '>', $ro->numero)
                ->orderBy('numero', 'asc')
                ->get();
                
            foreach ($ros as $r) {
                $nuevoNumero = intval($r->numero) - 1;
                $r->numero = str_pad($nuevoNumero, 2, '0', STR_PAD_LEFT);
                $r->save();
            }
        });
    }
}
