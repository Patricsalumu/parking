<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('facturations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('categorie_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('facturations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
