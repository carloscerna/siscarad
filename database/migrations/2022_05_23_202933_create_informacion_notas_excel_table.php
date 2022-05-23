<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInformacionNotasExcelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('informacion_notas_excel', function (Blueprint $table) {
            $table->bigInteger('id_informacion_notas_excel', true);
            $table->text('url_archivo_excel');
            $table->char('codigo_año_lectivo', 4);
            $table->char('codigo_bachillerato', 2);
            $table->char('codigo_grado', 2);
            $table->char('codigo_seccion', 2);
            $table->char('codigo_asignatura', 2);
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
        Schema::dropIfExists('informacion_notas_excel');
    }
}
