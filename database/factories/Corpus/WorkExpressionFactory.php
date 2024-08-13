<?php

namespace Database\Factories\Corpus;

use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Storage\CorpusDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkExpressionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkExpression::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'volume' => 1,
            'source_document_id' => CorpusDocument::factory(),
            'work_id' => Work::factory(),
            'start_date' => $this->faker->dateTimeThisCentury()->format('Y-m-d'),
            'source_url' => $this->faker->url(),
            'show_source_document' => true,
        ];
    }
}
