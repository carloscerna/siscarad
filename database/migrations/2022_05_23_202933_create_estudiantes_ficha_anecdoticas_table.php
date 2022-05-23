<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstudiantesFichaAnecdoticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estudiantes_ficha_anecdoticas', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('nie', 10);
            $table->date('fecha');
            $table->text('descripcion');
            $table->string('duidocente', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estudiantes_ficha_anecdoticas');
    }
}
