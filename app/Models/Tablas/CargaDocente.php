<?php

namespace App\Models\Tablas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargaDocente extends Model
{
    use HasFactory;
    protected $table = "carga_docente";
    protected $fillable = ['codigo_asignatura','codigo_bachillerato','codigo_grado','codigo_seccion','codigo_ann_lectivo','codigo_docente','codigo_turno'];
}
