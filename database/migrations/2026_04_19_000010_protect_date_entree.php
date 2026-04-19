<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates a BEFORE UPDATE trigger that prevents modifications to date_entree
     */
    public function up()
    {
        // Drop existing trigger if present, then create a trigger that forces NEW.date_entree = OLD.date_entree
        $sql = <<<'SQL'
        DROP TRIGGER IF EXISTS `trg_entrees_prevent_date_entree_update`;
        CREATE TRIGGER `trg_entrees_prevent_date_entree_update`
        BEFORE UPDATE ON `entrees`
        FOR EACH ROW
        BEGIN
            SET NEW.`date_entree` = OLD.`date_entree`;
        END;
        SQL;

        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_entrees_prevent_date_entree_update`;');
    }
};
