<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizarAnnLectivoCiclosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizar_ann_lectivo_ciclos', function (Blueprint $table) {
            $table->bigInteger('id_organizar_ann_lectivo_ciclos', true);
            $table->char('codigo_ann_lectivo', 4)->nullable();
            $table->char('codigo_bachillerato', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organizar_ann_lectivo_ciclos');
    }
}
