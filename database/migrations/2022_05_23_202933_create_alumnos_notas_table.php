<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnosNotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumnos_notas', function (Blueprint $table) {
            $table->bigInteger('id_alumno');
            $table->bigInteger('id_notas');
            $table->bigInteger('id_seccion');
            $table->bigInteger('id_grado_año');
            $table->bigInteger('id_año_lectivo');
            $table->bigInteger('id_bachillerato_ciclo_asignatura');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumnos_notas');
    }
}
