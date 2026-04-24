<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('facturations', 'numero')) return;
        Schema::table('facturations', function (Blueprint $table) {
            $table->unique('numero', 'facturations_numero_unique');
        });
    }

    public function down()
    {
        Schema::table('facturations', function (Blueprint $table) {
            $table->dropUnique('facturations_numero_unique');
        });
    }
};
