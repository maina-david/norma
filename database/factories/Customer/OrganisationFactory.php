<?php

namespace Database\Factories\Customer;

use App\Enums\Customer\OrganisationLevel;
use App\Enums\Customer\OrganisationPlan;
use App\Enums\Customer\OrganisationType;
use App\Models\Customer\Organisation;
use App\Models\Partners\WhiteLabel;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Organisation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'whitelabel_id' => WhiteLabel::factory(),
            'type' => OrganisationType::enterprise()->value,
            'plan' => OrganisationPlan::paid()->value,
            'customer_category' => OrganisationLevel::a()->value,
        ];
    }
}
