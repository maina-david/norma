<?php

namespace Tests\Unit\Services\Storage;

use App\Services\Storage\MimeTypeManager;
use Tests\TestCase;

class MimeTypeManagerTest extends TestCase
{
    public function testGetImageMimeTypes(): void
    {
        $arr = app(MimeTypeManager::class)->getImageMimeTypes();
        $this->assertTrue(in_array('image/jpeg', $arr));
    }

    public function testGetAcceptedProfileImageMimeTypes(): void
    {
        $arr = app(MimeTypeManager::class)->getAcceptedProfileImageMimeTypes();
        $this->assertTrue(in_array('image/jpeg', $arr));
    }

    public function testGetAcceptedUploads(): void
    {
        $arr = app(MimeTypeManager::class)->getAcceptedUploads();
        $this->assertTrue(in_array('image/jpeg', $arr));
    }

    public function testIsImage(): void
    {
        $result = app(MimeTypeManager::class)->isImage('image/jpeg');
        $this->assertTrue($result);
    }

    public function testIsPdf(): void
    {
        $result = app(MimeTypeManager::class)->isPdf('application/pdf');
        $this->assertTrue($result);
    }

    public function testIsDocx(): void
    {
        $result = app(MimeTypeManager::class)->isDocx('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertTrue($result);
        $this->assertFalse(app(MimeTypeManager::class)->isPdf('application/vnd.openxmlformats-officedocument.wordprocessingml.document'));
    }

    public function testIsCss(): void
    {
        $result = app(MimeTypeManager::class)->isCss('text/css');
        $this->assertTrue($result);
    }

    public function testIsHtml(): void
    {
        $result = app(MimeTypeManager::class)->isHtml('text/html');
        $this->assertTrue($result);
    }

    public function testIsJson(): void
    {
        $result = app(MimeTypeManager::class)->isJson('application/json');
        $this->assertTrue($result);
    }
}
