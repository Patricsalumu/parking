<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('acces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('reduction')->default(false);
            $table->boolean('antidate')->default(false);
            $table->boolean('modification')->default(false);
            $table->boolean('entree')->default(false);
            $table->boolean('facturation')->default(false);
            $table->boolean('sortie')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('acces');
    }
};
