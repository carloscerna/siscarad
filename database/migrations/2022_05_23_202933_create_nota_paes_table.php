<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotaPaesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nota_paes', function (Blueprint $table) {
            $table->char('apellido_nombre', 60)->primary();
            $table->char('nombre_alumno', 60)->nullable();
            $table->char('bachillerato', 70)->nullable();
            $table->char('año', 15)->nullable();
            $table->char('materia', 70)->nullable();
            $table->decimal('promedio_institucional', 2, 0)->nullable();
            $table->decimal('nota_paes', 4)->nullable();
            $table->decimal('año_lectivo', 4, 0)->nullable();
            $table->char('seccion', 1)->nullable();
            $table->decimal('año_lectivo_anterior', 4, 0)->nullable();
            $table->char('bachillerato_anterior', 70)->nullable();
            $table->char('año_anterior', 15)->nullable();
            $table->boolean('ordinario_recuperacion')->nullable();
            $table->decimal('nota_paes_recuperacion', 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nota_paes');
    }
}
