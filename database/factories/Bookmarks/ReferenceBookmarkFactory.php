<?php

namespace Database\Factories\Bookmarks;

use App\Models\Auth\User;
use App\Models\Bookmarks\ReferenceBookmark;
use App\Models\Corpus\Reference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferenceBookmark>
 */
class ReferenceBookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->create(),
            'reference_id' => Reference::factory()->create(),
        ];
    }
}
