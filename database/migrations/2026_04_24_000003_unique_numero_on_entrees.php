<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('entrees', 'numero')) return;
        Schema::table('entrees', function (Blueprint $table) {
            $table->unique('numero', 'entrees_numero_unique');
        });
    }

    public function down()
    {
        Schema::table('entrees', function (Blueprint $table) {
            $table->dropUnique('entrees_numero_unique');
        });
    }
};
