<?php

namespace Database\Factories\Comments\Collaborate;

use App\Models\Auth\User;
use App\Models\Comments\Collaborate\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'comment' => $this->faker->paragraphs(3, true),
            'author_id' => User::factory(),
        ];
    }
}
