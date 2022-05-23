<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nota', function (Blueprint $table) {
            $table->bigInteger('id_notas', true);
            $table->decimal('nota_p_p_1', 5)->nullable()->default(0);
            $table->decimal('nota_p_p_2', 5)->nullable()->default(0);
            $table->decimal('nota_p_p_3', 5)->nullable()->default(0);
            $table->decimal('nota_p_p_4', 5)->nullable()->default(0);
            $table->decimal('nota_final', 5)->nullable()->default(0);
            $table->decimal('codigo_alumno', 12, 0)->nullable();
            $table->decimal('codigo_matricula', 12, 0)->nullable();
            $table->char('codigo_asignatura', 3)->nullable();
            $table->decimal('recuperacion', 5)->nullable()->default(0);
            $table->decimal('nota_paes', 5)->nullable()->default(0);
            $table->boolean('estado')->nullable()->default(false);
            $table->boolean('numero_de_impresiones')->nullable()->default(false);
            $table->decimal('nota_a1_1', 5)->nullable()->default(0);
            $table->decimal('nota_a2_1', 5)->nullable()->default(0);
            $table->decimal('nota_a3_1', 5)->nullable()->default(0);
            $table->decimal('nota_r_1', 5)->nullable()->default(0);
            $table->text('observacion_1')->nullable();
            $table->decimal('nota_a1_2', 5)->nullable()->default(0);
            $table->decimal('nota_a2_2', 5)->nullable()->default(0);
            $table->decimal('nota_a3_2', 5)->nullable()->default(0);
            $table->decimal('nota_r_2', 5)->nullable()->default(0);
            $table->text('observacion_2')->nullable();
            $table->decimal('nota_a1_3', 5)->nullable()->default(0);
            $table->decimal('nota_a2_3', 5)->nullable()->default(0);
            $table->decimal('nota_a3_3', 5)->nullable()->default(0);
            $table->decimal('nota_r_3', 5)->nullable()->default(0);
            $table->text('observacion_3')->nullable();
            $table->decimal('nota_a1_4', 5)->nullable()->default(0);
            $table->decimal('nota_a3_4', 5)->nullable()->default(0);
            $table->decimal('nota_a2_4', 5)->nullable()->default(0);
            $table->decimal('nota_r_4', 5)->nullable()->default(0);
            $table->text('observacion_4')->nullable();
            $table->string('indicador_p_p_1', 2)->nullable();
            $table->string('indicador_p_p_2', 2)->nullable();
            $table->string('indicador_p_p_3', 2)->nullable();
            $table->string('indicador_final', 2)->nullable();
            $table->decimal('nota_p_p_5', 5)->nullable()->default(0);
            $table->decimal('nota_a1_5', 5)->nullable()->default(0);
            $table->decimal('nota_a2_5', 5)->nullable()->default(0);
            $table->decimal('nota_a3_5', 5)->nullable()->default(0);
            $table->decimal('nota_r_5', 5)->nullable()->default(0);
            $table->text('observacion_5')->nullable();
            $table->char('alertas', 10)->nullable();
            $table->decimal('nota_institucional', 5)->nullable()->default(0);
            $table->decimal('nota_recuperacion_2', 5)->nullable()->default(0);
            $table->char('observacion_r1', 25)->nullable();
            $table->char('observacion_r2', 25)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nota');
    }
}
