<?php

namespace Database\Factories\Customer;

use App\Models\Compilation\Library;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibryoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Libryo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(4, true),
            'description' => $this->faker->paragraph(2),
            'address' => $this->faker->address(),
            'geo_lat' => $this->faker->latitude(),
            'geo_lng' => $this->faker->longitude(),
            'location_id' => RequirementsCollection::factory(),
            'library_id' => Library::factory(),
            'organisation_id' => Organisation::factory(),
            'deactivated' => false,
            'compilation_in_progress' => false,
            'place_type_id' => null,
            'auto_compiled' => true,
        ];
    }
}
