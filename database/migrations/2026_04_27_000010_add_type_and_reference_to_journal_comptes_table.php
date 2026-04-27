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
        Schema::table('journal_comptes', function (Blueprint $table) {
            $table->string('type')->nullable()->after('libelle')->comment('Banques, caisses, ventes, achat, OD');
            $table->string('reference')->nullable()->after('type')->comment('Référence (numéro facture, référence d\'achat, etc.)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('journal_comptes', function (Blueprint $table) {
            $table->dropColumn(['type','reference']);
        });
    }
};
