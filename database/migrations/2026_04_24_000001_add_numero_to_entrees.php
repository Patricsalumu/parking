<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('entrees', function (Blueprint $table) {
            $table->unsignedInteger('numero')->nullable()->after('id')->index();
        });

        // populate existing records with sequential numbers based on id order
        $rows = DB::table('entrees')->orderBy('id')->get();
        $n = 0;
        foreach ($rows as $r) {
            $n++;
            DB::table('entrees')->where('id', $r->id)->update(['numero' => $n]);
        }
    }

    public function down()
    {
        Schema::table('entrees', function (Blueprint $table) {
            $table->dropColumn('numero');
        });
    }
};
