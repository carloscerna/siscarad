<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInformacionInstitucionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('informacion_institucion', function (Blueprint $table) {
            $table->char('nombre_director', 2);
            $table->char('codigo_institucion', 10);
            $table->char('nombre_institucion', 60);
            $table->text('direccion_institucion')->nullable();
            $table->char('codigo_municipio', 2)->nullable();
            $table->char('codigo_departamento', 2)->nullable();
            $table->char('telefax', 9)->nullable();
            $table->char('telefono_uno', 9)->nullable();
            $table->text('logo_uno')->nullable();
            $table->text('logo_dos')->nullable();
            $table->text('logo_tres')->nullable();
            $table->char('encargada_registro_academico', 2)->nullable();
            $table->char('subvespertino', 2)->nullable();
            $table->char('submatutino', 2)->nullable();
            $table->bigInteger('id_institucion', true);
            $table->char('sector', 15)->nullable();
            $table->char('jornada', 15)->nullable();
            $table->text('numero_acuerdo')->nullable();
            $table->char('dbname', 60)->nullable();
            $table->char('se_extiende_la_presente', 50)->nullable();
            $table->char('dia_entrega', 25)->nullable();
            $table->text('imagen_firma')->nullable()->comment('Almacenar url de la imagen correspondiente a la firma.');
            $table->text('imagen_sello')->nullable()->comment('Almacenar imagen correspondiente al sello.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('informacion_institucion');
    }
}
