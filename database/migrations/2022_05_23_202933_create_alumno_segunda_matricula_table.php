<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnoSegundaMatriculaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumno_segunda_matricula', function (Blueprint $table) {
            $table->bigInteger('id_segunda_matricula', true);
            $table->char('codigo_asignatura', 2)->nullable();
            $table->decimal('codigo_alumno', 12, 0)->nullable();
            $table->decimal('codigo_matricula', 12, 0)->nullable();
            $table->decimal('nota_p_p_1', 5)->nullable();
            $table->decimal('nota_p_p_2', 5)->nullable();
            $table->decimal('nota_p_p_3', 5)->nullable();
            $table->decimal('nota_p_p_4', 5)->nullable();
            $table->decimal('nota_recuperacion', 5)->nullable();
            $table->decimal('nota_final', 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumno_segunda_matricula');
    }
}
