<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibroPromocionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('libro_promocion', function (Blueprint $table) {
            $table->bigInteger('id_libro', true);
            $table->char('codigo_ann_lectivo', 4);
            $table->char('codigo_modalidad', 2)->nullable();
            $table->char('codigo_grado', 2);
            $table->char('codigo_seccion', 2)->nullable();
            $table->char('codigo_turno', 2)->nullable();
            $table->char('apellido_paterno', 45)->nullable();
            $table->char('apellido_materno', 45);
            $table->char('nombres', 50);
            $table->char('codigo_institucion', 12)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('libro_promocion');
    }
}
