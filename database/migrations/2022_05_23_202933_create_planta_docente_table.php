<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlantaDocenteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('planta_docente', function (Blueprint $table) {
            $table->bigInteger('id_docente', true);
            $table->char('nombre_completo', 70);
            $table->char('codigo', 2);
            $table->char('cargo', 25)->nullable();
            $table->char('nip', 10)->nullable();
            $table->char('dui', 10)->nullable();
            $table->char('nit', 20)->nullable();
            $table->boolean('print_or_view')->default(true);
            $table->char('codigo_estatus', 2)->nullable()->default('1');
            $table->text('direccion')->nullable();
            $table->char('telefono', 9)->nullable();
            $table->char('apellidos', 50)->nullable();
            $table->char('nombres', 50)->nullable();

            $table->index(['id_docente'], 'id_docente');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('planta_docente');
    }
}
