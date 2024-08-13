<?php

namespace Database\Factories\Storage;

use App\Models\Storage\CorpusDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CorpusDocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CorpusDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'mime_type' => 'text/html',
            'extension' => 'html',
            'size' => $this->faker->numberBetween(1000, 2000000),
            'path' => 'documents/' . Str::random(40),
        ];
    }
}
