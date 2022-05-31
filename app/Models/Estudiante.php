<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiantea extends Model
{
    use HasFactory;
    protected $table = "alumno";
    // es para definir que registros van hacer guardados, o sea solo los campos que esten en el listado.
        protected $fillable = ['apellido_materno','apellido_paterno','nombre_completo',
            'codigo_nie','direccion_alumno','telefono_alumno','codigo_departamento','codigo_municipio',
            'fecha_nacimiento','nacionalidad','distancia','pn_numero','pn_folio','pn_tomo','pn_libro',
            'transporte','medicamento','direccion_email','edad','certificado','partida_nacimiento',
            'tarjeta_vacunacion','genero','foto','estudio_parvularia','codigo_estado_civil',
            'codigo_estado_familiar','codigo_actividad_economica','codigo_apoyo_educativo','codigo_discapacidad',
            'ruta_pn','ruta_pn_vuelto','codigo_zona_residencia','tiene_hijos','cantidad_hijos','telefono_celular',
            'codigo_genero','codigo_estatus','codigo_transporte','codigo_nacionalidad'
        ]; 
}
