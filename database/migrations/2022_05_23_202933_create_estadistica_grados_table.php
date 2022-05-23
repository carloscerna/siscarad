<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstadisticaGradosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estadistica_grados', function (Blueprint $table) {
            $table->char('genero', 10)->nullable();
            $table->decimal('matricula_inicial', 3, 0)->nullable()->default(0);
            $table->decimal('retirados', 3, 0)->nullable()->default(0);
            $table->decimal('matricula_final', 3, 0)->nullable()->default(0);
            $table->decimal('promovidos', 3, 0)->nullable()->default(0);
            $table->decimal('retenidos', 3, 0)->nullable()->default(0);
            $table->bigInteger('id_estadistica_grado', true);
            $table->char('codigo_grado', 2)->nullable();
            $table->char('codigo_seccion', 2)->nullable();
            $table->char('codigo_bachillerato_ciclo', 2)->nullable();
            $table->char('codigo_ann_lectivo', 2)->nullable();
            $table->char('codigo_turno', 2)->nullable();
            $table->decimal('codigo_docente', 2, 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estadistica_grados');
    }
}
