<?php

namespace Database\Factories\Arachno;

use App\Models\Arachno\Crawler;
use App\Models\Arachno\Source;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CrawlerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Crawler::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        /** @var string */
        $name = $this->faker->unique()->words(3, true);

        return [
            'title' => $name,
            'slug' => Str::slug($name, '-'),
            'source_id' => Source::factory(),
        ];
    }
}
