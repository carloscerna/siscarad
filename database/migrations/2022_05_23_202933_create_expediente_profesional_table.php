<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpedienteProfesionalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expediente_profesional', function (Blueprint $table) {
            $table->char('nombre_completo_docente', 70);
            $table->char('partida', 25)->nullable();
            $table->char('sub_numero', 25)->nullable();
            $table->char('especialidad', 25)->nullable();
            $table->char('sexo', 15)->nullable();
            $table->char('estado_civil', 20)->nullable();
            $table->char('profesion_u_oficio', 30)->nullable();
            $table->text('lugar_y_fecha')->nullable();
            $table->char('cip', 25)->nullable();
            $table->char('nit', 25)->nullable();
            $table->char('inpep', 25)->nullable();
            $table->char('isss', 25)->nullable();
            $table->char('nip', 25)->nullable();
            $table->char('dui', 15)->nullable();
            $table->text('direccion_domicilio')->nullable();
            $table->char('municipio', 25)->nullable();
            $table->char('departamento', 25)->nullable();
            $table->char('domicilio_telefono', 25)->nullable();
            $table->char('sistema_contratacion_actual', 2)->nullable();
            $table->decimal('salario_actual', 12)->nullable();
            $table->text('espacio_institucional')->nullable();
            $table->char('password', 30)->nullable();
            $table->decimal('actividad_1', 5)->nullable();
            $table->decimal('actividad_2', 5)->nullable();
            $table->decimal('actividad_3', 5)->nullable();
            $table->boolean('encargado')->nullable();
            $table->boolean('imparte')->nullable();
            $table->decimal('actividad_1_t', 5)->nullable();
            $table->decimal('actividad_2_t', 5)->nullable();
            $table->decimal('actividad_3_t', 5)->nullable();
            $table->decimal('actividad_1_uno', 5)->nullable();
            $table->decimal('actividad_1_dos', 5)->nullable();
            $table->decimal('actividad_1_tres', 5)->nullable();
            $table->boolean('porcentaje_logico')->nullable();
            $table->boolean('suma_actividades')->nullable();
            $table->boolean('suma_actividades_bachillerato')->nullable();
            $table->char('codigo_docente', 15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expediente_profesional');
    }
}
