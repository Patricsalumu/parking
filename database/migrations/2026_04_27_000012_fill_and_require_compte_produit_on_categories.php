<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // find product account 701000
        $produit = DB::table('comptes')->where('numero','701000')->first();
        if ($produit) {
            // assign to categories without compte_produit_id
            DB::table('categories')->whereNull('compte_produit_id')->update(['compte_produit_id' => $produit->id]);
        }

        // drop existing foreign key constraint on compte_produit_id if exists (MySQL)
        try {
            $rows = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'compte_produit_id' AND REFERENCED_TABLE_NAME = 'comptes'");
            foreach ($rows as $r) {
                $fk = $r->CONSTRAINT_NAME ?? $r->constraint_name ?? null;
                if ($fk) {
                    DB::statement("ALTER TABLE `categories` DROP FOREIGN KEY `".$fk."`");
                }
            }
        } catch (\Exception $e) {
            // ignore if not MySQL or other error
        }

        // modify column to NOT NULL (use raw SQL for MySQL)
        try {
            DB::statement('ALTER TABLE `categories` MODIFY `compte_produit_id` BIGINT UNSIGNED NOT NULL');
        } catch (\Exception $e) {
            // if statement fails (other driver), try fluent change()
            try {
                Schema::table('categories', function (Blueprint $table) {
                    $table->unsignedBigInteger('compte_produit_id')->nullable(false)->change();
                });
            } catch (\Exception $e) {
                // last resort: ignore and let developer handle
            }
        }

        // add foreign key constraint
        try {
            DB::statement('ALTER TABLE `categories` ADD CONSTRAINT `categories_compte_produit_id_foreign` FOREIGN KEY (`compte_produit_id`) REFERENCES `comptes`(`id`) ON DELETE RESTRICT');
        } catch (\Exception $e) {
            // ignore if fails
        }
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            // drop foreign
            if (Schema::hasColumn('categories','compte_produit_id')) {
                $table->dropForeign(['compte_produit_id']);
                $table->unsignedBigInteger('compte_produit_id')->nullable()->change();
            }
        });
    }
};
