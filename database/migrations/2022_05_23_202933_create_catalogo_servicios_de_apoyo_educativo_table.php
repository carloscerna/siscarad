<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoServiciosDeApoyoEducativoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_servicios_de_apoyo_educativo', function (Blueprint $table) {
            $table->bigInteger('id_apoyo_educativo', true);
            $table->char('codigo', 2)->default('0');
            $table->char('nombre', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_servicios_de_apoyo_educativo');
    }
}
