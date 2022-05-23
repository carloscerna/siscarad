<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalSalarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_salario', function (Blueprint $table) {
            $table->bigInteger('id_personal_salario', true);
            $table->char('codigo_tipo_contratacion', 2);
            $table->char('codigo_tipo_descuento', 2);
            $table->decimal('salario', 7);
            $table->char('codigo_rubro', 2)->nullable();
            $table->decimal('codigo_personal', 2, 0);
            $table->char('codigo_turno', 2)->nullable();
            $table->char('codigo_horario', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_salario');
    }
}
