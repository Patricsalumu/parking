<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('facturations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entree_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categorie_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('montant_total', 10, 2);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->integer('duree')->nullable();
            $table->decimal('reduction', 10, 2)->default(0);
            $table->timestamp('date_paiement')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('facturations');
    }
};
