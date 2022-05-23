<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEncargadoGradoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('encargado_grado', function (Blueprint $table) {
            $table->bigInteger('id_encargado_grado', true);
            $table->boolean('encargado')->default(false);
            $table->boolean('imparte_asignatura')->default(false);
            $table->char('codigo_ann_lectivo', 2);
            $table->char('codigo_seccion', 2);
            $table->char('codigo_bachillerato', 2);
            $table->char('codigo_grado', 2);
            $table->char('codigo_encargado', 2)->nullable();
            $table->decimal('codigo_docente', 2, 0);
            $table->char('codigo_turno', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('encargado_grado');
    }
}
