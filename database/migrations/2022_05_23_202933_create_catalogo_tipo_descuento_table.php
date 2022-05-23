<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoTipoDescuentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_tipo_descuento', function (Blueprint $table) {
            $table->bigInteger('id_tipo_descuento', true);
            $table->char('codigo', 2);
            $table->char('descripcion', 25);
            $table->decimal('porcentaje', 5)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_tipo_descuento');
    }
}
