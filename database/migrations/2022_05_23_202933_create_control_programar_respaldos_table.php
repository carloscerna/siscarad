<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateControlProgramarRespaldosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('control_programar_respaldos', function (Blueprint $table) {
            $table->bigInteger('id_programar_respaldos', true);
            $table->char('hora_inicio', 8);
            $table->char('hora_fin', 8);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('control_programar_respaldos');
    }
}
