<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoLicenciaOPermisoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_licencia_o_permiso', function (Blueprint $table) {
            $table->bigInteger('id_tipo_licencia_o_permiso', true);
            $table->char('codigo', 2);
            $table->char('nombre', 100);
            $table->decimal('saldo', 3, 0)->nullable();
            $table->decimal('minutos', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipo_licencia_o_permiso');
    }
}
