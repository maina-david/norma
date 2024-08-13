<?php

namespace Tests\Feature\General;

use App\Services\TempFileManager;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TempFileManagerTest extends TestCase
{
    /**
     * @return void
     */
    public function testCorrectTempDirectory(): void
    {
        $this->assertStringContainsString(
            now()->startOfHour()->timestamp,
            app(TempFileManager::class)->currentTempDirectory()
        );
    }

    /**
     * @return void
     */
    public function testCreationOfTempDirectoryAndFiles(): void
    {
        Storage::deleteDirectory(app(TempFileManager::class)->getDirectory());

        Storage::assertMissing(app(TempFileManager::class)->currentTempDirectory());

        $created = app(TempFileManager::class)->store(UploadedFile::fake()->image('test.png'));

        Storage::assertExists(app(TempFileManager::class)->currentTempDirectory());

        Storage::assertExists($created);

        $created = app(TempFileManager::class)->get($created);

        $this->assertNotNull($created);

        $this->assertInstanceOf(File::class, $created);
    }

    /**
     * @return void
     */
    public function testCleaningTempDirectory(): void
    {
        Storage::fake();
        Storage::deleteDirectory(app(TempFileManager::class)->getDirectory());

        $current = Carbon::now()->startOfHour();

        $expected = Carbon::now()->startOfHour()->subHours(5);

        Carbon::setTestNow($expected);

        Storage::assertMissing(app(TempFileManager::class)->currentTempDirectory());

        app(TempFileManager::class)->store(UploadedFile::fake()->image('test.png'));

        $this->assertStringContainsString($expected->timestamp, app(TempFileManager::class)->currentTempDirectory());

        Storage::assertExists(app(TempFileManager::class)->currentTempDirectory());

        Carbon::setTestNow($current);

        $this->assertStringContainsString($current->timestamp, app(TempFileManager::class)->currentTempDirectory());

        app(TempFileManager::class)->clean();

        Storage::assertMissing(app(TempFileManager::class)->currentTempDirectory());
    }
}
