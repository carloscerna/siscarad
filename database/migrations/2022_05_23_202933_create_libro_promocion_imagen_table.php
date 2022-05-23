<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibroPromocionImagenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('libro_promocion_imagen', function (Blueprint $table) {
            $table->bigInteger('id_libro_imagen', true);
            $table->char('codigo_ann_lectivo', 4);
            $table->char('codigo_grado', 2);
            $table->char('codigo_seccion', 2);
            $table->char('codigo_modalidad', 2);
            $table->char('codigo_institucion', 6)->nullable();
            $table->char('imagen_frente', 255)->nullable();
            $table->char('imagen_vuelto', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('libro_promocion_imagen');
    }
}
