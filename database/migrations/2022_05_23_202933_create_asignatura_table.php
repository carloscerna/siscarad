<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsignaturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asignatura', function (Blueprint $table) {
            $table->char('nombre', 175);
            $table->char('codigo', 3);
            $table->char('codigo_cc', 12)->nullable();
            $table->boolean('nomenclatura')->nullable()->default(false);
            $table->char('codigo_area', 10)->nullable();
            $table->decimal('horas_semanales', 2, 0)->nullable()->default(0);
            $table->boolean('imprimir')->nullable()->default(true);
            $table->char('area_asignatura', 20)->nullable();
            $table->char('nombre_area_asignatura', 20)->nullable();
            $table->decimal('partes_dividida', 1, 0)->nullable()->default(0);
            $table->boolean('partes')->nullable();
            $table->char('codigo_sirai', 8)->nullable();
            $table->char('codigo_a_matricula', 2)->nullable();
            $table->bigInteger('id_asignatura', true);
            $table->char('descripcion', 35)->nullable();
            $table->char('codigo_servicio_educativo', 2)->nullable();
            $table->boolean('estatus')->nullable()->default(true);
            $table->bigInteger('id', true);
            $table->timeTz('timestampTz')->nullable();
            $table->timeTz('updated_at')->nullable();
            $table->timeTz('created_at')->nullable();
            $table->char('codigo_estatus', 2)->nullable()->default('01')->comment('Establece si la asignatura esta activa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asignatura');
    }
}
