<?php

namespace Tests\Feature\Managers;

use App\Managers\AppManager;
use App\Managers\ThemeManager;
use App\Models\Collaborators\Collaborator;
use App\Themes\Collaborate;
use App\Themes\Theme;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WhiteLabelManagerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItRedirectsInvalidWhiteLabels(): void
    {
        Config::set('app.url', 'https://cool-app.test');

        $this->call('GET', 'https://zebracorn.cool-app2.test')
            ->assertRedirect('https://norma.com/contact-us/');
    }

    /**
     * @return void
     */
    public function testItSetsTheCorrectAppSettings(): void
    {
        $manager = $this->app->make(ThemeManager::class);

        $this->get(route('my.dashboard'))->assertSessionHas('app.theme');

        $this->assertSame((new Theme())->shortname(), session('app.theme')->shortname());

        $current = $manager->current();

        $this->assertSame('default', $current->shortname());
        $this->assertSame(asset('/img/norma.png'), $current->loginLogo());
        $this->assertSame(asset('/img/norma-app.svg'), $current->appLogo());
        $this->assertSame(asset('/img/favicon.png'), $current->favicon());

        $user = Collaborator::factory()->create();
        $role = $this->collaborateSuper();
        $this->signIn($user);
        $user->roles()->attach($role->id);
        $user->flushComputedPermissions();

        $this->get(route('collaborate.dashboard'))->assertSessionHas('app.theme');

        $this->assertSame((new Collaborate())->shortname(), session('app.theme')->shortname());

        $this->assertSame('collaborate', AppManager::getApp());

        $current = $manager->current();

        $this->assertArrayHasKey('primary', $current->cssVariables());
        $this->assertArrayHasKey('navbar', $current->cssVariables());
        $this->assertArrayHasKey('navbar-active', $current->cssVariables());
        $this->assertArrayHasKey('navbar-text', $current->cssVariables());
        $this->assertSame('collaborate', $current->shortname());
        $this->assertSame(asset('/img/collaborate-logo.svg'), $current->loginLogo());
        $this->assertSame(asset('/img/collaborate-logo.svg'), $current->appLogo());
        $this->assertSame(asset('/img/collaborate-favicon.png'), $current->favicon());
        $this->assertSame(sprintf('url(%s) no-repeat fixed center / cover rgba(0,0,0, 0.2)', asset('/img/collaborate-background.jpg')), $current->authBackground());
    }
}
