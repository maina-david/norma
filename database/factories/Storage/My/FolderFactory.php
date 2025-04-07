<?php

namespace Database\Factories\Storage\My;

use App\Enums\Storage\My\FolderType;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\Folder;
use Illuminate\Database\Eloquent\Factories\Factory;

class FolderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Folder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'folder_type' => FolderType::norma()->value,
            'is_root' => false,
            'for_attachments' => false,
            'organisation_id' => Organisation::factory(),
            'author_id' => User::factory(),
        ];
    }
}
