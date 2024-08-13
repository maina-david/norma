<?php

namespace App\Contracts\Compilation;

use App\Enums\Compilation\RecompilationActivityType;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;

interface RecompilationEventInterface
{
    /**
     * Get the user that triggered the event.
     *
     * @return User
     */
    public function getUser(): User;

    /**
     * Get the Libryo that is affected.
     *
     * @return Libryo
     */
    public function getLibryo(): Libryo;

    /**
     * Get the type of activity that is represented by the event.
     *
     * @return RecompilationActivityType
     */
    public function getActivityType(): RecompilationActivityType;

    /**
     * Serialise the event to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
