<?php

namespace Database\Factories\Corpus;

use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class TocItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TocItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'label' => $this->faker->unique()->words(3, true),
            'doc_id' => Doc::factory(),
        ];
    }
}
