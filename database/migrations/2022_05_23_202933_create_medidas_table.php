<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedidasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medidas', function (Blueprint $table) {
            $table->bigInteger('id_medidas', true);
            $table->bigInteger('printer');
            $table->char('codigo_modalidad', 2);
            $table->float('fila')->nullable()->default(0);
            $table->float('columna')->nullable()->default(0);
            $table->char('descripcion', 45)->nullable();
            $table->char('codigo_fuente', 2)->nullable();
            $table->char('codigo_estilo', 2)->nullable();
            $table->char('codigo_tamano', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medidas');
    }
}
