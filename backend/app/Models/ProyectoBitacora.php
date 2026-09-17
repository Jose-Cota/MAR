<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProyectoBitacora extends Model
{
    use HasFactory;

    protected $fillable = [
        'proyecto_id',
        'user_id',
        'accion',
        'mensaje',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
