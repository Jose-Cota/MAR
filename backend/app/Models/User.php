<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['usuario', 'correo', 'area_id', 'nombre', 'apellido_paterno', 'apellido_materno', 'sexo', 'password', 'foto', 'nivel', 'ejercicio_elaboracion', 'consulta_integral', 'captura_seguimiento', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, \Spatie\Permission\Traits\HasRoles;

    protected $table = 'usuarios_poa';
    protected $primaryKey = 'usuario_poa_id';
    public $timestamps = false;

    protected $appends = ['name', 'email'];

    public function getNameAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno);
    }

    public function getEmailAttribute()
    {
        return $this->usuario;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // password might be old format, do not cast until all are bcrypt
        ];
    }

    public function responsablesOperativos()
    {
        return $this->belongsToMany(
            ResponsableOperativo::class,
            'usuarios_responsables_operativos',
            'usuario_poa_id',
            'responsable_operativo_id'
        );
    }

    public function unidadesResponsables()
    {
        return $this->belongsToMany(
            UnidadResponsableGasto::class,
            'usuario_unidad_responsable',
            'usuario_poa_id',
            'unidad_responsable_gasto_id'
        );
    }
}
