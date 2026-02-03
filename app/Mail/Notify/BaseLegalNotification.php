<?php

namespace App\Mail\Notify;

use App\Mail\Auth\AbstractUserMailable;
use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use App\Services\Notify\Tokenizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

abstract class BaseLegalNotification extends AbstractUserMailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User            $user
     * @param array<int, int> $notificationIds
     */
    public function __construct(public User $user, public array $notificationIds)
    {
        parent::__construct($user);
    }

    /**
     * Get the subject of the email.
     *
     * @return string
     */
    abstract protected function mailSubject(): string;

    /**
     * @codeCoverageIgnore
     * Get the mail intro lines.
     *
     * @return array<int, string>
     */
    protected function introLines(): array
    {
        return [];
    }

    /**
     * Get the mail outro lines.
     *
     * @return array<int, string>
     */
    protected function outroLines(): array
    {
        return [];
    }

    /**
     * Whether to use the alternate header.
     *
     * @return bool
     */
    protected function useAltHeader(): bool
    {
        return false;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $updates = LegalUpdate::whereKey($this->notificationIds)
            ->whereHas('normas', function ($builder) {
                $builder->userHasAccess($this->user)->active();
            })
            ->with([
                'notifiable:id,title,title_translation',
                'primaryLocation',
                'primaryLocation.country',
                'primaryLocation.ancestors',
            ])
            ->get()
            ->map(function (LegalUpdate $update) {
                $update->first_applicable_place_id = $update->normas()
                    ->userHasAccess($this->user)
                    ->active()
                    ->value('id');

                return $this->addJurisdictions($update);
            });

        return $this->markdown('emails.notify.legal-update')
            ->subject($this->mailSubject())
            ->with([
                'introLines' => $this->introLines(),
                'outroLines' => $this->outroLines(),
                'notifications' => $updates,
                'altHeader' => $this->useAltHeader(),
                'appName' => $this->getAppName(),
                'unsubscribe_token' => Tokenizer::make($this->user->id, config('auth.public_token_key')),
            ]);
    }

    /**
     * Add Jurisdictions to the legal update.
     *
     * @param LegalUpdate $update
     *
     * @return LegalUpdate
     */
    protected function addJurisdictions(LegalUpdate $update): LegalUpdate
    {
        $jurisdictions = collect();
        $location = $update->primaryLocation;
        if (!$location) {
            // @codeCoverageIgnoreStart
            return $update;
            // @codeCoverageIgnoreEnd
        }

        if ($location->level == 1) {
            $jurisdictions->push($location->title);
            $location['jurisdictions'] = $jurisdictions->join("\n");
        }
        $ancestors = $location->ancestors
            ->reverse()
            ->push($location)
            ->map(fn ($ancestor) => $ancestor->title)
            ->join(' > ');

        $jurisdictions->push($ancestors);
        $location['jurisdictions'] = $jurisdictions->unique('id')->join("\n");

        return $update;
    }

    /**
     * Get the total number of updates.
     *
     * @return int
     */
    protected function getUpdateCount(): int
    {
        return count($this->notificationIds);
    }
}
