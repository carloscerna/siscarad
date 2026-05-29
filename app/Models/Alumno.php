<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AlumnoEncargado;

class Alumno extends Model
{
    use HasFactory;

    protected $table = 'alumno';

    public function notas()
    {
        return $this->hasMany(Nota::class);
    }

public function matriculas()
{
    // 'codigo_alumno' es la FK en alumno_matricula
    // 'id_alumno' es la PK en la tabla alumno
    return $this->hasMany(AlumnoMatricula::class, 'codigo_alumno', 'id_alumno');
}

/**
     * NUEVA RELACIÓN: Vincula con el encargado apuntando el 'codigo_alumno' 
     * de la tabla 'alumno_encargado' al 'id_alumno' de esta tabla.
     */
    public function encargadoPrincipal()
    {
        // Use the fully-qualified class name as a string to avoid static analysis
        // issues with undefined class types in some tooling/environments.
        return $this->hasOne('App\\Models\\AlumnoEncargado', 'codigo_alumno', 'id_alumno')
                    ->where('encargado', true);
    }

}
