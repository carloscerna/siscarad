<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRutaPnCertificadoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ruta_pn_certificado', function (Blueprint $table) {
            $table->bigInteger('id_foto_certificado', true);
            $table->decimal('codigo_alumno', 12, 0)->nullable();
            $table->decimal('codigo_matricula', 12, 0)->nullable();
            $table->text('ruta_foto')->nullable();
            $table->text('ruta_certificado')->nullable();
            $table->char('año_lectivo', 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ruta_pn_certificado');
    }
}
