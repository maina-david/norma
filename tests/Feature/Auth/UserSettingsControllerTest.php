<?php

namespace Tests\Feature\Auth;

use App\Models\Auth\User;
use App\Models\Ontology\LegalDomain;
use Hash;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\My\MyTestCase;

class UserSettingsControllerTest extends MyTestCase
{
    public function testShowSettingsProfile(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.user.settings.profile.show';
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSeeSelector('//label[text()[contains(.,"First Name")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Last Name")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Timezone")]]');

        $response->assertSeeSelector('//input[@value[contains(.,"' . $user->fname . '")]]');
        $response->assertSeeSelector('//input[@value[contains(.,"' . $user->sname . '")]]');
    }

    public function testUpdateSettingsProfile(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.user.settings.profile.update';
        $response = $this->put(route($routeName), [
            'fname' => preg_replace('/[^a-z-_]/i', '', $this->faker()->firstName()),
        ])->assertRedirect();
        $response->assertSessionHasErrors(['sname']);
        $response = $this->put(route($routeName), [
            'sname' => preg_replace('/[^a-z-_]/i', '', $this->faker()->firstName()),
        ])->assertRedirect();
        $response->assertSessionHasErrors(['fname']);
        $response = $this->put(route($routeName), [
            'mobile_country_code' => 'asdfsdf',
        ])->assertRedirect();
        $response->assertSessionHasErrors(['mobile_country_code']);

        $response = $this->put(route($routeName), [
            'fname' => preg_replace('/[^a-z-_]/i', '', $this->faker()->firstName()),
            'sname' => preg_replace('/[^a-z-_]/i', '', $this->faker()->lastName()),
        ])->assertRedirect();
        $response->assertSessionHasErrors(['timezone']);

        Storage::fake();
        $response = $this->put(route($routeName), [
            'fname' => preg_replace('/[^a-z-_]/i', '', $this->faker()->firstName()),
            'sname' => preg_replace('/[^a-z-_]/i', '', $this->faker()->lastName()),
            'timezone' => preg_replace('/[^a-z-_]/i', '', $this->faker()->timezone()),
            'profile_photo' => UploadedFile::fake()->image('photo.jpg'),
        ])->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
    }

    public function testShowSettingsEmail(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.user.settings.email.show';
        $response = $this->get(route($routeName))->assertSuccessful();

        $response->assertSeeSelector('//label[text()[contains(.,"Password")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"New Email Address")]]');
    }

    public function testUpdateSettingsEmail(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.user.settings.email.update';
        $newEmail = $this->faker()->safeEmail();
        $response = $this->put(route($routeName), [
            'password' => 'wrong',
            'email' => $newEmail,
        ])->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->email === $newEmail);

        $password = 'Correct!';
        $newEmail = $this->faker()->safeEmail();
        $user->update(['password' => Hash::make($password)]);
        $user->refresh();
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertFalse($user->email === $newEmail);

        $response = $this->put(route($routeName), [
            'password' => $password,
            'email' => $newEmail,
        ])->assertRedirect(route('login'));

        $this->assertGuest();

        $this->signIn($user);

        $response = $this->put(route($routeName), [
            'password' => $password,
            'email' => $newEmail,
        ])->assertRedirect(url('/'));

        $user->refresh();
        $this->assertTrue($user->email === $newEmail);

        $response = $this->put(route($routeName), [
            'password' => $password,
            'email' => 'wrong.email@wrong',
        ])->assertRedirect();

