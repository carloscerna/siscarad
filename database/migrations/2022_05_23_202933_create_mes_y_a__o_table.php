<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMesYAOTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mes_y_año', function (Blueprint $table) {
            $table->decimal('dia_y_año', 4, 0);
            $table->char('dias', 15)->nullable();
            $table->char('año', 50)->nullable();
            $table->char('mes', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mes_y_año');
    }
}
