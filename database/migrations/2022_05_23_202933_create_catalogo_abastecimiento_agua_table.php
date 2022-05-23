<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoAbastecimientoAguaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_abastecimiento_agua', function (Blueprint $table) {
            $table->bigInteger('idabastecimiento', true);
            $table->char('codigo', 2);
            $table->char('descripcion', 25);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_abastecimiento_agua');
    }
}
