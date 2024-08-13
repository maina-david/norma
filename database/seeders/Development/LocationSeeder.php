<?php

namespace Database\Seeders\Development;

use App\Models\Geonames\Location;
use App\Models\Geonames\LocationType;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->seedTypes();
        Location::truncate();
        Location::factory()->create(['id' => 1, 'title' => 'South Africa', 'location_type_id' => 1, 'location_country_id' => 1]);
        Location::factory()->create(['id' => 2, 'title' => 'Kenya', 'location_type_id' => 1, 'location_country_id' => 2]);
        Location::factory()->create(['id' => 3, 'title' => 'United Kingdom', 'location_type_id' => 10, 'location_country_id' => 3]);
    }

    protected function seedTypes(): void
    {
        LocationType::truncate();
        LocationType::insert([
            [
                'id' => 1,
                'title' => 'Country',
                'slug' => 'country',
                'adjective_key' => 'national',
            ],
            [
                'id' => 2,
                'title' => 'Province',
                'slug' => 'province',
                'adjective_key' => 'provincial',
            ],
            [
                'id' => 3,
                'title' => 'District',
                'slug' => 'district',
                'adjective_key' => 'district',
            ],
            [
                'id' => 4,
                'title' => 'Municipality',
                'slug' => 'municipality',
                'adjective_key' => 'municipal',
            ],
            [
                'id' => 5,
                'title' => 'State',
                'slug' => 'state',
                'adjective_key' => 'state',
            ],
            [
                'id' => 6,
                'title' => 'County',
                'slug' => 'county',
                'adjective_key' => 'county',
            ],
            [
                'id' => 7,
                'title' => 'City',
                'slug' => 'city',
                'adjective_key' => 'city',
            ],
            [
                'id' => 8,
                'title' => 'Metro',
                'slug' => 'metro',
                'adjective_key' => 'metro',
            ],
            [
                'id' => 9,
                'title' => 'Union',
                'slug' => 'union',
                'adjective_key' => 'union',
            ],
            [
                'id' => 10,
                'title' => 'Sovereign',
                'slug' => 'sovereign',
                'adjective_key' => 'sovereign',
            ],
            [
                'id' => 11,
                'title' => 'Region',
                'slug' => 'region',
                'adjective_key' => 'regional',
            ],
            [
                'id' => 12,
                'title' => 'Sub-district',
                'slug' => 'sub-district',
                'adjective_key' => 'sub_district',
            ],
            [
                'id' => 13,
                'title' => 'Location',
                'slug' => 'location',
                'adjective_key' => 'locational',
            ],
            [
                'id' => 14,
                'title' => 'Sub-location',
                'slug' => 'sub-location',
                'adjective_key' => 'sub_locational',
            ],
            [
                'id' => 15,
                'title' => 'Constituency',
                'slug' => 'constituency',
                'adjective_key' => 'constituency',
            ],
            [
                'id' => 16,
                'title' => 'Administrative Division',
                'slug' => 'administrative-division',
                'adjective_key' => 'administrative_division',
            ],
            [
                'id' => 17,
                'title' => 'Local Government Area',
                'slug' => 'local-government-area',
                'adjective_key' => 'local_government_area',
            ],
            [
                'id' => 18,
                'title' => 'State / Territory',
                'slug' => 'state-territory',
                'adjective_key' => 'State / Territorial',
            ],
            [
                'id' => 19,
                'title' => 'Town',
                'slug' => 'town',
                'adjective_key' => 'town',
            ],
            [
                'id' => 20,
                'title' => 'Administrative Level 1',
                'slug' => 'administrative-level-1',
                'adjective_key' => 'administrative-level-1',
            ],
            [
                'id' => 21,
                'title' => 'Administrative Level 2',
                'slug' => 'administrative-level-2',
                'adjective_key' => 'administrative-level-2',
            ],
            [
                'id' => 22,
                'title' => 'Administrative Level 3',
                'slug' => 'administrative-level-3',
                'adjective_key' => 'administrative-level-3',
            ],
            [
                'id' => 23,
                'title' => 'Administrative Level 4',
                'slug' => 'administrative-level-4',
                'adjective_key' => 'administrative-level-4',
            ],
            [
                'id' => 24,
                'title' => 'Country - Federal',
                'slug' => 'country-federal',
                'adjective_key' => 'federal',
            ],
            [
                'id' => 25,
                'title' => 'EU Retained Legislation',
                'slug' => 'e-u-retained-legislation',
                'adjective_key' => 'EU retained legislation',
            ],
            [
                'id' => 26,
                'title' => 'Standards',
                'slug' => 'standards',
                'adjective_key' => 'standards',
            ],
            [
                'id' => 27,
                'title' => 'Site Specific',
                'slug' => 'site-specific',
                'adjective_key' => 'site_specific',
            ],
        ]);
    }
}
