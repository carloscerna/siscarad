<?php

namespace App\Models\Tablas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstudianteMatricula extends Model
{
    use HasFactory;
    protected $table = "alumno_matricula";
    protected $fillable = ['id_alumno_matricula','codigo_alumno','codigo_bach_o_ciclo','codigo_grado','codigo_ann_lectivo', 'codigo_turno',
                        'certificado','pn'];
}
