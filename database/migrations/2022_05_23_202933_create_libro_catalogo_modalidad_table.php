<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibroCatalogoModalidadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('libro_catalogo_modalidad', function (Blueprint $table) {
            $table->bigInteger('id_modalidad', true);
            $table->char('codigo', 2);
            $table->char('descripcion', 75);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('libro_catalogo_modalidad');
    }
}
