<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Facturation;
use App\Models\Entree;
use App\Models\Categorie;
use Carbon\Carbon;

class FacturationCalculationTest extends TestCase
{
    /** @test */
    public function price_for_zero_hours_counts_as_one_day()
    {
        $cat = new Categorie();
        $cat->id = 3;
        $cat->prix_par_24h = 10;

        $entree = new Entree();
        $entree->date_entree = Carbon::now();
        $entree->date_sortie = Carbon::now(); // 0h

        $fact = new Facturation();
        $fact->setRelation('entree', $entree);
        $fact->setRelation('categorie', $cat);

        $res = $fact->calculateFromEntree();
        $this->assertNotNull($res);
        $this->assertEquals(10, $res->montant_total);
    }

    /** @test */
    public function twenty_six_hours_charges_one_full_plus_half_last_day()
    {
        $cat = new Categorie();
        $cat->id = 3;
        $cat->prix_par_24h = 10;

        $entree = new Entree();
        $entree->date_entree = Carbon::now()->subHours(26);
        $entree->date_sortie = Carbon::now();

        $fact = new Facturation();
        $fact->setRelation('entree', $entree);
        $fact->setRelation('categorie', $cat);

        $res = $fact->calculateFromEntree();
        $this->assertEquals(15, $res->montant_total);
    }

    /** @test */
    public function forty_nine_hours_charges_two_full_days_plus_full_last_day()
    {
        $cat = new Categorie();
        $cat->id = 3;
        $cat->prix_par_24h = 10;

        $entree = new Entree();
        $entree->date_entree = Carbon::now()->subHours(49);
        $entree->date_sortie = Carbon::now();

        $fact = new Facturation();
        $fact->setRelation('entree', $entree);
        $fact->setRelation('categorie', $cat);

        $res = $fact->calculateFromEntree();
        $this->assertEquals(25, $res->montant_total);
    }

    /** @test */
    public function canter_category_today_results_in_zero_total()
    {
        $cat = new Categorie();
        $cat->id = 1;
        $cat->prix_par_24h = 10;

        $entree = new Entree();
        $entree->date_entree = Carbon::now();
        $entree->date_sortie = Carbon::now()->addHours(2);

        $fact = new Facturation();
        $fact->setRelation('entree', $entree);
        $fact->setRelation('categorie', $cat);

        $res = $fact->calculateFromEntree();
        $this->assertEquals(0, $res->montant_total);
    }
}
