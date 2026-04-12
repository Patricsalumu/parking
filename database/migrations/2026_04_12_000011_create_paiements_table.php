<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facturation_id')->constrained('facturations')->cascadeOnDelete();
            $table->decimal('montant', 10, 2);
            $table->timestamp('date_paiement')->nullable();
            $table->string('mode')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('paiements');
    }
};
