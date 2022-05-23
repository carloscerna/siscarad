<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnoMatriculaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumno_matricula', function (Blueprint $table) {
            $table->char('codigo_bach_o_ciclo', 2)->nullable();
            $table->char('codigo_ann_lectivo', 4)->nullable();
            $table->char('codigo_grado', 2)->nullable();
            $table->char('codigo_seccion', 2)->nullable();
            $table->bigInteger('id_alumno_matricula', true);
            $table->decimal('codigo_alumno', 12, 0);
            $table->boolean('retirado')->nullable()->default(false);
            $table->boolean('repitente')->nullable()->default(false);
            $table->boolean('sobreedad')->nullable()->default(false);
            $table->boolean('nuevo_ingreso')->nullable()->default(false);
            $table->char('codigo_turno', 2)->nullable()->default('01');
            $table->boolean('pn')->nullable()->default(true);
            $table->boolean('certificado')->nullable()->default(true);
            $table->boolean('tarjeta_vacunacion')->nullable()->default(false);
            $table->boolean('imprimir_foto')->nullable()->default(false);
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_retiro')->nullable();
            $table->text('observaciones')->nullable();
            $table->char('codigo_institucion', 6)->nullable();
            $table->char('codigo_estatus', 2)->nullable();
            $table->char('telefono_celular', 9)->nullable();
            $table->decimal('ann_anterior', 4, 0)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumno_matricula');
    }
}
