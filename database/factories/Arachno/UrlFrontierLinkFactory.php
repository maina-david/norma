<?php

namespace Database\Factories\Arachno;

use App\Models\Arachno\UrlFrontierLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class UrlFrontierLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UrlFrontierLink::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'url' => $this->faker->url(),
        ];
    }
}
