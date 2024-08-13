<?php

namespace Database\Seeders;

use Database\Seeders\Lookups\AnnotationSourcesSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AnnotationSourcesSeeder::class,
        ]);
    }
}
