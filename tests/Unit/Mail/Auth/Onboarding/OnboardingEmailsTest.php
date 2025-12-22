<?php

namespace Tests\Unit\Mail\Auth\Onboarding;

use App\Mail\Auth\Onboarding\GettingStartedEmail;
use App\Mail\Auth\Onboarding\IntroEmail;
use App\Mail\Auth\Onboarding\PendingDeactivationEmail;
use App\Mail\Auth\Onboarding\UserGuideEmail;
use App\Models\Auth\User;
use Tests\TestCase;

class OnboardingEmailsTest extends TestCase
{
    public function testIntroEmailContents(): void
    {
        $user = User::factory()->create();

        $mailable = new IntroEmail($user);

        // see in the body
        $mailable->assertSeeInHtml('received a system-generated email');
        $mailable->assertSeeInText('received a system-generated email');

        $mailable->assertSeeInHtml('What is the ERM Norma Platform?');
        $mailable->assertSeeInText('What is the ERM Norma Platform?');
    }

    public function testUserGuideContents(): void
    {
        $user = User::factory()->create();

        $mailable = new UserGuideEmail($user);

        $mailable->assertSeeInHtml('We hope that you are starting to familiarise yourself with the ERM Norma platform');
        $mailable->assertSeeInText('We hope that you are starting to familiarise yourself with the ERM Norma platform');
    }

    public function testGettingStartedContents(): void
    {
        $user = User::factory()->create();

        $mailable = new GettingStartedEmail($user);

        $mailable->assertSeeInHtml('We hope that you have managed to log in to ERM Norma and explore a bit');
        $mailable->assertSeeInText('We hope that you have managed to log in to ERM Norma and explore a bit');
    }

    public function testPendingDeactivationEmailContents(): void
    {
        $user = User::factory()->create();

        $mailable = new PendingDeactivationEmail($user);

        $mailable->assertSeeInHtml('so due to inactivity your account will be de-activated shortly');
        $mailable->assertSeeInText('so due to inactivity your account will be de-activated shortly');

        $mailable->assertSeeInHtml('We noticed you haven\'t accessed ERM Norma in over 5 months, so due to inactivity your account will be de-activated shortly.', false);
        $mailable->assertSeeInText('We noticed you haven\'t accessed ERM Norma in over 5 months, so due to inactivity your account will be de-activated shortly.');
    }
}
