<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEncargadoGradoViejoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('encargado_grado_viejo', function (Blueprint $table) {
            $table->bigInteger('id_encargado_grado', true)->index('id_grado_encargado');
            $table->boolean('encargado')->default(false);
            $table->boolean('imparte_asignatura')->default(false);
            $table->char('codigo_docente', 2);
            $table->char('codigo_ann_lectivo', 2);
            $table->char('codigo_seccion', 2);
            $table->char('codigo_bachillerato', 2);
            $table->char('codigo_grado', 2);
            $table->char('codigo_encargado', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('encargado_grado_viejo');
    }
}
