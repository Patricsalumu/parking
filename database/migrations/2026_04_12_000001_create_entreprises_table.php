<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('entreprises', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('devise')->default('USD');
            $table->decimal('taux_change', 10, 2)->nullable();
            $table->string('slogan')->nullable();
            $table->string('telephone')->nullable();
            $table->string('logo')->nullable();
            $table->string('rccm')->nullable();
            $table->string('id_nat')->nullable();
            $table->string('num_impot')->nullable();
            $table->text('adresse')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('entreprises');
    }
};
