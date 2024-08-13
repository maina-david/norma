<?php

namespace Database\Factories\Corpus;

use App\Enums\Corpus\ReferenceStatus;
use App\Models\Corpus\Citation;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\Work;
use App\Models\Requirements\ReferenceRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'work_id' => Work::factory(),
            'type' => 11,
            'status' => ReferenceStatus::active()->value,
            'volume' => 1,
            'referenceable_id' => Citation::factory(),
            'referenceable_type' => 'citations',
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Reference $reference) {
            ReferenceRequirement::factory()->for($reference)->create();
            ReferenceText::factory()->for($reference)->create();
        });
    }
}
