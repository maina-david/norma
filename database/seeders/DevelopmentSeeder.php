<?php

namespace Database\Seeders;

use App\Models\Lookups\AnnotationSource;
use Database\Seeders\Development\FolderSeeder;
use Database\Seeders\Development\LegalDomainSeeder;
use Database\Seeders\Development\LocationSeeder;
use Database\Seeders\Development\OrganisationSeeder;
use Database\Seeders\Development\UserSeeder;
use Database\Seeders\Lookups\AnnotationSourcesSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('set FOREIGN_KEY_CHECKS=0;');
        AnnotationSource::truncate();

        $this->call([
            AnnotationSourcesSeeder::class,
            LegalDomainSeeder::class,
            LocationSeeder::class,
            OrganisationSeeder::class,
            FolderSeeder::class,
            UserSeeder::class,
        ]);
        DB::statement('set FOREIGN_KEY_CHECKS=1;');
    }
}
