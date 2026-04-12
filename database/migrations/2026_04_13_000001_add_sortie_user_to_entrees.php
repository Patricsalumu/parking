<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('entrees', function (Blueprint $table) {
            $table->unsignedBigInteger('sortie_user_id')->nullable()->after('user_id');
            $table->foreign('sortie_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('entrees', function (Blueprint $table) {
            $table->dropForeign(['sortie_user_id']);
            $table->dropColumn('sortie_user_id');
        });
    }
};
