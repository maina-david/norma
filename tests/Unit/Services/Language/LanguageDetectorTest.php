<?php

namespace Tests\Unit\Services\Language;

use App\Services\Language\LanguageDetector;
use Tests\TestCase;

class LanguageDetectorTest extends TestCase
{
    public function testDetect(): void
    {
        $english = app(LanguageDetector::class)->detect('This should be detected as english');
        $this->assertSame('eng', $english);
        $german = app(LanguageDetector::class)->detect('Es wird empfohlen, dass im öffentlichen Personennahverkehr und in öffentlich zugänglichen Innenräumen, in denen sich mehrere Personen aufhalten, eine medizinische Gesichtsmaske getragen wird.');
        $this->assertSame('deu', $german);
        $result = app(LanguageDetector::class)->detect('');
        $this->assertNull($result);
    }
}
