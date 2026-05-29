<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Alumno;

class AlumnoEncargado extends Model
{
    // If your table name follows Laravel convention (alumno_encargados) you can remove this
    protected $table = 'alumno_encargado';

    // Primary key (adjust if different)
    protected $primaryKey = 'id_alumno_encargado';

    public $timestamps = true;

    protected $fillable = [
        'codigo_alumno',
        'encargado',
        'nombres',
        'telefono',
        'direccion',
        'firma_autorizacion',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'codigo_alumno', 'id_alumno');
    }
}