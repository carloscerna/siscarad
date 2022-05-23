<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCargaDocenteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carga_docente', function (Blueprint $table) {
            $table->char('codigo_asignatura', 3);
            $table->char('codigo_bachillerato', 2);
            $table->char('codigo_grado', 2);
            $table->char('codigo_seccion', 2);
            $table->char('codigo_ann_lectivo', 2);
            $table->decimal('codigo_docente', 2, 0);
            $table->char('codigo_turno', 2)->nullable();
            $table->bigInteger('id_carga_docente', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carga_docente');
    }
}
