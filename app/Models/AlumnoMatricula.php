<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumnoMatricula extends Model
{
    use HasFactory;
    protected $table = 'alumno_matricula';

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }
}
