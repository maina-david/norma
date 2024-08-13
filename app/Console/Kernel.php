<?php

namespace App\Console;

use App\Actions\Arachno\Crawler\ScheduleCrawlers;
use App\Actions\Arachno\Doc\PopulateDocRequirementScores;
use App\Actions\Assess\AssessmentItemResponse\CreateMissingResponses;
use App\Actions\Assess\AssessmentItemResponse\DeleteResponsesWithoutReferences;
use App\Actions\Assess\CreateMetricsSnapshots;
use App\Actions\Auth\User\MigrateLibryoUserRoles;
use App\Actions\Payments\CreatePaymentsForAllTeams;
use App\Actions\Tasks\Task\MigrateTaskSettings;
use App\Console\Commands\Auth\ReviewUserLifecycles;
use App\Console\Commands\Auth\SyncUsersLastActivity;
use App\Console\Commands\CleanTemporaryFiles;
use App\Console\Commands\Corpus\CacheWorkContent;
use App\Console\Commands\Notify\SendReminderNotifications;
use App\Console\Commands\Tasks\NotifyDueTasks;
use App\Jobs\Arachno\CheckForSearchUrlFrontierLinks;
use App\Jobs\Arachno\CheckForUrlFrontierLinks;
use App\Jobs\Arachno\Sources\Us\CasemakerSyncImages;
use App\Jobs\Auth\RemoveTrialUsers;
use App\Jobs\Collaborators\ValidateProfileDocuments;
use App\Jobs\Compilation\RecompileAllStreams;
use App\Jobs\Compilation\RecompileStreamsNeedingRecompilation;
use App\Jobs\Customer\ImplementPasswordResetPolicy;
use App\Jobs\Notify\NotifyLibraries;
use App\Jobs\Notify\SendDailyNotificationEmails;
use App\Jobs\Notify\SendMonthlyNotificationEmails;
use App\Jobs\PruneModels;
use App\Jobs\Tasks\CreateRecurringTasks;
use App\Jobs\Workflows\GenerateMonitoringTasks;
use App\Jobs\Workflows\NotifyTasksDueJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/** @codeCoverageIgnore */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, string>
     */
    protected $commands = [
        CreateMetricsSnapshots::class,
        CreateMissingResponses::class,
        MigrateTaskSettings::class,
        MigrateLibryoUserRoles::class,
        PopulateDocRequirementScores::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cache:prune-stale-tags')->hourly();

        $schedule->command('queue:prune-failed', ['--hours' => 48])->daily();

        $schedule->command(CleanTemporaryFiles::class)->hourly();

        $schedule->job(PruneModels::class, 'long-running')->dailyAt('23:00');

        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        $schedule->job(new RemoveTrialUsers())->timezone('UTC')->dailyAt('19:00');

        $schedule->command(ReviewUserLifecycles::class)->timezone('UTC')->dailyAt('06:30');

        $schedule->job(NotifyLibraries::class, 'long-running')->timezone('UTC')->dailyAt('00:15');

        $schedule->job(SendMonthlyNotificationEmails::class)->timezone('UTC')->monthlyOn(1, '04:00');

        $schedule->job(SendDailyNotificationEmails::class)->timezone('UTC')->dailyAt('05:00');

        $schedule->job(ImplementPasswordResetPolicy::class)->timezone('UTC')->dailyAt('06:00');

        $schedule->job(new RecompileStreamsNeedingRecompilation(), 'compilation')->everyFiveMinutes();

        $schedule->job(new RecompileAllStreams(true), 'exports')->timezone('UTC')->dailyAt('20:00');

        $schedule->job(CreateMetricsSnapshots::class, 'exports')->monthlyOn(1, '03:00');

        $schedule->job(CreateMissingResponses::class, 'long-running')->twiceDailyAt(13, 22);

        $schedule->job(DeleteResponsesWithoutReferences::class, 'long-running')->twiceDailyAt(14, 23);

        $schedule->command(SendReminderNotifications::class)->everyFiveMinutes();

        $schedule->command(NotifyDueTasks::class)->hourly();

        $schedule->command(SyncUsersLastActivity::class)->timezone('UTC')->dailyAt('01:30');

        $schedule->job(CheckForUrlFrontierLinks::class, 'arachno-frontier-checks')->everyMinute();

        $schedule->job(CheckForSearchUrlFrontierLinks::class, 'arachno-frontier-checks')->everyMinute();

        //        $schedule->job(IndexUnindexedWorks::class, 'arachno')->timezone('UTC')->dailyAt('18:00');

        // $schedule->command(AugustModelBackfill::class, [10])->everyMinute();

        $this->scheduleArachnoScripts($schedule);

        $this->scheduleCasemakerJobs($schedule);

        $schedule->command('queue:prune-batches')->daily();

        $schedule->job(ValidateProfileDocuments::class)->dailyAt('09:00');
        $schedule->job(NotifyTasksDueJob::class)->everyFiveMinutes();

        $schedule->job(CreatePaymentsForAllTeams::class, 'exports')->cron('0 7 1 2-11 *'); // 1st of month at 07:00, Feb - Nov.
        $schedule->job(CreatePaymentsForAllTeams::class, 'exports')->cron('0 7 15 12 *'); // 15th Dec at 07:00.

        $schedule->command(CacheWorkContent::class)->everyFiveMinutes();

        $schedule->job(GenerateMonitoringTasks::class)->daily();

        $schedule->job(CreateRecurringTasks::class)->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    public function scheduleArachnoScripts(Schedule $schedule): void
    {
        app(ScheduleCrawlers::class)->handle($schedule);
    }

    /**
     * @param Schedule $schedule
     *
     * @return void
     */
    public function scheduleCasemakerJobs(Schedule $schedule): void
    {
        $schedule->job(CasemakerSyncImages::class, 'CA')
            ->cron('35 17 2 */3 *'); // At 17:35 on day-of-month 2 in every 3rd month.
        $schedule->job(CasemakerSyncImages::class, 'SC')
            ->cron('35 17 5 */3 *'); // At 17:35 on day-of-month 5 in every 3rd month.
        $schedule->job(CasemakerSyncImages::class, 'USCFR/US')
            ->cron('35 17 3 */3 *'); // At 17:35 on day-of-month 3 in every 3rd month.
    }
}
