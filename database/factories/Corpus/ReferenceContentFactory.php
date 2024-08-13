<?php

namespace Database\Factories\Corpus;

use App\Models\Corpus\ReferenceContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferenceContentFactory extends Factory
{
    protected $model = ReferenceContent::class;

    /**
     * {@inherit}.
     */
    public function definition(): array
    {
        return [
            'cached_content' => $this->faker->paragraphs(2, true),
        ];
    }
}
