<?php

namespace Database\Seeders\Development;

use App\Models\Ontology\LegalDomain;
use Illuminate\Database\Seeder;

class LegalDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        LegalDomain::truncate();
        $top = LegalDomain::create(['title' => 'Environmental']);
        $top->update(['top_parent_id' => $top->id]);
        $parent = LegalDomain::create(['title' => 'Resource Conservation', 'parent_id' => $top->id, 'top_parent_id' => $top->id]);
        $parent = LegalDomain::create(['title' => 'Water Conservation', 'parent_id' => $parent->id, 'top_parent_id' => $top->id]);
        $parent = LegalDomain::create(['title' => 'Energy Conservation', 'parent_id' => $parent->id, 'top_parent_id' => $top->id]);

        $top = LegalDomain::create(['title' => 'Occupational Health & Safety']);
        $top->update(['top_parent_id' => $top->id]);
        $parent = LegalDomain::create(['title' => 'Working Environment Management', 'parent_id' => $top->id, 'top_parent_id' => $top->id]);
        $parent = LegalDomain::create(['title' => 'Hazardous Substances Health Management ', 'parent_id' => $parent->id, 'top_parent_id' => $top->id]);
        $parent = LegalDomain::create(['title' => 'Ergonomic Health and Safety Management', 'parent_id' => $parent->id, 'top_parent_id' => $top->id]);
    }
}
