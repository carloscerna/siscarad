<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstudiosRealizadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estudios_realizados', function (Blueprint $table) {
            $table->char('nombre_completo_docente', 70);
            $table->char('institucion_educativa', 60)->nullable();
            $table->char('año', 5)->nullable();
            $table->decimal('numero_registro', 10, 0)->nullable();
            $table->char('documento_obtenido', 60)->nullable();
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
        Schema::dropIfExists('estudios_realizados');
    }
}
