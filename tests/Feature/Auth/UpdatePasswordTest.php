<?php

namespace Tests\Feature\Auth;

use App\Models\Auth\User;
use App\Models\Auth\WeakPassword;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    public function testPasswordCanBeUpdated()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->json('PUT', route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-passwordD123!',
            'password_confirmation' => 'new-passwordD123!',
        ])->assertSuccessful();

        $this->assertTrue(Hash::check('new-passwordD123!', $user->fresh()->password));
    }

    public function testCurrentPasswordMustBeCorrect()
    {
        $this->actingAs($user = User::factory()->create());

        $this->json('PUT', route('user-password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-passwordD123!',
            'password_confirmation' => 'new-passwordD123!',
        ])->assertJsonValidationErrorFor('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function testNewPasswordsMustMatch()
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-passwordD123!',
            'password_confirmation' => 'wrong-passwordD123!',
        ]);

        $response->assertSessionHasErrors();

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function testPasswordsMustNotBeWeak(): void
    {
        WeakPassword::create(['password' => 'Very weak Password!123']);

        $this->actingAs($user = User::factory()->create());

        $response = $this->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'Very weak Password!123',
            'password_confirmation' => 'Very weak Password!123',
        ]);

        $response->assertSessionHasErrors();

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
