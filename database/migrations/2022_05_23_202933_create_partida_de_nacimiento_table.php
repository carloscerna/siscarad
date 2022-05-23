<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartidaDeNacimientoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partida_de_nacimiento', function (Blueprint $table) {
            $table->char('nombre_completo', 70)->nullable();
            $table->text('imagen_frente')->nullable();
            $table->text('imagen_vuelto')->nullable();
            $table->date('fecha')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partida_de_nacimiento');
    }
}
