<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoEscuelasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_escuelas', function (Blueprint $table) {
            $table->char('codigo', 6)->nullable();
            $table->char('nombre', 99)->nullable();
            $table->char('sector', 11)->nullable();
            $table->char('codigo_departamento', 2)->nullable();
            $table->char('nombre_departamento', 12)->nullable();
            $table->char('codigo_municipio', 2)->nullable();
            $table->char('municipio', 25)->nullable();
            $table->bigInteger('id_nombre_escuelas', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_escuelas');
    }
}
