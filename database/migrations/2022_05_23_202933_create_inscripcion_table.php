<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInscripcionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inscripcion', function (Blueprint $table) {
            $table->char('apellido_alumno', 30)->nullable();
            $table->char('nombre_alumno', 30);
            $table->text('direccion_alumno')->nullable();
            $table->char('bachillerato', 70)->nullable();
            $table->char('nombre_completo', 60)->nullable();
            $table->char('f_y_l_nacimiento', 60)->nullable();
            $table->char('e_c_e', 80)->nullable();
            $table->char('nombre_padre', 60)->nullable();
            $table->text('direccion_padre')->nullable();
            $table->char('telefono_padre', 9)->nullable();
            $table->char('trabajo_padre', 70)->nullable();
            $table->char('profesion_padre', 30)->nullable();
            $table->char('nombre_madre', 60)->nullable();
            $table->text('direccion_madre')->nullable();
            $table->char('telefono_madre', 9)->nullable();
            $table->char('trabajo_madre', 70)->nullable();
            $table->char('profesion_madre', 30)->nullable();
            $table->char('vive', 1)->nullable();
            $table->char('vive_con', 60)->nullable();
            $table->char('parentesco', 15)->nullable();
            $table->char('año', 15)->nullable();
            $table->date('fecha')->nullable();
            $table->decimal('año_lectivo', 4, 0)->nullable();
            $table->char('seccion', 2)->nullable();
            $table->char('ordinario_extraordinario', 1)->nullable();
            $table->char('excepciones', 1)->nullable();
            $table->char('password', 20)->nullable();
            $table->char('apellido_nombre_alumno', 60)->nullable();
            $table->char('nombre_foto', 250)->nullable();
            $table->char('sexo', 1)->nullable();
            $table->char('extra_edad', 1)->nullable();
            $table->char('repite_grado', 1)->nullable();
            $table->decimal('edad', 2, 0)->nullable();
            $table->char('nombre_encargado', 30)->nullable();
            $table->char('parentesco_encargado', 30)->nullable();
            $table->decimal('ingreso_mes', 14)->nullable();
            $table->char('grado_alumnos', 1)->nullable();
            $table->char('alergico', 1)->nullable();
            $table->char('alergico_especifique', 200)->nullable();
            $table->char('vacuna', 1)->nullable();
            $table->char('limitaciones_fisicas', 1)->nullable();
            $table->char('certificado_grado_anterior', 1)->nullable();
            $table->char('partida_de_nacimiento', 1)->nullable();
            $table->char('copia_tarjeta_vacunacion', 1)->nullable();
            $table->char('nombre_persona', 60)->nullable();
            $table->char('dui_persona', 15)->nullable();
            $table->char('apellido_paterno', 30)->nullable();
            $table->char('apellido_materno', 30)->nullable();
            $table->char('codigo', 10)->nullable();
            $table->char('numero_telefono_alumno', 10)->nullable();
            $table->char('con_condicion', 1)->nullable();
            $table->char('no_matricula', 1)->nullable();
            $table->char('turno', 15)->nullable();
            $table->char('pn_numero', 15)->nullable();
            $table->char('pn_folio', 15)->nullable();
            $table->char('pn_tomo', 15)->nullable();
            $table->char('pn_libro', 15)->nullable();
            $table->char('cerz', 1)->nullable();
            $table->char('educame', 1)->nullable();
            $table->char('observaciones', 200)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->char('departamento_nacimiento', 30)->nullable();
            $table->char('municipio_nacimiento', 45)->nullable();
            $table->char('dui_alumno', 15)->nullable();
            $table->char('estado_civil_alumno', 25)->nullable();
            $table->char('nacionalidad_alumno', 15)->nullable();
            $table->char('transporte_alumno', 10)->nullable();
            $table->decimal('distancia_alumno', 3, 0)->nullable();
            $table->char('direccion_e_mail', 50)->nullable();
            $table->char('telefono_celular', 10)->nullable();
            $table->decimal('n_miembros_alumno', 2, 0)->nullable();
            $table->char('trabajo_alumno', 1)->nullable();
            $table->char('tiene_hijos_alumno', 1)->nullable();
            $table->char('apellido_padre', 30)->nullable();
            $table->char('dui_padre', 15)->nullable();
            $table->char('apellido_madre', 30)->nullable();
            $table->char('dui_madre', 15)->nullable();
            $table->char('apellido_encargado', 30)->nullable();
            $table->char('dui_encargado', 30)->nullable();
            $table->char('trabajo_encargado', 40)->nullable();
            $table->char('telefono_encargado', 12)->nullable();
            $table->char('profesion_encargado', 30)->nullable();
            $table->text('direccion_encargado')->nullable();
            $table->char('medicamento_permanente', 200)->nullable();
            $table->char('convivencia_alumno', 15)->nullable();
            $table->char('economicamente_alumno', 15)->nullable();
            $table->char('retirado', 1)->nullable();
            $table->char('talla_camisa', 2)->nullable();
            $table->char('talla_pantalon', 2)->nullable();
            $table->char('talla_falda', 2)->nullable();
            $table->char('talla_zapato', 2)->nullable();
            $table->char('paquete_escolar', 2)->nullable();
            $table->bigInteger('id_inscripcion', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inscripcion');
    }
}
