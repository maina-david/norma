<?php

namespace Tests\Feature\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Auth\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreationOfUser(): void
    {
        $action = new CreateNewUser();

        $this->expectExceptionMessage('The email must be a valid email address.');

        $data = [
            'fname' => $this->faker->firstName(),
            'sname' => $this->faker->lastName(),
            'email' => 'fake-email',
            'password' => $this->faker->password(8),
        ];

        $action->create($data);

        $this->assertDatabaseCount('users', 0);

        $data['email'] = $this->faker->safeEmail();

        $user = $action->create($data);

        $this->assertDatabaseCount('users', 1);

        $this->assertNotSame($data['password'], $user->password);

        $this->assertTrue(Hash::check($data['password'], $user->password));
    }

    /**
     * @return void
     */
    public function testResettingOfPasswords(): void
    {
        $action = new ResetUserPassword();

        $user = User::factory()->create();

        $original = $user->password;

        $this->expectException(ValidationException::class);
        // $this->expectExceptionMessage('The password confirmation does not match.');

        $password = $this->faker->password(3);

        $action->reset($user, ['password' => $password]);

        $this->assertSame($original, $user->refresh()->password);

        $password = $this->faker->password(12);

        $action->reset($user, ['password' => $password]);

        $this->assertNotSame($original, $user->refresh()->password);

        $this->assertTrue(Hash::check($password, $user->password));
    }

    /**
     * @return void
     */
    public function testUpdatingOfPasswords(): void
    {
        $action = new UpdateUserPassword();

        $user = User::factory()->create();

        $original = $user->password;

        $this->expectException(ValidationException::class);
        // $this->expectExceptionMessage('The password confirmation does not match.');

        $password = $this->faker->password(3);

        $action->update($user, ['current_password' => 'password', 'password' => $password]);

        $this->expectException(ValidationException::class);
        // $this->expectExceptionMessage('The password must be at least 8 characters and contain at least one uppercase character and one special character. (and 1 more error)');

        $password = $this->faker->password(12);

        $action->update($user, ['current_password' => 'password1', 'password' => $password]);

        $this->assertSame($original, $user->refresh()->password);

        $password = $this->faker->password(12);

        $action->update($user, ['current_password' => 'password', 'password' => $password]);

        $this->assertNotSame($original, $user->refresh()->password);

        $this->assertTrue(Hash::check($password, $user->password));
    }

    /**
     * @return void
     */
    public function testUpdatingOfProfile(): void
    {
        $action = new UpdateUserProfileInformation();

        $user = User::factory()->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The fname field is required.');

        $action->update($user, ['fname' => null, 'sname' => $user->sname, 'email' => $user->email]);

        $this->expectExceptionMessage('The given data was invalid.');

        $fName = $this->faker->firstName();

        $action->update($user, ['fname' => $fName, 'sname' => $user->sname, 'email' => $user->email]);

        $this->assertNotSame($fName, $user->fname);

        $this->assertSame($fName, $user->refresh()->fname);
    }

    /**
     * @return void
     */
    public function testUpdatingProfilePhoto(): void
    {
        $user = User::factory()->create();

        Storage::fake();

        $this->assertNull($user->avatar_attachment_id);

        $user->updateProfilePhoto(UploadedFile::fake()->image('photo.jpg'));

        $user->refresh();

        $current = $user->avatar_attachment_id;

        $this->assertNotNull($current);

        $user->updateProfilePhoto(UploadedFile::fake()->image('photo2.jpg'));

        $user->refresh();

        $this->assertNotSame($user->avatar_attachment_id, $current);
    }

    public function testGeneratingTextAvatar(): void
    {
        $user = User::factory()->create();

        $expected = sprintf(
            'https://ui-avatars.com/api/?name=%s&color=ffffff&background=014E20',
            urlencode("{$user->fname} {$user->sname}")
        );

        $this->assertEquals($expected, $user->profile_photo_url);
    }
}
