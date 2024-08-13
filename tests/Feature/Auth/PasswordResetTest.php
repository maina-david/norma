<?php

namespace Tests\Feature\Auth;

use App\Enums\Auth\LifecycleStage;
use App\Models\Auth\User;
use Exception;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testResetPasswordLinkScreenCanBeRendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function testResetPasswordLinkCanBeRequested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function testResetPasswordScreenCanBeRendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/' . $notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function testPasswordCanBeResetWithValidToken(): void
    {
        Notification::fake();

        $user = User::factory()->create(['lifecycle_stage' => LifecycleStage::invitationSent()->value]);

        $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'Password!123',
                'password_confirmation' => 'Password!123',
            ]);

            $response->assertSessionHasNoErrors();

            return true;
        });
    }
}
