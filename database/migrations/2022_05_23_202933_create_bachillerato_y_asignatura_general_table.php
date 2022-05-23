<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBachilleratoYAsignaturaGeneralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bachillerato_y_asignatura_general', function (Blueprint $table) {
            $table->char('nombre_asignatura', 30)->nullable();
            $table->char('concepto', 15)->nullable();
            $table->decimal('nomenclatura', 1, 0)->nullable();
            $table->char('area', 12)->nullable();
            $table->decimal('horas_semanales', 2, 0)->nullable();
            $table->boolean('imprimir_en_certificado')->nullable();
            $table->decimal('id', 2, 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bachillerato_y_asignatura_general');
    }
}
