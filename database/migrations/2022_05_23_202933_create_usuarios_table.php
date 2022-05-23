<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->bigInteger('id_usuario', true);
            $table->char('nombre', 20);
            $table->char('password', 20);
            $table->char('codigo_perfil', 2);
            $table->char('base_de_datos', 50);
            $table->char('codigo_escuela', 10);
            $table->decimal('codigo_personal', 2, 0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
}
