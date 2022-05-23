<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->char('apellidos', 30);
            $table->char('nombres', 30);
            $table->text('direccion')->nullable();
            $table->char('telefono_residencia', 9)->nullable();
            $table->char('codigo_cargo', 2);
            $table->char('dui', 10)->nullable();
            $table->char('nit', 20)->nullable();
            $table->char('nip', 10)->nullable();
            $table->char('codigo_especialidad', 2)->nullable();
            $table->decimal('edad', 2, 0)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->char('codigo_zona_residencia', 2)->nullable();
            $table->char('codigo_departamento', 2)->nullable();
            $table->char('codigo_municipio', 2)->nullable();
            $table->char('codigo_genero', 2)->nullable();
            $table->char('telefono_celular', 9)->nullable();
            $table->char('codigo_estatus', 2)->nullable();
            $table->bigInteger('id_personal', true);
            $table->char('codigo_estado_civil', 2)->nullable();
            $table->text('foto')->nullable();
            $table->decimal('tiempo_servicio', 2, 0)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->char('tipo_sangre', 10)->nullable();
            $table->date('fecha_retiro')->nullable();
            $table->char('correo_electronico', 100)->nullable();
            $table->char('codigo_estudio', 2)->nullable()->default('01');
            $table->char('codigo_vivienda', 2)->nullable()->default('01');
            $table->char('codigo_afp', 2)->nullable()->default('01');
            $table->char('nombre_conyuge', 100)->nullable();
            $table->char('numero_cuenta', 25)->nullable();
            $table->char('codigo_tipo_licencia', 25)->nullable();
            $table->char('licencia', 25)->nullable();
            $table->char('isss', 25)->nullable();
            $table->char('afp', 25)->nullable();
            $table->text('comentario')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal');
    }
}
