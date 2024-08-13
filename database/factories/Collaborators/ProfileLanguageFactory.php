<?php

namespace Database\Factories\Collaborators;

use App\Enums\Collaborators\LanguageProficiency;
use App\Models\Collaborators\ProfileLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileLanguageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProfileLanguage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'language_code' => $this->faker->languageCode(),
            'proficiency' => $this->faker->randomElement(array_values(LanguageProficiency::options())),
        ];
    }
}
