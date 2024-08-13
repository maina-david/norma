<?php

namespace App\Events\Compilation;

use App\Contracts\Compilation\RecompilationEventInterface;
use App\Enums\Compilation\RecompilationActivityType;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class RecompilationActivityEvent implements RecompilationEventInterface
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param User   $user
     * @param Libryo $libryo
     */
    public function __construct(protected Libryo $libryo, protected User $user)
    {
    }

    /**
     * Get the user that triggered the event.
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Get the Libryo that is affected.
     *
     * @return Libryo
     */
    public function getLibryo(): Libryo
    {
        return $this->libryo;
    }

    /**
     * Get the type of activity that is represented by the event.
     *
     * @return RecompilationActivityType
     */
    abstract public function getActivityType(): RecompilationActivityType;

    /**
     * Serialise the event to array.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
