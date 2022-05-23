<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibroCatalogoAnnLectivoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('libro_catalogo_ann_lectivo', function (Blueprint $table) {
            $table->bigInteger('id_ann_lectivo', true);
            $table->char('codigo', 4);
            $table->char('descripcion', 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('libro_catalogo_ann_lectivo');
    }
}
