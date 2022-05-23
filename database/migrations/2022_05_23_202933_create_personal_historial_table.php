<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalHistorialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_historial', function (Blueprint $table) {
            $table->bigInteger('id_', true);
            $table->date('fecha')->nullable();
            $table->text('descripcion')->nullable();
            $table->bigInteger('id_personal')->nullable();
            $table->char('titulo', 25)->nullable()->default('Otros');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_historial');
    }
}
