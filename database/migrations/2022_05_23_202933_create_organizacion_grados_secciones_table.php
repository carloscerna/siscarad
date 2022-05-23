<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizacionGradosSeccionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizacion_grados_secciones', function (Blueprint $table) {
            $table->bigInteger('id_grados_secciones', true);
            $table->char('codigo_bachillerato', 2);
            $table->char('codigo_grado', 2);
            $table->char('codigo_seccion', 2);
            $table->char('codigo_ann_lectivo', 4);
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
        Schema::dropIfExists('organizacion_grados_secciones');
    }
}
