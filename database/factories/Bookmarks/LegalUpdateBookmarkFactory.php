<?php

namespace Database\Factories\Bookmarks;

use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bookmarks\LegalUpdateBookmark>
 */
class LegalUpdateBookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'legal_update_id' => LegalUpdate::factory()->create(),
            'user_id' => User::factory()->create(),
        ];
    }
}
