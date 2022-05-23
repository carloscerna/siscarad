<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoTipoServicioSanitarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_tipo_servicio_sanitario', function (Blueprint $table) {
            $table->bigInteger('idtiposanitario', true);
            $table->char('codigo', 2);
            $table->char('descripcion', 35);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_tipo_servicio_sanitario');
    }
}
