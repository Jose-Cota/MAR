<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    use HasFactory;

    protected $fillable = ['usuario_poa_id', 'nombre'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_poa_id', 'usuario_poa_id');
    }
}
