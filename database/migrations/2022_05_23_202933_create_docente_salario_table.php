<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocenteSalarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docente_salario', function (Blueprint $table) {
            $table->bigInteger('id_docente_salario', true);
            $table->char('codigo_docente', 2);
            $table->char('codigo_tipo_contratacion', 2);
            $table->decimal('salario', 9)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('docente_salario');
    }
}
