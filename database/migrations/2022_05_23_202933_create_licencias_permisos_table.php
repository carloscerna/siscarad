<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicenciasPermisosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('licencias_permisos', function (Blueprint $table) {
            $table->bigInteger('id_licencia_permiso', true);
            $table->char('codigo_docente', 2);
            $table->date('fecha');
            $table->char('codigo_contratacion', 2);
            $table->char('codigo_año_lectivo', 4);
            $table->text('observacion')->nullable();
            $table->decimal('dia', 2, 0)->nullable()->default(0);
            $table->decimal('hora', 2, 0)->nullable()->default(0);
            $table->decimal('minutos', 2, 0)->nullable()->default(0);
            $table->char('codigo_licencia_o_permiso', 2);
            $table->char('codigo_turno', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('licencias_permisos');
    }
}
