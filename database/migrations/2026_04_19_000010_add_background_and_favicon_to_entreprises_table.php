<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('entreprises', function (Blueprint $table) {
            if (!Schema::hasColumn('entreprises','background')) {
                $table->string('background')->nullable()->after('logo');
            }
            if (!Schema::hasColumn('entreprises','favicon')) {
                $table->string('favicon')->nullable()->after('background');
            }
        });
    }

    public function down()
    {
        Schema::table('entreprises', function (Blueprint $table) {
            if (Schema::hasColumn('entreprises','favicon')) {
                $table->dropColumn('favicon');
            }
            if (Schema::hasColumn('entreprises','background')) {
                $table->dropColumn('background');
            }
        });
    }
};
