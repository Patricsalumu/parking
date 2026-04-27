<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // If any categories have null compte_produit_id, assign them to legacy product account 701000 when available
        $prod = DB::table('comptes')->where('numero','701000')->first();
        if ($prod) {
            DB::table('categories')->whereNull('compte_produit_id')->update(['compte_produit_id' => $prod->id]);
        }

        // Alter column to NOT NULL. Use raw statement for compatibility.
        // Note: on some setups you may need doctrine/dbal for Schema::table()->change().
        DB::statement('ALTER TABLE categories MODIFY compte_produit_id BIGINT UNSIGNED NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE categories MODIFY compte_produit_id BIGINT UNSIGNED NULL');
    }
};
