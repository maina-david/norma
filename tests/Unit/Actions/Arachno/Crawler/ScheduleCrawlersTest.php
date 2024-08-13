<?php

namespace Tests\Unit\Actions\Arachno\Crawler;

use App\Actions\Arachno\Crawler\ScheduleCrawlers;
use App\Jobs\Arachno\RunCrawler;
use App\Models\Arachno\Crawler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ScheduleCrawlersTest extends TestCase
{
    public function testHandle(): void
    {
        Bus::fake();
        Event::fake();

        /** @var Crawler */
        $crawler = Crawler::factory()->create(['schedule_new_works' => 'invalid cron', 'enabled' => true]);
        $schedule = new Schedule();
        app(ScheduleCrawlers::class)->handle($schedule);

        $addedToScheduler = collect($schedule->events())
            ->pluck('description')
            ->contains(RunCrawler::class);
        $this->assertFalse($addedToScheduler);

        $crawler->update(['schedule_new_works' => '1 * * * *']);
        app(ScheduleCrawlers::class)->handle($schedule);
        $addedToScheduler = collect($schedule->events())
            ->pluck('description')
            ->contains(RunCrawler::class);
        $this->assertTrue($addedToScheduler);

        $schedule = new Schedule();
        $crawler->update(['schedule_changes' => '1 * * * *']);
        app(ScheduleCrawlers::class)->handle($schedule);
        $addedToScheduler = collect($schedule->events())
            ->pluck('description')
            ->contains(RunCrawler::class);
        $this->assertTrue($addedToScheduler);

        $schedule = new Schedule();
        $crawler->update(['schedule_updates' => '1 * * * *']);
        app(ScheduleCrawlers::class)->handle($schedule);
        $addedToScheduler = collect($schedule->events())
            ->pluck('description')
            ->contains(RunCrawler::class);
        $this->assertTrue($addedToScheduler);

        $schedule = new Schedule();
        $crawler->update(['schedule_full_catalogue' => '1 * * * *']);
        app(ScheduleCrawlers::class)->handle($schedule);
        $addedToScheduler = collect($schedule->events())
            ->pluck('description')
            ->contains(RunCrawler::class);
        $this->assertTrue($addedToScheduler);
    }
}
