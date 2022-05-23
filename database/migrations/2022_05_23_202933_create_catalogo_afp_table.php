<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoAfpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_afp', function (Blueprint $table) {
            $table->bigInteger('id_afp', true);
            $table->char('codigo', 2)->nullable();
            $table->char('descripcion', 15)->nullable();
            $table->decimal('porcentaje', 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_afp');
    }
}
