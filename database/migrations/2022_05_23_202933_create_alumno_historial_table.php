<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnoHistorialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumno_historial', function (Blueprint $table) {
            $table->bigInteger('id_alumno_bitacora', true);
            $table->decimal('codigo_alumno', 12, 0);
            $table->date('fecha_ob');
            $table->text('historial');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumno_historial');
    }
}
