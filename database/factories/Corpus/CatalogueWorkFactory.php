<?php

namespace Database\Factories\Corpus;

use App\Models\Compilation\RequirementsCollection;
use App\Models\Corpus\CatalogueWork;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CatalogueWorkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CatalogueWork::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(6, true),
            'source_unique_id' => $this->faker->slug(),
            'language_code' => 'eng',
            'work_date' => Carbon::now()->toDateString(),
            'effective_date' => Carbon::now()->toDateString(),
            'work_number' => $this->faker->unique()->words(1, true),
            'publication_document_number' => $this->faker->unique()->words(1, true),
            'publication_number' => $this->faker->unique()->words(1, true),
            'primary_location_id' => RequirementsCollection::factory(),
            'start_url' => $this->faker->unique()->url(),
            'work_type' => 'act',
        ];
    }
}
