<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entrees', function (Blueprint $table) {
            if (!Schema::hasColumn('entrees', 'sortie')) {
                $table->boolean('sortie')->default(false)->after('date_sortie');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entrees', function (Blueprint $table) {
            if (Schema::hasColumn('entrees', 'sortie')) {
                $table->dropColumn('sortie');
            }
        });
    }
};
