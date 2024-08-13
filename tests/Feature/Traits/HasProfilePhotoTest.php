<?php

namespace Tests\Feature\Traits;

use App\Models\Auth\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HasProfilePhotoTest extends TestCase
{
    /**
     * @return void
     */
    public function testItGeneratesCorrectImage(): void
    {
        Storage::fake();

        $user = User::factory()->create();

        $this->assertStringContainsString('https://ui-avatars.com/api', $user->profile_photo_url);

        $photo = UploadedFile::fake()->image('passport.jpg');

        $user->updateProfilePhoto($photo);

        $user->refresh();

        $this->assertStringNotContainsString('https://ui-avatars.com/api', $user->profile_photo_url);

        $user->deleteProfilePhoto();

        $user->refresh();

        $this->assertStringContainsString('https://ui-avatars.com/api', $user->profile_photo_url);
    }
}
