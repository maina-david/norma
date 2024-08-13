<?php

namespace Tests\Unit\Jobs\Compilation;

use App\Jobs\Compilation\HandleLibryoRecompilation;
use App\Models\Customer\Libryo;
use App\Services\Compilation\LibryoCompilationCacheService;
use Mockery\MockInterface;
use Tests\TestCase;

class HandleLibryoRecompilationTest extends TestCase
{
    public function testHandle(): void
    {
        $libryo = Libryo::factory()->create(['auto_compiled' => true, 'compiled_at' => null]);
        $libryo->updateNeedsRecompilation(true);

        $this->partialMock(LibryoCompilationCacheService::class, function (MockInterface $mock) use ($libryo) {
            $mock->shouldReceive('handleLibryoRecompilation')->withArgs(fn ($arg) => $arg->id === $libryo->id)->once();
        });

        HandleLibryoRecompilation::dispatch($libryo->id);
    }
}
