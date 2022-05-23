<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoFamiliarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_familiar', function (Blueprint $table) {
            $table->bigInteger('id_familiar', true);
            $table->char('codigo', 2);
            $table->char('descripcion', 12);

            $table->primary(['id_familiar', 'codigo', 'descripcion']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_familiar');
    }
}
