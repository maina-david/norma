<?php

namespace Database\Factories\Corpus;

use App\Models\Corpus\CatalogueWork;
use App\Models\Corpus\CatalogueWorkExpression;
use Illuminate\Database\Eloquent\Factories\Factory;

class CatalogueWorkExpressionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CatalogueWorkExpression::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'catalogue_work_id' => CatalogueWork::factory(),
            'start_date' => now()->format('Y-m-d'),
        ];
    }
}
