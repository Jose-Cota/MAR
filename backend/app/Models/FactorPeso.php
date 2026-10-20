<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactorPeso extends Model
{
    use HasFactory;

    protected $table = 'factor_peso';

    protected $fillable = [
        'ejercicio',
        'ur',
        'ur_id',
        'factor_peso',
    ];

    protected $casts = [
        'ejercicio' => 'integer',
        'factor_peso' => 'float',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'ur_id', 'area_id');
    }
}
