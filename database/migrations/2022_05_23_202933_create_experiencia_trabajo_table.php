<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExperienciaTrabajoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experiencia_trabajo', function (Blueprint $table) {
            $table->char('nombre_completo_docente', 70)->nullable();
            $table->text('unidad_direccion')->nullable();
            $table->char('desde', 5)->nullable();
            $table->char('hasta', 5)->nullable();
            $table->decimal('numero_registro', 10, 0)->nullable();
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
        Schema::dropIfExists('experiencia_trabajo');
    }
}
