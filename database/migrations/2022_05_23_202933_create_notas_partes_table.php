<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotasPartesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notas_partes', function (Blueprint $table) {
            $table->bigInteger('id_notas_partes', true);
            $table->decimal('nota_p_p_1', 5)->nullable();
            $table->decimal('nota_p_p_2', 5)->nullable();
            $table->decimal('nota_p_p_3', 5)->nullable();
            $table->decimal('nota_p_p_4', 5)->nullable();
            $table->decimal('nota_final', 5)->nullable();
            $table->char('codigo_asignatura', 2);
            $table->decimal('codigo_alumno', 12, 0);
            $table->decimal('codigo_matricula', 12, 0)->nullable();
            $table->char('codigo_docente', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notas_partes');
    }
}