        $response->assertSessionHasErrors(['email']);
    }

    public function testShowSettingsPassword(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.user.settings.password.show';
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSeeSelector('//label[text()[contains(.,"Current Password")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"New Password")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Confirm Password")]]');
    }

    public function testUpdateSettingsPassword(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.user.settings.password.update';
        $password = 'Incorrect';
        $new = 'New';
        $user->update(['password' => Hash::make($password)]);

        $response = $this->put(route($routeName), [
            'current_password' => $password,
            'password' => $new,
            'password_confirmation' => $new,
        ])->assertRedirect();

        $response->assertSessionHas('flash.type', 'error');

        $password = 'Correct!';
        $user->update(['password' => Hash::make($password)]);

        $this->get('/')->assertRedirect(route('login'));

        $this->signIn($user->refresh());

        $response = $this->put(route($routeName), [
            'current_password' => $password,
            'password' => $new,
            'password_confirmation' => $new . 'extra',
        ])->assertRedirect();

        $response->assertSessionHas('flash.type', 'error');
        $response->assertSessionHas('flash.type', 'error');

        $password = 'Correct!';
        $new = 'Correct!#@123';
        $user->update(['password' => Hash::make($password)]);

        $this->get('/')->assertRedirect(route('login'));

        $this->signIn($user->refresh());

        $response = $this->put(route($routeName), [
            'current_password' => $password,
            'password' => $new,
            'password_confirmation' => $new,
        ])->assertRedirect();

        $response->assertSessionDoesntHaveErrors();
        $response->assertSessionHas('flash.message', 'Password updated successfully!');
    }

    public function testShowSettingsNotifications(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.user.settings.notifications.show';
        $response = $this->get(route($routeName))->assertSuccessful();

        $response->assertSeeSelector('//label[text()[contains(.,"All Categories")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Daily Email Notifications")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Monthly Email Notifications")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Comment Mention Email")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Comment Reply Email")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Task assigned")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Reminder Email")]]');
    }

    public function testUpdateSettingsNotifications(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.user.settings.notifications.update';

        $response = $this->put(route($routeName), [
            'email_comment_mention' => true,
            'email_comment_reply' => true,
        ])->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->settings['email_comment_mention']);
        $this->assertTrue($user->settings['email_comment_reply']);
        $this->assertFalse(isset($user->settings['email_reminder']));
        $this->assertFalse(isset($user->settings['email_task_assigned']));
        $this->assertFalse(isset($user->settings['email_task_assignee_changed_assignee']));
        $this->assertFalse(isset($user->settings['notification_legal_domains']));

        $user->update(['settings' => User::defaultSettings()]);
        $domain = LegalDomain::factory()->create();
        $domain2 = LegalDomain::factory()->create();
        $response = $this->put(route($routeName), [
            'notification_legal_domains' => [$domain->id => true],
            'email_task_completed_watcher' => true,
            'email_task_completed_author' => true,
            'email_task_completed_assignee' => true,
        ])->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->settings['email_task_completed_watcher']);
        $this->assertTrue($user->settings['email_task_completed_author']);
        $this->assertTrue($user->settings['email_task_completed_assignee']);
        $this->assertTrue(in_array($domain->id, $user->settings['notification_legal_domains']));
        $this->assertFalse(in_array($domain2->id, $user->settings['notification_legal_domains']));
        $this->assertFalse(isset($user->settings['email_comment_mention']));
        $this->assertFalse(isset($user->settings['email_comment_reply']));

        // test when user unchecked all domains, selected a domain, and then checked all domains again, and then clicked save
        // that it doesn't save the domains
        $user->update(['settings' => User::defaultSettings()]);
        $response = $this->put(route($routeName), [
            'notification_legal_domains' => [$domain->id],
            'all_legal_domains' => true,
        ])->assertRedirect();

        $this->assertFalse(isset($user->settings['notification_legal_domains']));

        $user->update([
            'email_daily' => true,
            'email_monthly' => true,
        ]);
        $response = $this->put(route($routeName), [
            'email_daily' => '0', // check that bool conversion works
            'email_monthly' => false,
        ])->assertRedirect();
        $this->assertFalse($user->refresh()->email_daily);
        $this->assertFalse($user->refresh()->email_monthly);
    }

    public function testShowSettingsSecurity(): void
    {
        $this->initUserLibryoOrg();
        $routeName = 'my.user.settings.security.show';

        $this->get(route($routeName))->assertRedirect(route('password.confirm'));

        $this->post(route('password.confirm'), ['password' => 'password'])
            ->assertRedirect(route($routeName));

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSeeSelector('//div[text()[contains(.,"Two-Factor Authentication")]]');
    }

    /**
     * @return void
     */
    public function testLoggingOutOtherDevices()
    {
        Event::fake([OtherDeviceLogout::class]);

        $user = $this->signIn($this->mySuperUser());

        $this->post(route('my.auth.logout.devices'), ['password' => 'fakePassword'])
            ->assertRedirect();

        Event::assertNotDispatched(OtherDeviceLogout::class);
        $this->post(route('my.auth.logout.devices'), ['password' => 'password'])
            ->assertRedirect();

        Event::assertDispatched(OtherDeviceLogout::class, function ($event) use ($user) {
            $this->assertSame('web', $event->guard);
            $this->assertEquals($user->id, $event->user->id);

            return true;
        });
    }
}
