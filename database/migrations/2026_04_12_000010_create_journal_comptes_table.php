<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('journal_comptes', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->decimal('montant', 10, 2);
            $table->date('date');
            $table->foreignId('compte_debit_id')->constrained('comptes');
            $table->foreignId('compte_credit_id')->constrained('comptes');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('journal_comptes');
    }
};
