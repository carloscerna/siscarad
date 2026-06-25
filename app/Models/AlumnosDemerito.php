<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumnosDemerito extends Model
{
    protected $table = 'public.alumnos_demeritos';
    protected $primaryKey = 'id_demerito_consolidado';

    protected $fillable = [
        'id_encargado_grado',
        'codigo_ann_lectivo',
        'mes_evaluacion',
        'matricula_hombres',
        'matricula_mujeres',
        'total_demeritos_hombres',
        'total_demeritos_mujeres',
        'dem_causal_a',
        'dem_causal_b',
        'dem_causal_c',
        'dem_causal_d',
        'redenciones_hombres',
        'redenciones_mujeres',
        'redencion_opcion_a',
        'redencion_opcion_b',
        'redencion_opcion_c',
        'reconocimientos_hombres',
        'reconocimientos_mujeres',
        'reconocimiento_diploma',
        'reconocimiento_mural'
    ];

    public $timestamps = true;
}