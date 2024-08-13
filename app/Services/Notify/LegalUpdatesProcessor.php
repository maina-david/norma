<?php

namespace App\Services\Notify;

use App\Enums\Notify\UserLegalUpdateSentStatus;
use App\Mail\Notify\DailyNotification;
use App\Mail\Notify\MonthlyNotification;
use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\LegalUpdateNotified;
use App\Models\Notify\Pivots\LegalUpdateUser;
use App\Stores\Notify\LegalUpdateStore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LegalUpdatesProcessor
{
    /**
     * Trigger the sending of the daily notifications.
     *
     * @return void
     */
    public function sendDailyMails(): void
    {
        $count = User::withDispatchableMails(['id'], true)->count();
        Log::info("Notify: Sending Daily Emails to {$count} users");

        User::withDispatchableMails(['id'], true)
            ->chunk(1000, function (Collection $users) {
                $users->each(function (User $user) {
                    $updateIds = $user->dispatchableLegalUpdates->pluck('id')->toArray();

                    Mail::to($user)->queue(new DailyNotification($user, $updateIds));

                    $this->setAsMailed($user, $updateIds);
                });
            });
    }

    /**
     * Trigger the sending of monthly notifications.
     *
     * @return void
     */
    public function sendMonthlyMails(): void
    {
        $count = User::notifiedLastMonth(['id'], true)->count();
        Log::info("Notify: Sending Monthly Emails to {$count} users");

        User::notifiedLastMonth(['id'], true)
            ->chunk(1000, function (Collection $users) {
                $users->each(function (User $user) {
                    $updateIds = $user->lastMonthNotifications->pluck('id')->toArray();

                    Mail::to($user)->queue(new MonthlyNotification($user, $updateIds));
                });
            });
    }

    /**
     * Notify the libraries.
     *
     * @return void
     */
    public function notifyLibraries(): void
    {
        $store = app(LegalUpdateStore::class);

        $count = LegalUpdate::unnotifiedReadyForRelease()->count();
        Log::info("Notify: Notifiying libraries for {$count} updates");

        LegalUpdate::unnotifiedReadyForRelease()
            ->with(['notifyReference'])
            ->chunkById(10, function ($chunk) use ($store) {
                $chunk->each(function (LegalUpdate $update) use ($store) {
                    if ($update->notifyReference) {
                        $update->notifyReference->libryos()->active()->chunkById(2, function ($libryos) use ($update, $store) {
                            $store->attachLibryos($update, $libryos);
                        });
                        // just to keep things backwards compatible we notify libraries as well
                        // all associated libryos will already have been attached, so this is mainly just for
                        // populating the register_notification_library table
                        // TODO: refactor to not use libraries anymore
                        $update->notifyReference?->libraries()->chunkById(2, function ($libraries) use ($update, $store) {
                            $store->attachLibraries($update, $libraries);
                        });
                    }

                    $this->setAsNotified($update);
                });
            });

        Log::info('Notify: Notifiying libraries complete');
    }

    /**
     * Set the given legal updates as mailed to the given users.
     *
     * @param User            $user
     * @param array<int, int> $updateIds
     *
     * @return void
     */
    protected function setAsMailed(User $user, array $updateIds): void
    {
        LegalUpdateUser::where('user_id', $user->id)
            ->whereIn('register_notification_id', $updateIds)
            ->update(['email_sent' => UserLegalUpdateSentStatus::SENT->value]);
    }

    /**
     * Set the legal update as notified.
     *
     * @param LegalUpdate $update
     *
     * @return void
     */
    protected function setAsNotified(LegalUpdate $update): void
    {
        try {
            LegalUpdateNotified::create([
                'register_notification_id' => $update->id,
                'notify_reference_id' => $update->notify_reference_id,
            ]);
            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
        }
        // @codeCoverageIgnoreEnd
    }
}
