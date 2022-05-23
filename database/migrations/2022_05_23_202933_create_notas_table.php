<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->char('nombre_alumno', 30)->nullable();
            $table->char('apellido_alumno', 30)->nullable();
            $table->char('nombre_completo', 60)->nullable();
            $table->char('bachillerato', 70)->nullable();
            $table->char('año', 15)->nullable();
            $table->char('materia', 70)->nullable();
            $table->decimal('nota_1_p_1_uno', 5)->nullable()->default(0);
            $table->decimal('nota_1_p_1_dos', 5)->nullable();
            $table->decimal('nota_1_p_1_tres', 5)->nullable();
            $table->decimal('nota_1_p_1', 5)->nullable();
            $table->decimal('nota_2_p_1_auto', 5)->nullable();
            $table->decimal('nota_2_p_1_hetero', 5)->nullable();
            $table->decimal('nota_2_p_1', 5)->nullable();
            $table->decimal('nota_3_p_1_examen', 5)->nullable();
            $table->decimal('nota_3_p_1', 5)->nullable();
            $table->decimal('nota_p_p_1', 5)->nullable();
            $table->char('concepto_1', 2)->nullable();
            $table->char('resultado_1', 10)->nullable();
            $table->char('observacion_1', 100)->nullable();
            $table->decimal('nota_1_p_2_uno', 5)->nullable();
            $table->decimal('nota_1_p_2_dos', 5)->nullable();
            $table->decimal('nota_1_p_2_tres', 5)->nullable();
            $table->decimal('nota_1_p_2', 5)->nullable();
            $table->decimal('nota_2_p_2_auto', 5)->nullable();
            $table->decimal('nota_2_p_2_hetero', 5)->nullable();
            $table->decimal('nota_2_p_2', 5)->nullable();
            $table->decimal('nota_3_p_2_examen', 5)->nullable();
            $table->decimal('nota_3_p_2', 5)->nullable();
            $table->decimal('nota_p_p_2', 5)->nullable();
            $table->char('concepto_2', 2)->nullable();
            $table->char('resultado_2', 10)->nullable();
            $table->char('observacion_2', 100)->nullable();
            $table->decimal('nota_1_p_3_uno', 5)->nullable();
            $table->decimal('nota_1_p_3_dos', 5)->nullable();
            $table->decimal('nota_1_p_3_tres', 5)->nullable();
            $table->decimal('nota_1_p_3', 5)->nullable();
            $table->decimal('nota_2_p_3_auto', 5)->nullable();
            $table->decimal('nota_2_p_3_hetero', 5)->nullable();
            $table->decimal('nota_2_p_3', 5)->nullable();
            $table->decimal('nota_3_p_3_examen', 5)->nullable();
            $table->decimal('nota_3_p_3', 5)->nullable();
            $table->decimal('nota_p_p_3', 5)->nullable();
            $table->char('concepto_3', 2)->nullable();
            $table->char('resultado_3', 10)->nullable();
            $table->char('observacion_3', 100)->nullable();
            $table->decimal('nota_1_p_4_uno', 5)->nullable();
            $table->decimal('nota_1_p_4_dos', 5)->nullable();
            $table->decimal('nota_1_p_4_tres', 5)->nullable();
            $table->decimal('nota_1_p_4', 5)->nullable();
            $table->decimal('nota_2_p_4_auto', 5)->nullable();
            $table->decimal('nota_2_p_4_hetero', 5)->nullable();
            $table->decimal('nota_2_p_4', 5)->nullable();
            $table->decimal('nota_3_p_4_examen', 5)->nullable();
            $table->decimal('nota_3_p_4', 5)->nullable();
            $table->decimal('nota_p_p_4', 5)->nullable();
            $table->char('concepto_4', 2)->nullable();
            $table->char('resultado_4', 10)->nullable();
            $table->char('observacion_4', 100)->nullable();
            $table->decimal('nota_final', 5)->nullable();
            $table->char('concepto', 2)->nullable();
            $table->char('resultado', 10)->nullable();
            $table->char('observacion', 100)->nullable();
            $table->char('recuperacion', 15)->nullable();
            $table->char('asignatura_recuperacion', 30)->nullable();
            $table->decimal('nota_r_1', 4)->nullable();
            $table->char('resultados_recuperacion', 15)->nullable();
            $table->char('pendiente_nopendiente', 15)->nullable();
            $table->char('asignatura_pendiente', 30)->nullable();
            $table->decimal('n_p_1', 4)->nullable();
            $table->char('n_p_1_r', 15)->nullable();
            $table->decimal('n_p_2', 4)->nullable();
            $table->char('n_p_2_r', 15)->nullable();
            $table->decimal('n_p_3', 4)->nullable();
            $table->char('n_p_3_r', 15)->nullable();
            $table->decimal('n_p_4', 4)->nullable();
            $table->char('n_p_4_r', 15)->nullable();
            $table->decimal('n_p_f', 4)->nullable();
            $table->char('n_p_f_r', 15)->nullable();
            $table->char('n_p_f_concepto', 2)->nullable();
            $table->decimal('n_p_r', 4)->nullable();
            $table->char('n_p_r_r', 15)->nullable();
            $table->char('n_p_r_concepto', 2)->nullable();
            $table->date('fecha')->nullable();
            $table->char('de_concepto_o_calificacion', 15)->nullable();
            $table->decimal('horas_semanales', 2, 0)->nullable();
            $table->char('area', 12)->nullable();
            $table->char('excepciones', 1)->nullable();
            $table->decimal('nota_paes', 5)->nullable();
            $table->decimal('año_lectivo', 4, 0)->nullable();
            $table->char('imprimir_en_certificado', 1)->nullable();
            $table->decimal('id_notas', 3, 1)->nullable();
            $table->decimal('id_inscripcion', 6, 0)->nullable();
            $table->decimal('nota_1_p_5', 5)->nullable();
            $table->decimal('nota_2_p_5', 5)->nullable();
            $table->decimal('nota_3_p_5', 5)->nullable();
            $table->decimal('nota_p_p_5', 5)->nullable();
            $table->char('resultado_5', 10)->nullable();
            $table->char('concepto_5', 2)->nullable();
            $table->decimal('nota_1_p_6', 5)->nullable();
            $table->decimal('nota_2_p_6', 5)->nullable();
            $table->decimal('nota_3_p_6', 5)->nullable();
            $table->decimal('nota_p_p_6', 5)->nullable();
            $table->char('resultado_6', 10)->nullable();
            $table->char('concepto_6', 2)->nullable();
            $table->char('observacion_5', 100)->nullable();
            $table->char('observacion_6', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notas');
    }
}
