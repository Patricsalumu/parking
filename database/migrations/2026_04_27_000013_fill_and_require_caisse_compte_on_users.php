<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // try to find a caisse account by name or number pattern
        $caisse = DB::table('comptes')->whereRaw("LOWER(nom) LIKE '%caisse%'")->first();
        if (!$caisse) {
            // fallback: try numero starting with 53 (common caisse numbering)
            $caisse = DB::table('comptes')->where('numero','like','53%')->first();
        }
        if (!$caisse) {
            // last resort: pick first compte
            $caisse = DB::table('comptes')->first();
        }

        if ($caisse) {
            DB::table('users')->whereNull('caisse_compte_id')->update(['caisse_compte_id' => $caisse->id]);
        }

        // drop existing foreign key on users.caisse_compte_id if exists
        try {
            $rows = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'caisse_compte_id' AND REFERENCED_TABLE_NAME = 'comptes'");
            foreach ($rows as $r) {
                $fk = $r->CONSTRAINT_NAME ?? $r->constraint_name ?? null;
                if ($fk) {
                    DB::statement("ALTER TABLE `users` DROP FOREIGN KEY `".$fk."`");
                }
            }
        } catch (\Exception $e) {
            // ignore
        }

        try {
            DB::statement('ALTER TABLE `users` MODIFY `caisse_compte_id` BIGINT UNSIGNED NOT NULL');
        } catch (\Exception $e) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->unsignedBigInteger('caisse_compte_id')->nullable(false)->change();
                });
            } catch (\Exception $e) {
                // ignore
            }
        }

        try {
            DB::statement('ALTER TABLE `users` ADD CONSTRAINT `users_caisse_compte_id_foreign` FOREIGN KEY (`caisse_compte_id`) REFERENCES `comptes`(`id`) ON DELETE RESTRICT');
        } catch (\Exception $e) {
            // ignore
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users','caisse_compte_id')) {
                $table->dropForeign(['caisse_compte_id']);
                $table->unsignedBigInteger('caisse_compte_id')->nullable()->change();
            }
        });
    }
};
