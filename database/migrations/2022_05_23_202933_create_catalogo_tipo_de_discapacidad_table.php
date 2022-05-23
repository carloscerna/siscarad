<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoTipoDeDiscapacidadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_tipo_de_discapacidad', function (Blueprint $table) {
            $table->bigInteger('id_tipo_discapacidad', true);
            $table->char('codigo', 2);
            $table->char('nombre', 75);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_tipo_de_discapacidad');
    }
}
