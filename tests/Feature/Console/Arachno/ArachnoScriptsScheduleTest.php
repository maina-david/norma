<?php

namespace Tests\Feature\Console\Arachno;

use App\Console\Kernel;
use App\Models\Arachno\Crawler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ArachnoScriptsScheduleTest extends TestCase
{
    public function testScheduleArachnoScripts(): void
    {
        Queue::fake();

        $crawler = Crawler::factory()->create(['schedule' => '* * * * *', 'enabled' => true]);
        $invalidSchedule = Crawler::factory()->create(['schedule' => '*********', 'enabled' => true]);
        $this->travelTo(Carbon::createFromTimeString('2022-01-01 08:00:00'));
        app(Kernel::class)->scheduleArachnoScripts(new Schedule());
        $this->assertTrue(true);
    }
}
