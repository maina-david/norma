<?php

namespace Tests\Unit\Jobs\Compilation;

use App\Jobs\Compilation\HandleNormaRecompilation;
use App\Jobs\Compilation\RecompileAllStreams;
use App\Models\Customer\Norma;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class RecompileAllStreamsTest extends TestCase
{
    public function testHandle(): void
    {
        $norma = Norma::factory()->create(['auto_compiled' => true]);
        $norma->updateNeedsRecompilation(true);

        $job = new RecompileAllStreams(true);

        Bus::fake();
        $job->handle();

        Bus::assertDispatched(HandleNormaRecompilation::class);

        $norma->refresh();
        $this->assertFalse($norma->needs_recompilation);
    }
}
