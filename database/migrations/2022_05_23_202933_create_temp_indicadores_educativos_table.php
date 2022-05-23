<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempIndicadoresEducativosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_indicadores_educativos', function (Blueprint $table) {
            $table->char('codigo_modalidad_turno_ann_lectivo', 6);
            $table->char('nombre_modalidad', 70);
            $table->char('nombre_turno', 25)->nullable();
            $table->char('nombre_ann_lectivo', 10)->nullable();
            $table->integer('matricula_maxima_m')->nullable()->default(0);
            $table->integer('retirado_m')->nullable()->default(0);
            $table->integer('repitente_m')->nullable()->default(0);
            $table->integer('sobreedad_m')->nullable()->default(0);
            $table->integer('matricula_m')->nullable()->default(0);
            $table->char('codigo_genero', 2)->nullable();
            $table->integer('matricula_maxima_f')->nullable()->default(0);
            $table->integer('retirado_f')->nullable()->default(0);
            $table->integer('repitente_f')->nullable()->default(0);
            $table->integer('sobreedad_f')->nullable()->default(0);
            $table->integer('matricula_f')->nullable()->default(0);
            $table->integer('nuevo_ingreso_m')->nullable()->default(0);
            $table->integer('nuevo_ingreso_f')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temp_indicadores_educativos');
    }
}
