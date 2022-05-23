<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnoEncargadoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumno_encargado', function (Blueprint $table) {
            $table->bigInteger('id_alumno_encargado', true);
            $table->decimal('codigo_alumno', 12, 0);
            $table->char('nombres', 80)->nullable();
            $table->char('lugar_trabajo', 60)->nullable();
            $table->char('profesion_oficio', 60)->nullable();
            $table->char('dui', 10)->nullable();
            $table->char('telefono', 9)->nullable();
            $table->text('direccion')->nullable();
            $table->boolean('encargado')->nullable()->default(false);
            $table->char('institucion_proviene', 80)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->char('codigo_familiar', 2)->nullable()->default('03');
            $table->char('codigo_nacionalidad', 2)->nullable()->default('01');
            $table->char('codigo_zona', 2)->nullable()->default('01');
            $table->char('codigo_departamento', 2)->nullable()->default('02');
            $table->char('codigo_municipio', 2)->nullable()->default('10');
            $table->char('codigo_genero', 2)->nullable()->default('02');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumno_encargado');
    }
}
