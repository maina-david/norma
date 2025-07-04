<?php

namespace App\Events\Compilation;

use App\Models\Compilation\Library;
use App\Models\Customer\Norma;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LibraryAttachedToNorma
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Library $library, public Norma $norma)
    {
    }
}
