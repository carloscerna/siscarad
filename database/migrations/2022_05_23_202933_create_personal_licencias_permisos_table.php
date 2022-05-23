<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalLicenciasPermisosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_licencias_permisos', function (Blueprint $table) {
            $table->bigInteger('id_licencia_permiso', true);
            $table->date('fecha');
            $table->decimal('dia', 2, 0)->default(0);
            $table->decimal('hora', 2, 0)->default(0);
            $table->decimal('minutos', 2, 0)->default(0);
            $table->text('observacion')->nullable();
            $table->char('hora_inicio', 5);
            $table->char('hora_fin', 5);
            $table->char('codigo_licencia_permiso', 2);
            $table->char('codigo_contratacion', 2);
            $table->char('codigo_turno', 2);
            $table->decimal('codigo_personal', 2, 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_licencias_permisos');
    }
}
