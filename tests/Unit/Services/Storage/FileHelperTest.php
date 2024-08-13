<?php

namespace Tests\Unit\Services\Storage;

use App\Services\Storage\FileHelper;
use Tests\TestCase;

class FileHelperTest extends TestCase
{
    /**
     * @return void
     */
    public function testItGeneratesAHumanReadableValue(): void
    {
        $helper = (new FileHelper());

        $readable = $helper->getHumanReadableSize(0);
        $this->assertSame('0.00B', $readable);
        $readable = $helper->getHumanReadableSize(4);
        $this->assertSame('4B', $readable);
        $readable = $helper->getHumanReadableSize(4 * pow(1024, 1));
        $this->assertSame('4KB', $readable);
        $readable = $helper->getHumanReadableSize(4 * pow(1024, 2));
        $this->assertSame('4MB', $readable);
        $readable = $helper->getHumanReadableSize(4 * pow(1024, 3));
        $this->assertSame('4GB', $readable);
        $readable = $helper->getHumanReadableSize(4 * pow(1024, 4));
        $this->assertSame('4TB', $readable);
        $readable = $helper->getHumanReadableSize(4 * pow(1024, 5));
        $this->assertSame('4PB', $readable);
    }
}
