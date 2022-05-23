<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAAABachOCicloTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('a_a_a_bach_o_ciclo', function (Blueprint $table) {
            $table->char('codigo_asignacion', 2);
            $table->char('codigo_bach_o_ciclo', 2);
            $table->char('codigo_asignatura', 3);
            $table->char('codigo_ann_lectivo', 4);
            $table->bigInteger('id_asignacion', true);
            $table->char('codigo_sirai', 8)->nullable();
            $table->char('codigo_grado', 2)->nullable();
            $table->decimal('orden', 2, 0)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('a_a_a_bach_o_ciclo');
    }
}
