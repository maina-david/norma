<?php

namespace Tests\Feature\Managers;

use App\Enums\Application\ApplicationType;
use App\Managers\AppManager;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Tests\TestCase;

class AppManagerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItGeneratesTheCorrectAppDomain(): void
    {
        Config::set('app.url', 'https://monolith.test');

        $this->assertSame('monolith.test', AppManager::appDomain());
    }

    /**
     * @return void
     */
    public function testItGeneratesTheCorrectWhiteLabelURL(): void
    {
        Config::set('app.url', 'https://monolith.test');

        $this->assertSame('zebracorn.monolith.test', AppManager::appWhiteLabelURL('zebracorn'));
    }

    /**
     * @return void
     */
    public function testItSetsTheCorrectApp(): void
    {
        Config::set('app.name', 'Monolith');

        $this->assertSame('Monolith', Config::get('app.name'));

        $manager = $this->app->make(AppManager::class);

        $manager->setApp(ApplicationType::collaborate()->value);

        $this->assertSame('Libryo Collaborate', Config::get('app.name'));

        $this->assertSame(ApplicationType::collaborate()->value, AppManager::getApp());

        $this->expectException(InvalidArgumentException::class);

        $manager->setApp('zebracorn');

        $this->assertSame('Monolith', Config::get('app.name'));
    }

    /**
     * @return void
     */
    public function testItValidatesThemes(): void
    {
        collect(AppManager::COLOURS)->each(function ($theme) {
            $this->assertSame($theme, AppManager::validateThemeAttribute($theme));
        });

        $this->expectException(InvalidArgumentException::class);

        AppManager::validateThemeAttribute('zebra');
    }
}
