<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumno', function (Blueprint $table) {
            $table->bigInteger('id_alumno', true);
            $table->char('apellido_materno', 30);
            $table->char('apellido_paterno', 30);
            $table->char('nombre_completo', 60);
            $table->char('codigo_nie', 12)->nullable()->default('0');
            $table->text('direccion_alumno')->nullable();
            $table->char('telefono_alumno', 9)->nullable();
            $table->char('codigo_departamento', 2)->nullable()->default('02');
            $table->char('codigo_municipio', 2)->nullable()->default('10');
            $table->date('fecha_nacimiento')->nullable();
            $table->char('nacionalidad', 15)->nullable();
            $table->decimal('distancia', 3, 0)->nullable()->default(0);
            $table->char('pn_numero', 4)->nullable();
            $table->char('pn_folio', 4)->nullable();
            $table->char('pn_tomo', 6)->nullable();
            $table->char('pn_libro', 6)->nullable();
            $table->char('transporte', 15)->nullable();
            $table->text('medicamento')->nullable();
            $table->char('direccion_email', 50)->nullable();
            $table->decimal('edad', 2, 0)->nullable()->default(0);
            $table->boolean('certificado')->nullable()->default(true);
            $table->boolean('partida_nacimiento')->nullable()->default(true);
            $table->boolean('tarjeta_vacunacion')->nullable()->default(true);
            $table->char('genero', 1)->nullable();
            $table->text('foto')->nullable()->default('foto_no_disponible.jpg');
            $table->boolean('estudio_parvularia')->nullable()->default(true);
            $table->char('codigo_estado_civil', 2)->nullable()->default('01');
            $table->char('codigo_estado_familiar', 2)->nullable()->default('01');
            $table->char('codigo_actividad_economica', 2)->nullable()->default('01');
            $table->char('codigo_apoyo_educativo', 2)->nullable()->default('01');
            $table->char('codigo_discapacidad', 2)->nullable()->default('01');
            $table->text('ruta_pn')->nullable();
            $table->text('ruta_pn_vuelto')->nullable();
            $table->char('codigo_zona_residencia', 2)->nullable()->default('01');
            $table->boolean('tiene_hijos')->nullable()->default(false);
            $table->decimal('cantidad_hijos', 1, 0)->nullable()->default(0);
            $table->char('telefono_celular', 9)->nullable();
            $table->char('codigo_genero', 2)->nullable();
            $table->char('codigo_estatus', 2)->nullable();
            $table->char('codigo_transporte', 2)->nullable()->default('04');
            $table->char('codigo_nacionalidad', 2)->nullable()->default('01');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumno');
    }
}
