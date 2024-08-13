<?php

namespace Database\Seeders\Development;

use App\Models\Customer\CompilationSetting;
use App\Models\Customer\Libryo;
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
        Libryo::truncate();
        CompilationSetting::truncate();

        $org = Organisation::factory()->create(['title' => 'Libryo', 'whitelabel_id' => null]);

        Libryo::factory()->create(['title' => 'Libryo SA', 'organisation_id' => $org->id, 'location_id' => 1]);
        Libryo::factory()->create(['title' => 'Libryo KE', 'organisation_id' => $org->id, 'location_id' => 2]);
        Libryo::factory()->create(['title' => 'Libryo UK', 'organisation_id' => $org->id, 'location_id' => 3]);
    }
}
