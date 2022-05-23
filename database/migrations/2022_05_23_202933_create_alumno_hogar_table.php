<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnoHogarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumno_hogar', function (Blueprint $table) {
            $table->bigInteger('idhogar', true);
            $table->char('codigo_nie', 12);
            $table->decimal('cantidad_dormitorios', 2, 0)->nullable()->default(1);
            $table->text('catalogo_hogar')->nullable()->default('01');
            $table->boolean('servicio_energia')->nullable()->default(true);
            $table->char('catalogo_abastecimiento_agua', 2)->nullable()->default('01');
            $table->char('catalogo_material_piso', 2)->nullable();
            $table->char('catalogo_tipo_servicio_sanitario', 2)->nullable()->default('01');
            $table->boolean('conexion_internet')->nullable()->default(false);
            $table->decimal('distancia_centro_educativo', 5)->nullable()->default(1);
            $table->boolean('sintonizar_canal')->nullable()->default(false);
            $table->boolean('sintonizar_franja_educativa')->nullable()->default(false);
            $table->decimal('cantidad_viven_estudiante', 2, 0)->nullable()->default(0);
            $table->boolean('viven_personas_menores')->nullable()->default(false);
            $table->char('catalogo_zona_residencia', 2)->nullable()->default('01');
            $table->decimal('codigo_alumno', 12, 0);
            $table->char('codigo_company', 2)->nullable()->default('07');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumno_hogar');
    }
}
