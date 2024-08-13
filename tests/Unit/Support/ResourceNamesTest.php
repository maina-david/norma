<?php

namespace Tests\Unit\Support;

use App\Managers\AppManager;
use App\Support\ResourceNames;
use Tests\TestCase;

class ResourceNamesTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetLongName(): void
    {
        $name = ResourceNames::getLongName('App\\Models\\Arachno\\Source');
        $this->assertEquals('Arachno\\Source', $name);
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        $name = ResourceNames::getName('App\\Models\\Arachno\\Source');
        $this->assertEquals('Source', $name);
    }

    /**
     * @return void
     */
    public function testGetViewPath(): void
    {
        $app = AppManager::getApp();
        $name = ResourceNames::getViewPath('App\\Models\\Arachno\\Source');
        $this->assertEquals("arachno.{$app}.source", $name);
    }

    /**
     * @return void
     */
    public function testGetPermissionPath(): void
    {
        $name = ResourceNames::getPermissionPath('App\\Models\\Arachno\\Source');
        $this->assertEquals('arachno.source', $name);
    }

    /**
     * @return void
     */
    public function testGetLangPath(): void
    {
        $name = ResourceNames::getLangPath('App\\Models\\System\\SystemNotification');
        $this->assertEquals('system.system_notification', $name);
    }
}
