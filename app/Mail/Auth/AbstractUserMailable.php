<?php

namespace App\Mail\Auth;

use App\Models\Auth\User;
use App\Models\Partners\WhiteLabel;
use App\Services\Partners\WhitelabelsForUser;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use RuntimeException;

abstract class AbstractUserMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    /** @var Whitelabel|null */
    public $whitelabel;

    /** @var string */
    public $baseClientUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user)
    {
        $this->setWhitelabel();
        $this->onQueue('notifications');
        if (is_null($user->email)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Trying to email send to user without email');
            // @codeCoverageIgnoreEnd
        }

        $this->to($user->email);
    }

    /**
     * Initialises the mailable to use the correct whitelabel for the user.
     *
     * @return void
     **/
    public function setWhitelabel(): void
    {
        $whitelabels = app(WhitelabelsForUser::class);
        $this->whitelabel = $whitelabels->getDefaultForUser($this->user);
        $this->baseClientUrl = $whitelabels->getBaseUrlForUser($this->user);

        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $this->from($fromAddress, $fromName);
    }

    /**
     * Get the application name.
     *
     * @return string
     */
    protected function getAppName()
    {
        return $this->whitelabel
            ? $this->whitelabel->title
            : config('app.name');
    }

    /**
     * Format the date to users timezone.
     *
     * @param Carbon|DateTimeInterface|string $date
     *
     * @return string
     */
    protected function formatDate(Carbon|DateTimeInterface|string $date): string
    {
        return $this->user->formatDate($date);
    }
}
