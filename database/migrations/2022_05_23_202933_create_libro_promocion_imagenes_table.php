<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibroPromocionImagenesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('libro_promocion_imagenes', function (Blueprint $table) {
            $table->bigInteger('id_imagen', true);
            $table->char('codigo_ann_lectivo', 4);
            $table->char('codigo_grado', 2);
            $table->char('codigo_seccion', 2);
            $table->binary('imagen_frente')->nullable();
            $table->binary('imagen_vuelto')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('libro_promocion_imagenes');
    }
}
