<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnLectivoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ann_lectivo', function (Blueprint $table) {
            $table->bigInteger('id_annlectivo', true);
            $table->decimal('nombre', 4, 0)->index('id_año_lectivo');
            $table->char('codigo', 4);
            $table->char('descripcion', 30)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('estatus')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ann_lectivo');
    }
}
