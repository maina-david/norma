<?php

namespace Tests\Unit\Services\Hubspot;

use App\Http\Services\Hubspot\Properties;
use PHPUnit\Framework\TestCase;

class PropertiesTest extends TestCase
{
    /**
     * @return void
     */
    public function testItAllowsAdditionOfProperties(): void
    {
        $props = new Properties();

        $this->assertEmpty($props->toArray());

        $props->push('key', 'value');

        $this->assertCount(1, $props->toArray());

        $this->assertSame('value', $props->get('key'));

        $props->push('key', 'new value');

        $this->assertCount(1, $props->toArray());

        $this->assertNotSame('value', $props->get('key'));

        $this->assertSame('new value', $props->get('key'));
    }

    /**
     * @return void
     */
    public function testItAllowsAdditionOfPropertiesViaArray(): void
    {
        $props = new Properties();

        $this->assertEmpty($props->toArray());

        $props->fromArray(['firstname' => 'Zebra', 'lastname' => 'Corn']);

        $this->assertCount(2, $props->toArray());

        $this->assertTrue($props->has('firstname'));

        $this->assertTrue($props->missing('middlename'));

        $this->assertSame('Zebra', $props->get('firstname'));

        $this->assertSame('Corn', $props->get('lastname'));
    }

    /**
     * @return void
     */
    public function testItGeneratesTheCorrectFormat(): void
    {
        $props = new Properties();

        $props->fromArray(['firstname' => 'Zebra']);

        $this->assertEquals('firstname', $props->toArray()[0]['property'] ?? '');

        $this->assertEquals('Zebra', $props->toArray()[0]['value'] ?? '');
    }

    /**
     * @return void
     */
    public function testItCanUnsetProperties(): void
    {
        $props = new Properties();

        $props->fromArray(['firstname' => 'Zebra']);

        $this->assertEquals('firstname', $props->toArray()[0]['property'] ?? '');

        $this->assertEquals('Zebra', $props->toArray()[0]['value'] ?? '');

        $props->unset('firstname');

        $this->assertCount(0, $props->toArray());
    }
}
