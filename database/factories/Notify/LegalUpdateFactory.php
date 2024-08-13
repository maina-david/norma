<?php

namespace Database\Factories\Notify;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Models\Arachno\Source;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

class LegalUpdateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LegalUpdate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            // 'work_id' => Work::factory(),
            'language_code' => 'eng',
            'highlights' => $this->faker->unique()->randomHtml(1, 1),
            'changes_to_register' => $this->faker->unique()->randomHtml(1, 1),
            'update_report' => $this->faker->unique()->randomHtml(1, 1),
            'publication_number' => $this->faker->unique()->word(),
            'publication_document_number' => $this->faker->unique()->word(),
            'effective_date' => $this->faker->date(),
            'publication_date' => $this->faker->date(),
            'primary_location_id' => Location::factory(),
            'source_id' => Source::factory(),
            'status' => LegalUpdatePublishedStatus::UNPUBLISHED->value,
            'late' => false,
        ];
    }
}
