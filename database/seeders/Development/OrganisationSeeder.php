<?php

namespace Database\Seeders\Development;

use App\Models\Customer\CompilationSetting;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Database\Seeder;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Organisation::truncate();
        Norma::truncate();
        CompilationSetting::truncate();

        $org = Organisation::factory()->create(['title' => 'Norma', 'whitelabel_id' => null]);

        Norma::factory()->create(['title' => 'Norma SA', 'organisation_id' => $org->id, 'location_id' => 1]);
        Norma::factory()->create(['title' => 'Norma KE', 'organisation_id' => $org->id, 'location_id' => 2]);
        Norma::factory()->create(['title' => 'Norma UK', 'organisation_id' => $org->id, 'location_id' => 3]);
    }
}
