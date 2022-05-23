<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNombreDeLasAsignaturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nombre_de_las_asignaturas', function (Blueprint $table) {
            $table->char('nombre_asignatura', 70)->nullable();
            $table->char('de_concepto_o_calificacion', 15)->nullable();
            $table->decimal('nomenclatura', 1, 0)->nullable();
            $table->char('area', 20)->nullable();
            $table->decimal('horas_semanales', 2, 0)->nullable();
            $table->boolean('imprimir_en_certificado')->nullable();
            $table->boolean('area_asignatura')->nullable();
            $table->char('nombre_area_asignatura', 30)->nullable();
            $table->decimal('partes_dividida', 1, 0)->nullable();
            $table->boolean('partes')->nullable();
            $table->char('id', 15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nombre_de_las_asignaturas');
    }
}
