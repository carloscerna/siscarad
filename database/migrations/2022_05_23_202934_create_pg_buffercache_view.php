<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreatePgBuffercacheView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE VIEW \"pg_buffercache\" AS  SELECT p.bufferid,
    p.relfilenode,
    p.reltablespace,
    p.reldatabase,
    p.relblocknumber,
    p.isdirty,
    p.usagecount
   FROM pg_buffercache_pages() p(bufferid integer, relfilenode oid, reltablespace oid, reldatabase oid, relblocknumber bigint, isdirty boolean, usagecount smallint);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS \"pg_buffercache\"");
    }
}
