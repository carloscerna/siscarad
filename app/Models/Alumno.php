<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->hasMany(AlumnoMatricula::class);
    }
}
