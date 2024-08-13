<?php

namespace Database\Factories\Corpus;

use App\Models\Corpus\ContentResource;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentResourceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContentResource::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'mime_type' => 'text/plain',
        ];
    }
}
