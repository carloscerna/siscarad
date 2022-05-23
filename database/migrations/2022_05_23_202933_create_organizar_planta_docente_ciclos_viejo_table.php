<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizarPlantaDocenteCiclosViejoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizar_planta_docente_ciclos_viejo', function (Blueprint $table) {
            $table->bigInteger('id_organizar_planta_docente_ciclos', true);
            $table->char('codigo_bachillerato', 2);
            $table->char('codigo_ann_lectivo', 4);
            $table->char('codigo_turno', 2);
            $table->char('codigo_docente', 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organizar_planta_docente_ciclos_viejo');
    }
}
