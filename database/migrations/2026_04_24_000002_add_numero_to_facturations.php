<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('facturations', function (Blueprint $table) {
            $table->unsignedInteger('numero')->nullable()->after('id')->index();
        });

        // populate existing facturations sequentially
        $rows = DB::table('facturations')->orderBy('id')->get();
        $n = 0;
        foreach ($rows as $r) {
            $n++;
            DB::table('facturations')->where('id', $r->id)->update(['numero' => $n]);
        }
    }

    public function down()
    {
        Schema::table('facturations', function (Blueprint $table) {
            $table->dropColumn('numero');
        });
    }
};
