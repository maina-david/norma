<?php

namespace App\Actions\Arachno\Crawler;

use App\Enums\Arachno\CrawlType;
use App\Jobs\Arachno\RunCrawler;
use App\Models\Arachno\Crawler;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\Schedule;
use Lorisleiva\Actions\Concerns\AsAction;

class ScheduleCrawlers
{
    use AsAction;

    /**
     * @param Schedule $schedule
     *
     * @return void
     */
    public function handle(Schedule $schedule): void
    {
        foreach (Crawler::isEnabled()->hasNewWorksSchedule()->cursor() as $crawler) {
            $this->runCrawler($schedule, $crawler, 'schedule_new_works');
        }
        foreach (Crawler::isEnabled()->hasChangesSchedule()->cursor() as $crawler) {
            $this->runCrawler($schedule, $crawler, 'schedule_changes');
        }
        foreach (Crawler::isEnabled()->hasUpdatesSchedule()->cursor() as $crawler) {
            $this->runCrawler($schedule, $crawler, 'schedule_updates');
        }
        foreach (Crawler::isEnabled()->hasFullCatalogueSchedule()->cursor() as $crawler) {
            $this->runCrawler($schedule, $crawler, 'schedule_full_catalogue');
        }
    }

    /**
     * @param Schedule $schedule
     * @param Crawler  $crawler
     * @param string   $scheduleField
     *
     * @return void
     */
    protected function runCrawler(Schedule $schedule, Crawler $crawler, string $scheduleField): void
    {
        $cron = $crawler->{$scheduleField};
        if (!CronExpression::isValidExpression($cron)) {
            return;
        }

        $crawlType = match ($scheduleField) {
            'schedule_new_works' => CrawlType::NEW_CATALOGUE_WORK_DISCOVERY->value,
            'schedule_changes' => CrawlType::WORK_CHANGES_DISCOVERY->value,
            'schedule_updates' => CrawlType::FOR_UPDATES->value,
            'schedule_full_catalogue' => CrawlType::FULL_CATALOGUE->value,
            default => CrawlType::GENERAL->value,
        };

        $schedule->job(new RunCrawler($crawler->id, $crawlType), 'arachno')
            ->cron($cron ?? '25 6 * * 6');
    }
}
