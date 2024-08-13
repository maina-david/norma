<?php

namespace Tests\Unit\Jobs\Compilation;

use App\Jobs\Compilation\HandleLibryoRecompilation;
use App\Jobs\Compilation\RecompileAllStreams;
use App\Models\Customer\Libryo;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class RecompileAllStreamsTest extends TestCase
{
    public function testHandle(): void
    {
        $libryo = Libryo::factory()->create(['auto_compiled' => true]);
        $libryo->updateNeedsRecompilation(true);

        $job = new RecompileAllStreams(true);

        Bus::fake();
        $job->handle();

        Bus::assertDispatched(HandleLibryoRecompilation::class);

        $libryo->refresh();
        $this->assertFalse($libryo->needs_recompilation);
    }
}
