<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoNacionalidadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_nacionalidad', function (Blueprint $table) {
            $table->bigInteger('id_nacionalidad', true);
            $table->char('codigo', 2);
            $table->char('descripcion', 20);
            $table->bigInteger('id')->nullable();
            $table->timestampTz('timestamps')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_nacionalidad');
    }
}
