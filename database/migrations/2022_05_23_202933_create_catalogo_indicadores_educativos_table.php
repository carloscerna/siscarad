<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoIndicadoresEducativosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_indicadores_educativos', function (Blueprint $table) {
            $table->bigInteger('id_indicadores_educativos', true);
            $table->char('codigo', 2);
            $table->char('descripcion', 16);

            $table->primary(['id_indicadores_educativos', 'codigo', 'descripcion']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_indicadores_educativos');
    }
}
