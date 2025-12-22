<?php

namespace Tests\Unit\Jobs\Compilation;

use App\Jobs\Compilation\HandleNormaRecompilation;
use App\Models\Customer\Norma;
use App\Services\Compilation\NormaCompilationCacheService;
use Mockery\MockInterface;
use Tests\TestCase;

class HandleNormaRecompilationTest extends TestCase
{
    public function testHandle(): void
    {
        $norma = Norma::factory()->create(['auto_compiled' => true, 'compiled_at' => null]);
        $norma->updateNeedsRecompilation(true);

        $this->partialMock(NormaCompilationCacheService::class, function (MockInterface $mock) use ($norma) {
            $mock->shouldReceive('handleNormaRecompilation')->withArgs(fn ($arg) => $arg->id === $norma->id)->once();
        });

        HandleNormaRecompilation::dispatch($norma->id);
    }
}
