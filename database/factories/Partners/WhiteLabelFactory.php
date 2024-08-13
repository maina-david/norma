<?php

namespace Database\Factories\Partners;

use App\Managers\AppManager;
use App\Models\Partners\WhiteLabel;
use Illuminate\Database\Eloquent\Factories\Factory;

class WhiteLabelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WhiteLabel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->unique()->word();
        $shortname = strtolower($title);

        return [
            'title' => $title,
            'shortname' => $shortname,
            'email_template' => '',
            'email_address' => '',
            'urls' => app(AppManager::class)->appWhiteLabelURL($shortname),
            'auth_provider' => 'libryo',
            'app_logo' => '',
            'login_logo' => '',
            'theme' => '',
        ];
    }
}
