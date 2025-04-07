<?php

namespace Database\Factories\Storage\My;

use App\Enums\Storage\My\FolderType;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'folder_id' => Folder::factory(),
            'title' => $this->faker->unique()->words(3, true),
            'path' => Str::random(40),
            'mime_type' => $this->faker->mimeType(),
            'extension' => $this->faker->fileExtension(),
            'size' => $this->faker->numberBetween(0, 100000000),
            'folder_type' => FolderType::norma()->value,
            'author_id' => User::factory(),
            'organisation_id' => Organisation::factory(),
        ];
    }
}
