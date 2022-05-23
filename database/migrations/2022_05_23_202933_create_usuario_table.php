<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->char('nombre', 40)->nullable();
            $table->char('password', 20)->nullable();
            $table->char('otros', 10)->nullable();
            $table->decimal('codigo', 1, 0)->nullable();
            $table->decimal('paes', 5)->nullable();
            $table->decimal('institucion', 5)->nullable();
            $table->bigInteger('id_usuario', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuario');
    }
}
