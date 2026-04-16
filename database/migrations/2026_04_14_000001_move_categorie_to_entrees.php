<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // add categorie_id to entrees
        Schema::table('entrees', function (Blueprint $table) {
            if (!Schema::hasColumn('entrees', 'categorie_id')) {
                $table->unsignedBigInteger('categorie_id')->nullable()->after('client_id');
            }
        });

        // migrate existing data from facturations to entrees when linked
        try {
            DB::statement('UPDATE entrees e JOIN facturations f ON f.entree_id = e.id SET e.categorie_id = f.categorie_id WHERE f.categorie_id IS NOT NULL');
        } catch (\Exception $e) {
            // some DB drivers (sqlite) may not support JOIN in UPDATE - fallback to PHP loop
            try {
                $rows = DB::table('facturations')->whereNotNull('categorie_id')->get(['entree_id','categorie_id']);
                foreach ($rows as $r) {
                    DB::table('entrees')->where('id', $r->entree_id)->update(['categorie_id' => $r->categorie_id]);
                }
            } catch (\Exception $e2) {
                // log and continue
                \Log::error('Categorie migration to entrees failed: '.$e2->getMessage());
            }
        }

        // drop categorie_id from facturations
        Schema::table('facturations', function (Blueprint $table) {
            if (Schema::hasColumn('facturations','categorie_id')) {
                // drop foreign key if exists
                try { $table->dropForeign(['categorie_id']); } catch (\Exception $_) { }
                try { $table->dropColumn('categorie_id'); } catch (\Exception $_) { }
            }
        });
    }

    public function down()
    {
        // restore categorie_id on facturations
        Schema::table('facturations', function (Blueprint $table) {
            if (!Schema::hasColumn('facturations','categorie_id')) {
                $table->unsignedBigInteger('categorie_id')->nullable()->after('entree_id');
                $table->foreign('categorie_id')->references('id')->on('categories')->nullOnDelete();
            }
        });

        // move data back from entrees to facturations where possible
        try {
            DB::statement('UPDATE facturations f JOIN entrees e ON f.entree_id = e.id SET f.categorie_id = e.categorie_id WHERE e.categorie_id IS NOT NULL');
        } catch (\Exception $e) {
            try {
                $rows = DB::table('entrees')->whereNotNull('categorie_id')->get(['id','categorie_id']);
                foreach ($rows as $r) {
                    DB::table('facturations')->where('entree_id', $r->id)->update(['categorie_id' => $r->categorie_id]);
                }
            } catch (\Exception $e2) {
                \Log::error('Categorie rollback migration failed: '.$e2->getMessage());
            }
        }

        // drop categorie_id from entrees
        Schema::table('entrees', function (Blueprint $table) {
            if (Schema::hasColumn('entrees','categorie_id')) {
                try { $table->dropForeign(['categorie_id']); } catch (\Exception $_) { }
                try { $table->dropColumn('categorie_id'); } catch (\Exception $_) { }
            }
        });
    }
};
